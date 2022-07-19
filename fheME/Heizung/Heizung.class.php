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
	function connect(){
		$S = new FhemServer($this->A("HeizungFhemServerID"));
		
		$this->connection = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
		return $this->connection;
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
		
		$data = date_sun_info(time(), (float) $this->A("HeizungLat"), (float) $this->A("HeizungLon"));
		
		$c = "set ".$this->A("HeizungFhemName")." programHC1_Mo-So_0 ".date("H:i", $this->round($data["sunrise"] + 1800))."--".date("H:i", $this->round($data["sunset"] - 1800));
		$this->connection->fireAndForget($c);
		echo $c."\n";
	}
	
	function air() {
		#programFan_Mo-So_0 00:00--06:30
		#programFan_Mo-So_1 n.a.--n.a.
		#programFan_Mo-So_2 21:00--24:00
		
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

		if($parsed["outsideTemp"] < (float) $this->A("HeizungFanCutoffTemp")){
			$c = "set ".$this->A("HeizungFhemName")." p07FanStageDay ".$this->A("HeizungFanStageBelowDay")."\n";
			$this->connection->fireAndForget($c);
			echo $c;
			
			$c = "set ".$this->A("HeizungFhemName")." p08FanStageNight ".$this->A("HeizungFanStageBelowNight")."\n";
			$this->connection->fireAndForget($c);
			echo $c;
		} else {
			$c = "set ".$this->A("HeizungFhemName")." p07FanStageDay ".$this->A("HeizungFanStageAboveDay")."\n";
			$this->connection->fireAndForget($c);
			echo $c;
			
			$c = "set ".$this->A("HeizungFhemName")." p08FanStageNight ".$this->A("HeizungFanStageAboveNight")."\n";
			$this->connection->fireAndForget($c);
			echo $c;
		}
		
		
		$data = date_sun_info(time(), (float) $this->A("HeizungLat"), (float) $this->A("HeizungLon"));
		
		$c = "set ".$this->A("HeizungFhemName")." programFan_Mo-So_0 ".date("H:i", $this->round($data["sunrise"] + 3600))."--".date("H:i", $this->round($data["sunset"] - 3600));
		$this->connection->fireAndForget($c);
		echo $c."\n";
	}
	
	function water(){
		$data = date_sun_info(time(), (float) $this->A("HeizungLat"), (float) $this->A("HeizungLon"));
		
		$c = "set ".$this->A("HeizungFhemName")." programDHW_Mo-So_0 ".date("H:i", $this->round($data["sunrise"] + 3600))."--".date("H:i", $this->round($data["sunset"] - 3600));
		$this->connection->fireAndForget($c);
		echo $c."\n";
	}
	
	private function round($time){
		$time = $time / (60 * 15);
		$time = ceil($time) * (60 * 15);
		
		return $time;
	}
}
?>