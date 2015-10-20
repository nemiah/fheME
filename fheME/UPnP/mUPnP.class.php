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

class mUPnP extends anyC {
	
	public static $desiredServices = array("AVTransport" => "urn:upnp-org:serviceId:AVTransport", "ContentDirectory" => "urn:upnp-org:serviceId:ContentDirectory", "ConnectionManager" => "urn:upnp-org:serviceId:ConnectionManager", "RenderingControl" => "urn:upnp-org:serviceId:RenderingControl");
	
	public function discoverNow($reloadWhat = null, $force = false, $quiet = false){
		$last = mUserdata::getGlobalSettingValue("UPnPLastDiscover", 0);
		
		if(time() - $last < 3600 * 3.5 AND !$force)
			return;
		
		$C = new phpUPnP();
		$result = $C->mSearch();
		
		#print_r($result);
		if(!$quiet)
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
			#echo "<pre>";
			#print_r($xml->device->UDN);
			#echo "</pre>";
			$F = new Factory("UPnP");
			$F->sA("UPnPUDN", $xml->device->UDN);
			$L->addItem($xml->device->friendlyName);
			
			$U = $F->exists(true);
			if($U !== false){
				$U->changeA("UPnPLocation", $r["location"]);
				#$U->changeA("UPnPName", $xml->device->friendlyName);
				$U->changeA("UPnPModelName", $xml->device->modelName);
				$U->changeA("UPnPUDN", $xml->device->UDN);
				
				foreach(self::$desiredServices AS $S => $nil)
					$U->changeA("UPnP$S", 0);
				
				foreach($services AS $S => $service){
					$U->changeA("UPnP$S", 1);
					$U->changeA("UPnP".$S."SCPDURL", $service->SCPDURL[0]."");
					$U->changeA("UPnP".$S."controlURL", $service->controlURL[0]."");
				}
				
				#echo "save";
				$U->saveMe();
			} else {
				$F->sA("UPnPLocation", $r["location"]);
				$F->sA("UPnPName", $xml->device->friendlyName);
				$F->sA("UPnPModelName", $xml->device->modelName);
				
				foreach(self::$desiredServices AS $S => $nil)
					$F->sA("UPnP$S", 0);
				
				foreach($services AS $S => $service){
					$F->sA("UPnP$S", 1);
					$F->sA("UPnP".$S."SCPDURL", $service->SCPDURL[0]."");
					$F->sA("UPnP".$S."controlURL", $service->controlURL[0]."");
				}
				#echo "store";
				$F->store();
			}
			
			
			$foundLocations[$r["location"]] = true;
		}
		
		$AC = anyC::get("UPnP");
		while($U = $AC->getNextEntry()){
			if(!isset($foundLocations[$U->A("UPnPLocation")]))
				$U->deleteMe();
		}
		if(!$quiet)
			echo $L;
		
		$B = new Button("OK", "bestaetigung");
		$B->style("float:right;margin:10px;");
		if($reloadWhat == "targets")
			$B->onclick(OnEvent::closePopup("mUPnP")." UPnP.targetSelection();");
		
		if($reloadWhat == "sources")
			$B->onclick(OnEvent::closePopup("mUPnP")." UPnP.sourceSelection();");
		
		if($reloadWhat)
			echo $B."<div style=\"clear:both;\"></div>";
		
		mUserdata::setUserdataS("UPnPLastDiscover", time(), "", -1);
		#echo "</pre>";
	}
	
}
?>