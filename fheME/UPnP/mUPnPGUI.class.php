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

class mUPnPGUI extends anyC implements iGUIHTMLMP2 {
	
	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);
		T::load(dirname(__FILE__), "UPnP");
		
		$gui = new HTMLGUIX($this);
		$gui->version("mUPnP");
		$gui->screenHeight();
		
		$gui->name("UPnP");
		
		$gui->attributes(array("UPnPName"));
		
		$B = $gui->addSideButton("Geräte\nerkennen", "lieferschein");
		$B->popup("", "UPnP-Geräte", "mUPnP", "-1", "discover");
		
		$B = $gui->addSideButton("Remote\nanzeigen", "./fheME/UPnP/remote.png");
		$B->onclick("UPnP.show();");
		
		$B = $gui->addSideButton("Radio-\nStationen", "./fheME/UPnP/radio.png");
		$B->loadFrame("contentRight", "mUPnPRadioStation");
		
		return $gui->getBrowserHTML($id);
	}

	public function remote(){
		
		echo "
		<div style=\"width:100%;margin-bottom:20px;position:fixed;top:0;left:0;background-color:black;\" id=\"UPnPSelection\">
			<div style=\"float:right;margin-right:20px;\">
				<div onclick=\"UPnP.hide();\" style=\"cursor:pointer;float:left;font-family:Roboto;font-size:30px;padding:10px;\">
					<span style=\"margin-left:10px;float:right;margin-top:5px;color:#bbb;\" class=\"iconic iconicL x\"></span> <span>Schließen</span>
				</div>
			</div>
			
			<div style=\"width:33%;float:left;\">
				<div onclick=\"UPnP.targetSelection();\" style=\"cursor:pointer;font-family:Roboto;font-size:30px;padding:10px;white-space: nowrap;\">
					<span style=\"margin-right:10px;float:left;margin-top:5px;color:#bbb;\" class=\"iconic iconicL arrow_down\"></span> <span id=\"UPnPTargetName\">Abspielgerät auswählen</span> 
				</div>
			</div>
			
			<div style=\"display:none;float:left;width:33%;\">
				<div onclick=\"UPnP.sourceSelection();\" style=\"cursor:pointer;float:left;font-family:Roboto;font-size:30px;padding:10px;white-space: nowrap;\">
					<span style=\"margin-right:10px;float:left;margin-top:5px;color:#bbb;\" class=\"iconic iconicL arrow_down\"></span> <span id=\"UPnPSourceName\">Quelle auswählen</span> 
				</div>
			</div>

			<div style=\"clear:both;height:15px;\">
			</div>
			
			<div style=\"float:right;margin-right:20px;\">
				<div onclick=\" Popup.load('Steuerung', 'UPnP', UPnP.currentTargetID, 'controls', [''], '', 'edit');\" style=\"cursor:pointer;float:left;font-family:Roboto;font-size:30px;padding:10px;\">
					<span style=\"margin-left:10px;float:right;margin-top:5px;color:#bbb;\" class=\"iconic iconicL cog\"></span> <span>Steuerung</span>
				</div>
			</div>
		</div>
		
		
		<div id=\"UPnPTargetSelection\" style=\"padding:10px;display:none;width:66%;position:absolute;\"></div>
		<div id=\"UPnPSourceSelection\" style=\"padding:10px;display:none;margin-left:33%;position:absolute;\"></div>
		<div style=\"overflow-x:hidden;width:100%;\">
			<div id=\"UPnPMediaSelection\" style=\"padding-right:0px;width:200%;\"></div>
		</div>";
	}
	
	public function getTargets(){
		$this->addAssocV3("UPnPAVTransport", "=", "1");
		
		$L = new HTMLList();
		$L->addListStyle("font-size:30px;font-family:Roboto;margin-left:10px;");
		while($T = $this->getNextEntry()){
			$L->addItem($T->A("UPnPName"));
			
			$L->addItemEvent("onclick", "UPnP.selectTarget('".$T->getID()."', '".$T->A("UPnPName")."');");
			$L->addItemStyle("cursor:pointer;padding:10px;");
		}
		
		$L->addItem("Liste aktualisieren...");
		$L->addItemEvent("onclick", OnEvent::popup("Abspielgeräte aktualisieren", "mUPnP", "-1", "discover", array("'targets'"), "", "{hPosition: 'center'}"));
		$L->addItemStyle("cursor:pointer;padding:10px;");
		
		echo $L;
	}
	
	public function getSources(){
		$this->addAssocV3("UPnPContentDirectory", "=", "1");
		
		$L = new HTMLList();
		$L->addListStyle("font-size:30px;font-family:Roboto;margin-left:10px;");
		while($T = $this->getNextEntry()){
			$L->addItem($T->A("UPnPName"));
			
			$L->addItemEvent("onclick", "UPnP.selectSource('".$T->getID()."', '".$T->A("UPnPName")."');");
			$L->addItemStyle("cursor:pointer;padding:10px;");
		}
		
		$L->addItem("Liste aktualisieren...");
		$L->addItemEvent("onclick", OnEvent::popup("Abspielgeräte aktualisieren", "mUPnP", "-1", "discover", array("'sources'"), "", "{hPosition: 'center'}"));
		$L->addItemStyle("cursor:pointer;padding:10px;");
		
		echo $L;
	}
	
	public function discover($reloadWhat = null){
		echo "<p>Starte Suche. Das könnte etwas dauern...</p>";
		echo OnEvent::script(OnEvent::popup("", "mUPnP", "-1", "discoverNow", array("'$reloadWhat'")));
	}
	
	public static $desiredServices = array("AVTransport" => "urn:upnp-org:serviceId:AVTransport", "ContentDirectory" => "urn:upnp-org:serviceId:ContentDirectory", "ConnectionManager" => "urn:upnp-org:serviceId:ConnectionManager", "RenderingControl" => "urn:upnp-org:serviceId:RenderingControl");
	
	public function discoverNow($reloadWhat = null){
		$C = new phpUPnP();
		#
		$result = $C->mSearch();
		echo "<p>Gefundene Geräte:</p>";
		#$locations = array();
		$L = new HTMLList();
		$L->addListStyle("list-style-type:none;");
		$foundLocations = array();
		#echo "<pre style=\"padding:5px;font-size:9px;overflow:auto;height:400px;\">";
		#print_r($result);
		#echo "</pre>";
		foreach($result AS $r){
			if(isset($foundLocations[$r["location"]]))
				continue;
			
			$info = file_get_contents($r["location"]);
			if($info === false)
				continue;
			
			$xml = new SimpleXMLElement($info);
			
			$services = array();
			foreach ($xml->device->serviceList->service AS $service){
				foreach(self::$desiredServices AS $k => $S)
					if($service->serviceId[0] == $S)
						$services[$k] = $service;
			}
			
			
			$F = new Factory("UPnP");
			$F->sA("UPnPLocation", $r["location"]);
			$L->addItem($xml->device->friendlyName);
			
			$U = $F->exists(true);
			if($U !== false){
				$U->changeA("UPnPName", $xml->device->friendlyName);
				$U->changeA("UPnPModelName", $xml->device->modelName);
				
				foreach(self::$desiredServices AS $S => $nil)
					$U->changeA("UPnP$S", 0);
				
				foreach($services AS $S => $service){
					$U->changeA("UPnP$S", 1);
					$U->changeA("UPnP".$S."SCPDURL", $service->SCPDURL[0]."");
					$U->changeA("UPnP".$S."controlURL", $service->controlURL[0]."");
				}
				$U->saveMe();
			} else {
				$F->sA("UPnPName", $xml->device->friendlyName);
				$F->sA("UPnPModelName", $xml->device->modelName);
				
				foreach(self::$desiredServices AS $S => $nil)
					$F->sA("UPnP$S", 0);
				
				foreach($services AS $S => $service){
					$F->sA("UPnP$S", 1);
					$F->sA("UPnP".$S."SCPDURL", $service->SCPDURL[0]."");
					$F->sA("UPnP".$S."controlURL", $service->controlURL[0]."");
				}
				
				$F->store();
			}
			
			
			$foundLocations[$r["location"]] = true;
		}
		
		$AC = anyC::get("UPnP");
		while($U = $AC->getNextEntry()){
			if(!isset($foundLocations[$U->A("UPnPLocation")]))
				$U->deleteMe();
		}
		
		echo $L;
		
		$B = new Button("OK", "bestaetigung");
		$B->style("float:right;margin:10px;");
		if($reloadWhat == "targets")
			$B->onclick(OnEvent::closePopup("mUPnP")." UPnP.targetSelection();");
		
		if($reloadWhat == "sources")
			$B->onclick(OnEvent::closePopup("mUPnP")." UPnP.sourceSelection();");
		
		if($reloadWhat)
			echo $B."<div style=\"clear:both;\"></div>";
		#echo "</pre>";
	}
	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemUPnPGUI\"></span><p>Multimedia</p></div>
			<div style=\"padding:10px;\">";

		
			$B = new Button("Mediencenter", "share", "iconicL");
			#Overlay.showDark();
			$html .= "
			<div class=\"touchButton\" onclick=\"UPnP.show();\">
				".$B."
				<div class=\"label\">Mediencenter</div>
				<div style=\"clear:both;\"></div>
			</div>";
			
			$B = new Button("Radio", "share", "iconicL");
			#Overlay.showDark();
			$html .= "
			<div class=\"touchButton\" onclick=\"".OnEvent::popup("Radio", "mUPnP", "-1", "popupRadio")."\">
				".$B."
				<div class=\"label\">Radio</div>
				<div style=\"clear:both;\"></div>
			</div>";
		
		$html .= "</div>";
		echo $html;
	}
	
	public function popupRadio(){
		$AC = anyC::get("UPnP");
		$AC->addAssocV3("UPnPAVTransport", "=", "1");
		
		$I = new HTMLInput("radioSelection", "select", BPS::getProperty("mUPnPGUI", "lastStation", "0"));
		$I->setOptions($AC, "UPnPName", "Abspielgerät auswählen");
		
		echo $I;
		
		$AC = anyC::get("UPnPRadioStation");
		
		$I = new HTMLInput("radioStation", "select", BPS::getProperty("mUPnPGUI", "lastStation", "0"));
		$I->setOptions($AC, "UPnPRadioStationName", "Sender auswählen");
		
		echo $I;
		
		$B = new Button("Play", "play", "touch");
		$B->rmePCR("mUPnP", "-1", "playRadio", array("\$j('select[name=radioSelection]').val()", "\$j('select[name=radioStation]').val()"));
		$B->style("display:inline-block;width:30%;");
		echo $B;
		
		$B = new Button("Stop", "stop", "touch");
		$B->rmePCR("mUPnP", "-1", "stopRadio", array("\$j('select[name=radioSelection]').val()", "\$j('select[name=radioStation]').val()"));
		$B->style("display:inline-block;width:30%;");
		echo $B;
	}
	
	public function stopRadio($targetUPnPID){
		$U = new UPnP($targetUPnPID);
		$U->Stop();
	}
	
	public function playRadio($targetUPnPID, $UPnPRadioStationID){
		echo $targetUPnPID;
		echo "\n";
		
		$URS = new UPnPRadioStation($UPnPRadioStationID);
		echo $URS->A("UPnPRadioStationURL");
		
		$U = new UPnP($targetUPnPID);
		$U->SetAVTransportURI(0, $URS->A("UPnPRadioStationURL"));
		$U->Play();
	}
	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mUPnPGUI", "Multimedia", 100);
	}
}
?>