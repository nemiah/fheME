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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class UPnPGUI extends UPnP implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("UPnP");
	
		$gui->label("UPnPConnectionManager", "Available?");
		$gui->label("UPnPAVTransport", "Available?");
		$gui->label("UPnPContentDirectory", "Available?");
		
		$gui->label("UPnPContentDirectorySCPDURL", "SCPDURL");
		$gui->label("UPnPAVTransportSCPDURL", "SCPDURL");
		$gui->label("UPnPConnectionManagerSCPDURL", "SCPDURL");
	
		$gui->label("UPnPContentDirectorycontrolURL", "controlURL");
		$gui->label("UPnPAVTransportcontrolURL", "controlURL");
		$gui->label("UPnPConnectionManagercontrolURL", "controlURL");
		
		$gui->space("UPnPConnectionManager", "ConnectionManager");
		$gui->space("UPnPAVTransport", "AVTransport");
		$gui->space("UPnPContentDirectory", "ContentDirectory");
		
		$B = $gui->addSideButton("Info\nabrufen", "lieferschein");
		$B->popup("", "Info abrufen", "UPnP", $this->getID(), "loadInfo");
		
		
		if($this->A("UPnPAVTransport") == "1"){
			$B = $gui->addSideButton("Steuerung", "./fheME/UPnP/controls.png");
			$B->popup("", "Steuerung", "UPnP", $this->getID(), "controls");
		}
		
		return $gui->getEditHTML();
	}
	
	
	public function controls(){
		$desiredCommands = array("Play", "Stop", "Next");
		
		$url = parse_url($this->A("UPnPLocation"));
		#print_r($url);
		$info = file_get_contents($url["scheme"]."://".$url["host"].":".$url["port"].$this->A("UPnPAVTransportSCPDURL"));
		$xml = new SimpleXMLElement($info);
		#echo "<pre style=\"padding:5px;font-size:9px;overflow:auto;height:400px;\">";
		foreach ($xml->actionList->action AS $action){
			$name = $action->name[0]."";
			if(!in_array($name, $desiredCommands))
				continue;
			
			echo "<p><a href=\"#\" onclick=\"".OnEvent::rme($this, $name)." return false;\">".$name."</a></p>";
		}
		#$this->prettyfy($info);
		#echo "</pre>";
		
		#print_r($info);
	}
	
	public function loadInfo(){
		$info = file_get_contents($this->A("UPnPLocation"));
		if($info === false)
			continue;
		
		libxml_use_internal_errors(true);
		try {
			$xml = new SimpleXMLElement($info);
		} catch(Exception $e){
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
		}
		
		echo "<pre style=\"padding:5px;font-size:9px;overflow:auto;height:400px;\">";
		$this->prettyfy($info);
		echo "</pre>";
	}
}
?>