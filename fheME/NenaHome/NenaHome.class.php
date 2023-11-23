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
			$N->battery(2000);
			$N->battery(800);
		}
	}
	
	private $dataWechselrichter;
	private $dataWeather;
	private $connectionFhem;
	private $dataFhem;
	
	public function data(){
		$S = new FhemServer($this->A("NenaHomeFhemServerID"));
		$this->connectionFhem = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
		
		if($this->A("NenaHomeWechselrichterID")){
			$W = new Wechselrichter($this->A("NenaHomeWechselrichterID"));
			$this->dataWechselrichter = json_decode($W->getData());
		}
		
		if($this->A("NenaHomeOpenWeatherMapID"))
			$this->dataWeather = new OpenWeatherMap($this->A("NenaHomeOpenWeatherMapID"));
		
		$data = $S->getListXML();
		$xml = new SimpleXMLElement($data);
		
		$states = [];
		foreach($xml->THZ_LIST->THZ[0]->STATE AS $STATE)
			$states[$STATE["key"].""] = $STATE["value"]."";
		
		$states["sHC1"] = $this->parse($states["sHC1"]);
		$this->dataFhem = $states;
	}
	
	private function parse($data){
		preg_match_all("/([a-zA-Z]+): ([a-zA-Z0-9\-\.]+) /", $data, $matches);
		$parsed = [];
		foreach($matches[1] AS $k => $v)
			$parsed[$v] = $matches[2][$k];
		
		return $parsed;
	}
	
	public function battery($hour){
		if(date("Hi") < $hour + 51)
			return;
		
		if(date("Hi") > $hour + 58)
			return;

		$isSet = mUserdata::getGlobalSettingValue("NenaHomeNoDischargeSet", "0");
		
		$W = new Wechselrichter($this->A("NenaHomeWechselrichterID"));
		if($isSet){
			$days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
			foreach($days AS $day)
				shell_exec("python3 ".Util::getRootPath()."/fheME/Photovoltaik/kostal-RESTAPI.py -host \"".$W->A("WechselrichterIP")."\" -password \"".$W->A("WechselrichterPasswort")."\" -SetTimeControl$day \"000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000\"");
			
			 mUserdata::setUserdataS("NenaHomeNoDischargeSet", "0", "", -1);
		}
		
		if(date("m") > 3 AND date("m") < 10)
			return;
		
		
		#$json = $this->dataWechselrichter;
		#$battSOC = 0;
		#if($json === null)
		#	echo "NenaHome: Keine Daten vom Wechselrichter ".__LINE__."!\n";
		#else 
		#	$battSOC = $json->{"Battery SOC"};
		
		
		$Strom = new Stromanbieter($this->A("NenaHomeStromanbieterID"));
		$preise = $Strom->pricesGetProcessed();
		
		#$minPrice = 9999999;
		#$minTime = 0;

		#foreach($preise[0] AS $preis){
			#$preisSec = $preis[0] / 1000;
			
			#if($preisSec < time())
			#	continue;
			
			#if($preisSec > time() + 12 * 3600)
			#	continue;
			
			#if($preis[1] < $minPrice){
				#$minPrice = $preis[1];
				#$minTime = $preisSec;
			#}
			
			#echo date("d. H:i", $preisSec).": ".$preis[1]."\n";
		#}
		
		if(
			#($battSOC > 50 OR $battSOC == 0) 
			#$minPrice >= $Strom->A("StromanbieterBuyBelowCent")
			$this->dataFhem["sHC1"]["seasonMode"] != "winter")#date("m") > 3 AND date("m") < 10)
			return;
		
		#echo $minTime.": $minPrice\n";
		$noDischarge = [];
		foreach($preise[0] AS $preis){
			$preisSec = $preis[0] / 1000;
			
			if($preisSec < time())
				continue;
			
			if($preisSec > time() + 12 * 3600)
				continue;
			#$preis[1] - $minPrice < $minPrice * 0.02 OR 
			if($preis[1] < $Strom->A("StromanbieterBuyBelowCent"))
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
		
		mUserdata::setUserdataS("NoDischargeTimes", json_encode($days), "", -1);
		
		foreach($days AS $day => $value)
			echo shell_exec("python3 ".Util::getRootPath()."/fheME/Photovoltaik/kostal-RESTAPI.py -host \"".$W->A("WechselrichterIP")."\" -password \"".$W->A("WechselrichterPasswort")."\" -SetTimeControl".date("D", $day)." \"".implode("", $value)."\"");
		
		mUserdata::setUserdataS("NenaHomeNoDischargeSet", "1", "", -1);
	}
	
	private function emptyDay(){
		$day = [];
		for($i = 0; $i < 24; $i++)
			$day[$i] = "0000";
		
		return $day;
	}
	
	public function charge(){
		$S = Stromanbieter::getDefault();
		$prices = $S->pricesGetProcessed();
		$isBelow = [];
		$streak = false;
		foreach($prices[0] AS $price){
			if($price[0] / 1000 < time())
				continue;
			
			if($streak AND $price[1] > $S->A("StromanbieterChargeBelowCent"))
				break;
			
			if($price[1] < $S->A("StromanbieterChargeBelowCent")){
				$isBelow[] = $price;
				$streak = true;
			}
		}
		
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
		
		#print_r($isBelow);
		if(count($isBelow) >= 4){
			$pvCurrentPower = 10000;
			$battSOC = 99;
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
			
			if($pvCurrentUsage + $Z->A("ZweiradWatts") < $pvCurrentPower AND $Z->A("ZweiradSOC") < 99 AND $battSOC > 90){
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