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
	private $connection;
	private $data;
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
		
		$this->data = $parsed;
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
	
	function heat(){
		#programHC1_Mo-So_0 06:00--21:30
		#programHC1_Mo-So_1 n.a.--n.a.
		#programHC1_Mo-So_2 n.a.--n.a.
		
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
		
		$log[time()] = $this->data["outsideTemp"];
		
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
		
		$parsed = $this->data;
		$overrideStage = null;
		
		
		$ventHours = [4, 6, 7, 8];

		if(in_array(date("H"), $ventHours)){
			$log = json_decode($this->A("HeizungTempLog"), true);
			$highest = [0, 0];
			foreach($log AS $time => $value){
				if(time() - $time > 3600 * 24)
					continue;

				if($value > $highest[0])
					$highest = [$value, $time];
			}
			
			if($highest[0] > $this->A("HeizungFanCutoffTemp")){
				echo "Höchste Temperatur der letzten 24 Stunden: $highest[0], um ".date("H:i", $highest[1])." Uhr\n";
				if($parsed["outsideTemp"] <= $this->A("HeizungVentTemp")){
					echo "Außentemperatur ".$parsed["outsideTemp"]." <= ".$this->A("HeizungVentTemp")." Jetzt Lüften!\n";
					$overrideStage = $this->A("HeizungVentStage");
				} else {
					echo "Außentemperatur ".$parsed["outsideTemp"]." > ".$this->A("HeizungVentTemp")." NICHT Lüften!\n";
				}
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
	
	function water(){
		if($this->A("HeizungWechselrichterID")){
			$W = new Wechselrichter($this->A("HeizungWechselrichterID"));
			$data = $W->getData();
			if(!$data){
				echo "Keine Daten vom Wechselrichter!";
				$c  = "set p04DHWsetDayTemp ".$this->A("HeizungWaterDayTemp");
				$this->connection->fireAndForget($c);
				echo $c."\n";
			} else {
				$json = json_decode($data);
				if($json->{"Total DC power Panels"} > $this->A("HeizungWaterHotAboveW"))
					$c = "set p04DHWsetDayTemp ".$this->A("HeizungWaterHotTemp");
				else
					$c = "set p04DHWsetDayTemp ".$this->A("HeizungWaterDayTemp");
				
				$this->connection->fireAndForget($c);
				echo $c."\n";
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