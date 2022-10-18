<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */
class Heizung extends PersistentObject {
	protected $connection;
	protected $data;
	protected $dataWechselrichter;
	protected $dataWeather;
	
	function connect(){
		$S = new FhemServer($this->A("HeizungFhemServerID"));
		
		$this->connection = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
		return $this->connection;
	}
	
	function data(){
		$this->connection->setPrompt("</FHZINFO>");
		$answer = $this->connection->fireAndGet("xmllist ".$this->A("HeizungFhemName"))."</FHZINFO>";

		$xml = new SimpleXMLElement($answer);
		$data = "";
		foreach($xml->THZ_LIST->THZ[0]->STATE AS $STATE){
			if($STATE["key"] != "sGlobal")
				continue;
			
			if(time() - strtotime($STATE["measured"]) > 3600)
				throw new Exception("Old data!");
			
			$data = $STATE["value"];
		}
		
		if(trim($data) == "")
			throw new Exception("No data!");
		
		preg_match_all("/([a-zA-Z]+): ([0-9\-\.]+) /", $data, $matches);
		$parsed = [];
		foreach($matches[1] AS $k => $v)
			$parsed[$v] = $matches[2][$k];
		
		$this->dataFhem = $parsed;
		
		if($this->A("HeizungWechselrichterID")){
			$W = new Wechselrichter($this->A("HeizungWechselrichterID"));
			$this->dataWechselrichter = json_decode($W->getData());
		}
		
		
		if($this->A("HeizungOpenWeatherMapID"))
			$this->dataWeather = new OpenWeatherMap($this->A("HeizungOpenWeatherMapID"));
		
	}
	
	function disconnect(){
		$this->connection->disconnect();
	}
	
	protected function getData(){
		$S = new FhemServer($this->A("HeizungFhemServerID"));
		$data = $S->getListXML();
		$xml = new SimpleXMLElement($data);
		return $xml;
	}
	
	public function getParsedData(){
		$xml = $this->getData();
		$states = [];
		foreach($xml->THZ_LIST->THZ[0]->STATE AS $STATE)
			$states[$STATE["key"].""] = $STATE["value"]."";
		
		$states["sGlobal"] = $this->parse($states["sGlobal"]);
		$states["sHC1"] = $this->parse($states["sHC1"]);
		$states["sDisplay"] = $this->parse($states["sDisplay"]);
		
		return $states;
	}
	
	private function parse($data){
		preg_match_all("/([a-zA-Z]+): ([a-zA-Z0-9\-\.]+) /", $data, $matches);
		$parsed = [];
		foreach($matches[1] AS $k => $v)
			$parsed[$v] = $matches[2][$k];
		
		return $parsed;
	}
	
	public static $raumtempDefault = 17;
	public static $raumtempHeat = 24;
	public static $summerModeTempDefault = 10;
	public static $summerModeTempHeat = 20;
	
	function heat(){
		#programHC1_Mo-So_0 06:00--21:30
		#programHC1_Mo-So_1 n.a.--n.a.
		#programHC1_Mo-So_2 n.a.--n.a.
		
		#$raumtempDefault = 17;
		#$raumtempHeat = 24;
		#$summerModeTempDefault = 10;
		#$summerModeTempHeat = 20;
		
		$override = mUserdata::getGlobalSettingValue("HeizungHeatUntil", "0");
		if($override > time()){
			echo "Heizung: Manueller Modus bis ".date("H:i:s", $override).", tue  nichts!\n";
			return;
		}
		
		#$parsed = $this->dataFhem;
		#print_r($parsed);
		$json = $this->dataWechselrichter;
		$hasBatt = false;
		if($json === null){
			echo "Heizung: Keine Daten vom Wechselrichter!\n";
		
			$c  = "set ".$this->A("HeizungFhemName")." p01RoomTempDayHC1 ".self::$raumtempDefault;
			$this->connection->fireAndForget($c);
			echo $c."\n";	
		} else 
			if($json->{"Battery SOC"} >= 95)
				$hasBatt = true;
		
		$last = mUserdata::getGlobalSettingValue("p01RoomTempDayHC1", "0");
		if($json->{"Total DC power Panels"} > 1500 AND $hasBatt){
			#print_r($this->dataWechselrichter);
			$c = "set ".$this->A("HeizungFhemName")." p01RoomTempDayHC1 ".self::$raumtempHeat;
			$this->connection->fireAndForget($c);
			echo $c."\n";
			$last = mUserdata::setUserdataS("p01RoomTempDayHC1", self::$raumtempHeat, "", -1);
			
			$c = "set ".$this->A("HeizungFhemName")." p49SummerModeTemp ".self::$summerModeTempHeat;
			$this->connection->fireAndForget($c);
			echo $c."\n";
		} elseif($last != self::$raumtempDefault) {
			$c = "set ".$this->A("HeizungFhemName")." p01RoomTempDayHC1 ".self::$raumtempDefault;
			$this->connection->fireAndForget($c);
			echo $c."\n";
			$last = mUserdata::setUserdataS("p01RoomTempDayHC1", self::$raumtempDefault, "", -1);
			
			$c = "set ".$this->A("HeizungFhemName")." p49SummerModeTemp ".self::$summerModeTempDefault;
			$this->connection->fireAndForget($c);
			echo $c."\n";
		}
			
		$lastTimes = mUserdata::getGlobalSettingValue("HeizungHeatLastTimes", "");
		if($lastTimes != date("Ymd")){
			$data = date_sun_info(time(), (float) $this->A("HeizungLat"), (float) $this->A("HeizungLon"));

			$c = "set ".$this->A("HeizungFhemName")." programHC1_Mo-So_0 ".date("H:i", $this->round($data["sunrise"] + 1800))."--".date("H:i", $this->round($data["sunset"] - 1800));
			$this->connection->fireAndForget($c);
			
			echo $c."\n";
		}
		
		mUserdata::setUserdataS("HeizungHeatLastTimes", date("Ymd"), "", -1);
	}
	
	function log(){
		if($this->A("HeizungTempLog") == "")
			$this->changeA("HeizungTempLog", "[]");
		
		#$this->changeA("HeizungTempLog", str_replace(["{", "}"], ["[", "]"], $this->A("HeizungTempLog")));
		
		$log = json_decode($this->A("HeizungTempLog"), true);
		
		$log[time()] = $this->dataFhem["outsideTemp"];
		
		foreach($log AS $time => $value)
			if(time() - $time > 3600 * 36)
				unset($log[$time]);
			
		$this->changeA("HeizungTempLog", json_encode($log, JSON_UNESCAPED_UNICODE));
		$this->saveMe();
	}
	
	function air() {
		#programFan_Mo-So_0 00:00--06:30
		#programFan_Mo-So_1 n.a.--n.a.
		#programFan_Mo-So_2 21:00--24:00
		
		$parsed = $this->dataFhem;
		$overrideStage = null;
		
		
		$ventHours = [4, 6, 7, 8];

		if(in_array(date("H"), $ventHours)){
			$log = json_decode($this->A("HeizungTempLog"), true);
			$highest = [0, 0];
			$lowest = [200, 0];
			foreach($log AS $time => $value){
				if(time() - $time > 3600 * 24)
					continue;

				if($value > $highest[0])
					$highest = [$value, $time];
				
				if($value > $lowest[0])
					$lowest = [$value, $time];
			}
			
			$hasBatt = true;
			if($this->dataWechselrichter === null)
				echo "Lüftung: Keine Daten vom Wechselrichter!\n";
			else 
				if($this->dataWechselrichter->{"Battery SOC"} < 30)
					$hasBatt = false;
					#echo "Lüftung: Nicht mehr genug Akku!\n";
				
			
			$willBeHot = true;
			if($this->dataWeather === null)
				echo "Lüftung: Keine Wetterdaten!\n";
			else {
				$jsonForecast = json_decode($this->dataWeather->A("OpenWeatherMapDataForecastDaily"));
				#echo Util::CLDateTimeParser($jsonForecast->list[0]->dt);
				if($jsonForecast->list[0]->temp->max < $this->A("HeizungFanCutoffTemp")){
					$willBeHot = false;
					#echo "Lüftung: Heute wird es nicht warm genug!\n";
				}
			}
				
			
			if($highest[0] > $this->A("HeizungFanCutoffTemp") AND $lowest[0] > 14){
				#echo "Höchste Temperatur der letzten 24 Stunden: $highest[0], um ".date("H:i", $highest[1])." Uhr\n";
				if(
					$parsed["outsideTemp"] <= $this->A("HeizungVentTemp") 
					AND $parsed["outsideTemp"] > 14 
					AND $hasBatt 
					AND $willBeHot){
					#echo "Außentemperatur ".$parsed["outsideTemp"]." <= ".$this->A("HeizungVentTemp")." Jetzt Lüften!\n";
					$overrideStage = $this->A("HeizungVentStage");
				}# else {
				#	echo "Außentemperatur ".$parsed["outsideTemp"]." > ".$this->A("HeizungVentTemp")." NICHT Lüften!\n";
				#}
			}
		}
		
		if($parsed["outsideTemp"] < (float) $this->A("HeizungFanCutoffTemp")){
			$c = "set ".$this->A("HeizungFhemName")." p07FanStageDay ".($overrideStage !== null ? $overrideStage : $this->A("HeizungFanStageBelowDay"));
			$this->connection->fireAndForget($c);
			#echo $c."\n";
			
			$c = "set ".$this->A("HeizungFhemName")." p08FanStageNight ".($overrideStage !== null ? $overrideStage : $this->A("HeizungFanStageBelowNight"));
			$this->connection->fireAndForget($c);
			#echo $c."\n";
		} else {
			$c = "set ".$this->A("HeizungFhemName")." p07FanStageDay ".($overrideStage !== null ? $overrideStage : $this->A("HeizungFanStageAboveDay"));
			$this->connection->fireAndForget($c);
			#echo $c."\n";
			
			$c = "set ".$this->A("HeizungFhemName")." p08FanStageNight ".($overrideStage !== null ? $overrideStage : $this->A("HeizungFanStageAboveNight"));
			$this->connection->fireAndForget($c);
			#echo $c."\n";
		}
		
		
		$lastTimes = mUserdata::getGlobalSettingValue("HeizungAirLastTimes", "");
		if($lastTimes != date("Ymd")){
			$data = date_sun_info(time(), (float) $this->A("HeizungLat"), (float) $this->A("HeizungLon"));

			$c = "set ".$this->A("HeizungFhemName")." programFan_Mo-So_0 ".date("H:i", $this->round($data["sunrise"] + 3600))."--".date("H:i", $this->round($data["sunset"] - 3600));
			$this->connection->fireAndForget($c);
			echo $c."\n";
		}
		
		mUserdata::setUserdataS("HeizungAirLastTimes", date("Ymd"), "", -1);
	}
	
	function time(){
		
		
		$lastTimes = mUserdata::getGlobalSettingValue("HeizungTimeLastTimes", "");
		if($lastTimes != date("Ymd")){
			$c = "set ".$this->A("HeizungFhemName")." pClockMinutes ".date("i");
			$this->connection->fireAndForget($c);
			echo $c."\n";
			
			$c = "set ".$this->A("HeizungFhemName")." pClockHour ".date("H");
			$this->connection->fireAndForget($c);
			echo $c."\n";
			
			#$c = "set ".$this->A("HeizungFhemName")." pClockMinutes ".date("m");
			#$this->connection->fireAndForget($c);
			#echo $c."\n";
		}
		
		mUserdata::setUserdataS("HeizungTimeLastTimes", date("Ymd"), "", -1);
	}
	
	function water(){
		if($this->A("HeizungWechselrichterID")){
			#$W = new Wechselrichter($this->A("HeizungWechselrichterID"));
			#$data = $W->getData();
			$json = $this->dataWechselrichter;
			if(!$json){
				echo "Keine Daten vom Wechselrichter!";
				$c  = "set ".$this->A("HeizungFhemName")." p04DHWsetDayTemp ".$this->A("HeizungWaterDayTemp");
				$this->connection->fireAndForget($c);
				echo $c."\n";
			} else {
				#$json = json_decode($data);
				if($json->{"Total DC power Panels"} > $this->A("HeizungWaterHotAboveW") AND $json->{"Battery SOC"} > 20)
					$temp = $this->A("HeizungWaterHotTemp");
				else
					$temp = $this->A("HeizungWaterDayTemp");
				
				$last = mUserdata::getGlobalSettingValue("p04DHWsetDayTemp", "0");
				if($last != $temp){
					$this->connection->fireAndForget("set ".$this->A("HeizungFhemName")." p04DHWsetDayTemp ".$temp);
					echo "set ".$this->A("HeizungFhemName")." p04DHWsetDayTemp ".$temp."\n";
					$last = mUserdata::setUserdataS("p04DHWsetDayTemp", $temp, "", -1);
				}
			}
		}
		
		$lastTimes = mUserdata::getGlobalSettingValue("HeizungWaterLastTimes", "");
		if($lastTimes != date("Ymd")){
			$data = date_sun_info(time(), (float) $this->A("HeizungLat"), (float) $this->A("HeizungLon"));

			$c = "set ".$this->A("HeizungFhemName")." programDHW_Mo-So_0 ".date("H:i", $this->round($data["sunrise"] + 7200))."--".date("H:i", $this->round($data["sunset"] - 7200));
			$this->connection->fireAndForget($c);
			echo $c."\n";
		}
		
		mUserdata::setUserdataS("HeizungWaterLastTimes", date("Ymd"), "", -1);
	}
	
	private function round($time){
		$time = $time / (60 * 15);
		$time = ceil($time) * (60 * 15);
		
		return $time;
	}
}
?>