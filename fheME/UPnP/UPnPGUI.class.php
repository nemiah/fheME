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
		
		if($this->A("UPnPContentDirectory") == "1"){
			$B = $gui->addSideButton("Verzeichnis", "./fheME/UPnP/directory.png");
			$B->popup("", "Verzeichnis", "UPnP", $this->getID(), "directory", "0", "", "{width:800}");
		}
		
		return $gui->getEditHTML();
	}
	
	public function directoryTouch($ObjectID, $UPnPTargetID = null, $isBack = false){
		$result = $this->Browse($ObjectID, "BrowseDirectChildren", "");
		
		$xml = new SimpleXMLElement($result["Result"]);
		
		#$L = new HTMLList();
		$L = "";
		$ex = explode("$", $ObjectID);
		array_pop($ex);
		
		#$B = new Button("Zurück", "back");
		#$B->popup("", "", "UPnP", $this->getID(), "directory", implode("$", $ex));
		#$B->style("margin:10px;");
		$B = "
		<div id=\"UPnPBackButton\" style=\"width:33%;display:inline-block;cursor:pointer;overflow:hidden;\" onclick=\"".OnEvent::rme($this, "directoryTouch", array("'".implode("$", $ex)."'", $UPnPTargetID, 1), "function(transport){ \$j('#UPnPMediaSelection').prepend(transport.responseText); \$j('.UPnPDirectory:first').animate({'margin-left': '0'}, 600, function(){ \$j('.UPnPDirectory:last').remove(); }); }")."\">
			<div style=\"font-family:Roboto;font-size:30px;padding:10px;\"><span class=\"iconic iconicL arrow_left\"></span> Zurück</div>
		</div>";
		
		if(count($ex) == 0)
			$B = "<div id=\"UPnPBackButton\" style=\"width:33%;display:inline-block;cursor:pointer;overflow:hidden;\">
			<div style=\"font-family:Roboto;font-size:30px;padding:10px;\">&nbsp;</div>
		</div>";
		
		foreach($xml->container AS $container){
			$L .= "
				<div style=\"width:33%;display:inline-block;cursor:pointer;overflow:hidden;margin-bottom:30px;\" onclick=\"".OnEvent::rme($this, "directoryTouch", array("'".$container->attributes()->id."'", $UPnPTargetID), "function(transport){ \$j('#UPnPMediaSelection').append(transport.responseText); \$j('.UPnPDirectory:first').animate({'margin-left': '-50%'}, 600, function(){ \$j('.UPnPDirectory:first').remove(); }); }")."\">
					<div style=\"font-family:Roboto;font-size:30px;padding:10px;\">
					<span class=\"iconic iconicL folder_stroke\"></span> ".$container->children("http://purl.org/dc/elements/1.1/")."
					</div>
				</div>";
		}
		
		$L .= "<div style=\"width:100%;display:inline-block;\">&nbsp;</div>";
		
		foreach($xml->item AS $item){
			$L .= "
				<div onclick=\"".OnEvent::rme(new UPnP($this->getID()), "readSetStart", array("'".$item->attributes()->id."'", "UPnP.currentTargetID"))."\" style=\"width:33%;display:inline-block;overflow:hidden;margin-bottom:30px;\">
					<div style=\"font-family:Roboto;font-size:20px;padding:10px;width:1000%;\">
					<span class=\"iconic iconicL document_alt_stroke\" style=\"float:left;\"></span> ".$item->children("http://purl.org/dc/elements/1.1/")."
					</div>
				</div>";
		}
		
		echo "<div class=\"UPnPDirectory\" style=\"".($isBack ? "margin-left:-50%;" : "")."width:50%;display:inline-block;\">".$B."<div class=\"UPnPDirectoryBrowser\" style=\"overflow-y: auto;;\">".$L."</div>".OnEvent::script("\$j('.UPnPDirectoryBrowser').css('height', (\$j(window).height() - \$j('#UPnPSelection').outerHeight() - \$j('#UPnPBackButton').outerHeight() - 10)+'px');")."</div>";
	}
	
	public function directory($ObjectID){
		$result = $this->Browse($ObjectID, "BrowseDirectChildren", "");
		
		$xml = new SimpleXMLElement($result["Result"]);
		#echo "<pre style=\"max-height:300px;overflow:auto;padding:5px;\">";
		#$this->prettyfy($result["Result"]);
		/*
		$this->prettyfy('<?xml version="1.0"?><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Browse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><ObjectID>0$3$35R1138699</ObjectID><BrowseFlag>BrowseMetadata</BrowseFlag><Filter>*</Filter><StartingIndex>0</StartingIndex><RequestedCount>0</RequestedCount><SortCriteria></SortCriteria></u:Browse></s:Body></s:Envelope>');
		$this->prettyfy('<?xml version="1.0" encoding="utf-8"?><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:BrowseResponse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><Result>&lt;DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns:dlna="urn:schemas-dlna-org:metadata-1-0/" xmlns:arib="urn:schemas-arib-or-jp:elements-1-0/" xmlns:dtcp="urn:schemas-dtcp-com:metadata-1-0/" xmlns:pv="http://www.pv.com/pvns/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"&gt;&lt;item id=&quot;0$3$35R1138699&quot; refID=&quot;0$3$28I1138699&quot; parentID=&quot;0$3$35&quot; restricted=&quot;1&quot;&gt;&lt;dc:title&gt;ddlsource.com_banshee.s01e10.720p.hdtv.x264-2hd&lt;/dc:title&gt;&lt;dc:date&gt;2013-03-16&lt;/dc:date&gt;&lt;upnp:genre&gt;Unknown&lt;/upnp:genre&gt;&lt;upnp:album&gt;Entertain&lt;/upnp:album&gt;&lt;upnp:albumArtURI dlna:profileID="JPEG_TN" &gt;http://192.168.7.123:9000/disk/defaultalbumart/nocover_video.jpg/O0$3$28I1138699.jpg?scale=160x160&lt;/upnp:albumArtURI&gt;&lt;pv:extension&gt;mkv&lt;/pv:extension&gt;&lt;pv:modificationTime&gt;1363415976&lt;/pv:modificationTime&gt;&lt;pv:addedTime&gt;1363515970&lt;/pv:addedTime&gt;&lt;pv:lastUpdated&gt;1363415976&lt;/pv:lastUpdated&gt;&lt;res duration="0:57:14.496" size="1372420691" resolution="1280x716" protocolInfo="http-get:*:video/x-matroska:DLNA.ORG_PN=MKV;DLNA.ORG_OP=01;DLNA.ORG_FLAGS=01700000000000000000000000000000" &gt;http://192.168.7.123:9000/disk/DLNA-PNMKV-OP01-FLAGS01700000/O0$3$28I1138699.mkv&lt;/res&gt;&lt;upnp:class&gt;object.item.videoItem.movie&lt;/upnp:class&gt;&lt;/item&gt;&lt;/DIDL-Lite&gt;</Result><NumberReturned>1</NumberReturned><TotalMatches>1</TotalMatches><UpdateID>0</UpdateID></u:BrowseResponse></s:Body></s:Envelope>');
		*/
		#echo "</pre>";
		
		
		$L = new HTMLList();
		$ex = explode("$", $ObjectID);
		array_pop($ex);
		
		$B = new Button("Zurück", "back");
		$B->popup("", "", "UPnP", $this->getID(), "directory", implode("$", $ex));
		$B->style("margin:10px;");
		if(count($ex) == 0)
			$B = "";
		
		foreach($xml->container AS $container){
			$L->addItem("<a href=\"#\" onclick=\"".OnEvent::popup("", "UPnP", $this->getID(), "directory", $container->attributes()->id)." return false;\">".$container->children("http://purl.org/dc/elements/1.1/")."</a>");
		}
		
		foreach($xml->item AS $item){#".OnEvent::popup("", "UPnP", $this->getID(), "readSetStart", array("'".$item->attributes()->id."'"))."
			$L->addItem("<a href=\"#\" onclick=\"return false;\">".$item->children("http://purl.org/dc/elements/1.1/")."</a>");
		}
		
		echo $B."<div style=\"max-height:450px;overflow:auto;padding:5px;\">".$L."</div>";
	}
	
	public function readSetStart($ObjectID, $targetUPnPID){
		$B = new Button("Reload", "refresh");
		$B->onclick(OnEvent::popup("", "UPnP", $this->getID(), "readSetStart", array("'".$ObjectID."'")));
		$B->style("margin:10px;");
		
		$result = $this->Browse($ObjectID, "BrowseMetadata", "*");
		echo $B;
		
		echo "<pre style=\"max-height:300px;overflow:auto;padding:5px;\">";
		#$this->prettyfy($result["Result"]);
		
		$xml = new SimpleXMLElement($result["Result"]);
		print_r($xml->item[0]->res[0]."");
		
		$U = new UPnP($targetUPnPID);
		$U->SetAVTransportURI(0, $xml->item[0]->res[0]."");
		$U->Play();
		
		echo "</pre>";
		
	}
	
	public function controls(){
		$desiredCommands = array("Play", "Pause", "Stop", "Next");
		
		$url = parse_url($this->A("UPnPLocation"));
		#print_r($url);
		$info = file_get_contents($url["scheme"]."://".$url["host"].":".$url["port"].$this->A("UPnPAVTransportSCPDURL"));
		$xml = new SimpleXMLElement($info);
		#echo "<pre style=\"padding:5px;font-size:9px;overflow:auto;height:400px;\">";
		foreach ($xml->actionList->action AS $action){
			$name = $action->name[0]."";
			if(!in_array($name, $desiredCommands))
				continue;
			
			echo "<p><a href=\"#\" onclick=\"".OnEvent::rme($this, $name, array("'0'"))." return false;\">".$name."</a></p>";
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