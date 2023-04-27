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
				$this->setFhemState($F, "off");
				continue;
			}
			
			if($currentState == "on")
				$pvCurrentUsage -= $Z->A("ZweiradWatts");
			
			if($pvCurrentUsage + $Z->A("ZweiradWatts") < $pvCurrentPower AND $Z->A("ZweiradSOC") < 99 AND $battSOC > 50){
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
		
		echo $c."\n";
	}
}
?>