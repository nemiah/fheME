<?php
/*
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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class FhemServer extends PersistentObject {
	public function setDevice($fhemID, $action){
		$F = new Fhem($fhemID);

		switch($this->A("FhemServerType")){
			case "1":
				if($fhemID != -1) $url = $this->A("FhemServerURL")."?device=".$F->A("FhemName")."&value=".$action;
				else $url = $this->A("FhemServerURL")."?value=".$action;
				fopen($url, "r");
			break;
		}
	}
	
	public function getListXML(){
		$T = new Telnet($this->A("FhemServerIP"), $this->A("FhemServerPort"));
		$T->setPrompt("</FHZINFO>");
		$data = $T->fireAndGet("xmllist")."</FHZINFO>";
		$T->disconnect();
		return $data;
	}
	
	public function getListDevices(){
		$xml = new SimpleXMLElement($this->getListXML());
		
		$devices = array();
		
		if(isset($xml->CUL_HM_LIST->CUL_HM) AND count($xml->CUL_HM_LIST->CUL_HM) > 0)
			foreach($xml->CUL_HM_LIST->CUL_HM AS $k => $v){
				#echo "<pre style=\"font-size:10px;overflow:auto;\">";
				#print_r(htmlentities($v->asXML()));
				#echo "</pre>";
				
				$D = new stdClass();
				
				$D->name = $v->attributes()->name;
				$D->state = $v->attributes()->state;
				$D->type = "CUL_HM";
				
				foreach($v->INT AS $int){
					if($int->attributes()->key == "DEF")
						$D->address = $int->attributes()->value;
					
				}
				
				$devices[] = $D;
			}
			
		return $devices;
	}
}
?>
