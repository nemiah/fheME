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
class NenaHome extends PersistentObject {
	
	public static function run(){
		$AC = anyC::get("NenaHome");
		while($N = $AC->n()){
			$N->data();
			$N->charge();
			$N->batteryNight();
		}
	}
	
	private $dataWechselrichter;
	private $dataWeather;
	private $connectionFhem;
	
	public function data(){
		$S = new FhemServer($this->A("NenaHomeFhemServerID"));
		$this->connectionFhem = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
		
		if($this->A("NenaHomeWechselrichterID")){
			$W = new Wechselrichter($this->A("NenaHomeWechselrichterID"));
			$this->dataWechselrichter = json_decode($W->getData());
		}
		
		
		if($this->A("NenaHomeOpenWeatherMapID"))
			$this->dataWeather = new OpenWeatherMap($this->A("NenaHomeOpenWeatherMapID"));
	}
	
	public function batteryNight(){
		if(date("Hi") < 2250)
			return;
		
		$W = new Wechselrichter($this->A("NenaHomeWechselrichterID"));
		
		$days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
		foreach($days AS $day)
			shell_exec("python3 ".Util::getRootPath()."/fheME/Photovoltaik/kostal-RESTAPI.py -host \"".$W->A("WechselrichterIP")."\" -password \"".$W->A("WechselrichterPasswort")."\" -SetTimeControl$day \"000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000\"");
		
		#python3 kostal-RESTAPI.py -SetTimeControlSun "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000002222"
		$Strom = new Stromanbieter($this->A("NenaHomeStromanbieterID"));
		$preise = $Strom->pricesGet();
		
		
		$json = $this->dataWechselrichter;
		$battSOC = 0;
		#$pvCurrentPower = 0;
		#$pvCurrentUsage = 0;
		if($json === null)
			echo "NenaHome: Keine Daten vom Wechselrichter ".__LINE__."!\n";
		else {
			$battSOC = $json->{"Battery SOC"};
			#$pvCurrentPower = $json->{"Total DC power Panels"};
			#$pvCurrentUsage = $json->{"Consumption power Home total"};
		}
		
		if($battSOC > 50)
			return;
		
		$minPrice = 9999999;
		$minTime = 0;
		
		foreach($preise[0] AS $preis){
			$preisSec = $preis[0] / 1000;
			
			if($preisSec < time())
				continue;
			
			if($preisSec > time() + 12 * 3600)
				continue;
			
			if($preis[1] < $minPrice){
				$minPrice = $preis[1];
				$minTime = $preisSec;
			}
			
			#echo date("d. H:i", $preisSec).": ".$preis[1]."\n";
		}
		
		echo $minTime.": $minPrice\n";
		$noDischarge = [];
		foreach($preise[0] AS $preis){
			$preisSec = $preis[0] / 1000;
			
			if($preisSec < time())
				continue;
			
			if($preisSec > time() + 12 * 3600)
				continue;
			
			if($preis[1] - $minPrice < $minPrice * 0.02)
				$noDischarge[] = [$preisSec, $preis[1]];
		}
		
		
		$days = [];
		foreach($noDischarge AS $preis){
			$cDay = $preis[0] - date("H", $preis[0]) * 3600;
			
			if(!isset($days[$cDay]))
				$days[$cDay] = $this->emptyDay();
			
			$days[$cDay][(int) date("H", $preis[0])] = "2222";
			
			#echo date("d. H:i", $preis[0]).": ".$preis[1]."\n";
			
		}
		
		foreach($days AS $day => $value){
			echo shell_exec("python3 ".Util::getRootPath()."/fheME/Photovoltaik/kostal-RESTAPI.py -host \"".$W->A("WechselrichterIP")."\" -password \"".$W->A("WechselrichterPasswort")."\" -SetTimeControl".date("D", $day)." \"".implode("", $value)."\"");
		}
	}
	
	private function emptyDay(){
		$day = [];
		for($i = 0; $i < 24; $i++)
			$day[$i] = "0000";
		
		return $day;
	}
	
	public function charge(){
		
		$json = $this->dataWechselrichter;
		$battSOC = 0;
		$pvCurrentPower = 0;
		$pvCurrentUsage = 0;
		if($json === null)
			echo "NenaHome: Keine Daten vom Wechselrichter ".__LINE__."!\n";
		else {
			$battSOC = $json->{"Battery SOC"};
			$pvCurrentPower = $json->{"Total DC power Panels"};
			$pvCurrentUsage = $json->{"Consumption power Home total"};
		}
		
		#echo $battSOC."\n";
		#echo $pvCurrentPower."\n";
		#echo $pvCurrentUsage;
		
		$AC = anyC::get("Zweirad");
		while($Z = $AC->n()){
			$currentState = "unknown";
			
			$F = new Fhem($Z->A("ZweiradFhemID"));
			foreach($F->getData()[0] AS $state){
				if($state["key"]."" != "state")
					continue;
				
				$currentState = $state["value"]."";
			}

			if(time() - $Z->A("ZweiradLastUpdate") > 30 * 60 OR stripos($Z->A("ZweiradStatus"), "ERROR") !== false){
				if($currentState == "on")
					$this->setFhemState($F, "off");
				continue;
			}
			
			if($currentState == "on")
				$pvCurrentUsage -= $Z->A("ZweiradWatts");
			
			if($pvCurrentUsage + $Z->A("ZweiradWatts") < $pvCurrentPower AND $Z->A("ZweiradSOC") < 99 AND $battSOC > 80){
				if($currentState != "on")
					$this->setFhemState($F, "on");
				continue;
			}
			
			if($Z->A("ZweiradSOC") < $Z->A("ZweiradSOCTarget") AND $currentState != "on")
				$this->setFhemState($F, "on");
			
			if($Z->A("ZweiradSOC") >= $Z->A("ZweiradSOCTarget") AND $currentState == "on")
				$this->setFhemState($F, "off");
			
		}
	}
	
	private function setFhemState($F, $state){
		$c  = "set ".$F->A("FhemName")." $state";
		$this->connectionFhem->fireAndForget($c);
		
		#echo $c."\n";
	}
}
?>