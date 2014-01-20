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
	public static $prettifyerRules = array(
		"^OneDDL.com-|^1-3-3-8.com[_-]|Ddlsource.com_" => "",
		"-ctu|-immerse|[-.]2hd|-bia|-wasabi|-Hannibal|-FoV|.immerse|-EVOLVE|c4tv|-HoC|Repack|-compulsion|-ASAP|-SiNNERS|-ECI|BluRay|-AVS|-KILLERS|-LOL" => "",
		"[-.]DIMENSION|-MADHACKER|-FEVER|[-.]PiLAF|.PROPER|WEBRip|AAC" => "",
		"s([0-9]+)e([0-9]+)" => "S\\1E\\2",
		"^([a-z])" => "strtoupper('\\1')",
		".hdtv|-orenji|[-.]x264|[_.]WEB[-.]DL|[._]h.264|-kyr" => "",
		"([0-9]{1,2})×([0-9]{2})" => "S\\1E\\2",
		"(.[a-z])" => "strtoupper('\\1')",
		"Mkv" => "mkv",
		"Mp4" => "mp4",
		".720p" => "",
		".(20[0-9]{2})." => "' (\\1) '",
		"." => "' '"
	);
	
	function __construct($ID) {
		parent::__construct($ID);
		
		T::load(dirname(__FILE__), "UPnP");
		T::D("UPnP");
	}
	
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("UPnP");
	
		$gui->attributes(array(
			"UPnPName",
			"UPnPLocation",
			"UPnPModelName",
			"UPnPUDN",
			"UPnPHide",
			"UPnPDefaultDownloadsServer",
			"UPnPDefaultDownloadsDirectory",
			"UPnPContentDirectory",
			"UPnPContentDirectorySCPDURL",
			"UPnPContentDirectorycontrolURL",
			"UPnPAVTransport",
			"UPnPAVTransportSCPDURL",
			"UPnPAVTransportcontrolURL",
			"UPnPConnectionManager",
			"UPnPConnectionManagerSCPDURL",
			"UPnPConnectionManagercontrolURL",
			"UPnPRenderingControl",
			"UPnPRenderingControlSCPDURL",
			"UPnPRenderingControlcontrolURL"
		));
		
		#$gui->optionsEdit(false, false);
		
		$gui->toggleFields("UPnPDefaultDownloadsServer", "1", array("UPnPDefaultDownloadsDirectory"));
		
		$gui->label("UPnPDefaultDownloadsServer", "Server?");
		$gui->label("UPnPDefaultDownloadsDirectory", "Verzeichnis");
		
		$gui->label("UPnPConnectionManager", "Verfügbar?");
		$gui->label("UPnPAVTransport", "Verfügbar?");
		$gui->label("UPnPContentDirectory", "Verfügbar?");
		$gui->label("UPnPRenderingControl", "Verfügbar?");
		
		$gui->type("UPnPDefaultDownloadsServer", "checkbox");
		$gui->type("UPnPConnectionManager", "checkbox");
		$gui->type("UPnPAVTransport", "checkbox");
		$gui->type("UPnPContentDirectory", "checkbox");
		$gui->type("UPnPRenderingControl", "checkbox");
		$gui->type("UPnPHide", "checkbox");
		
		$gui->label("UPnPContentDirectorySCPDURL", "SCPDURL");
		$gui->label("UPnPAVTransportSCPDURL", "SCPDURL");
		$gui->label("UPnPConnectionManagerSCPDURL", "SCPDURL");
		$gui->label("UPnPRenderingControlSCPDURL", "SCPDURL");
	
		$gui->label("UPnPContentDirectorycontrolURL", "controlURL");
		$gui->label("UPnPAVTransportcontrolURL", "controlURL");
		$gui->label("UPnPConnectionManagercontrolURL", "controlURL");
		$gui->label("UPnPRenderingControlcontrolURL", "controlURL");
		
		$gui->space("UPnPDefaultDownloadsServer", "Downloads");
		$gui->space("UPnPConnectionManager", "ConnectionManager");
		$gui->space("UPnPAVTransport", "AVTransport");
		$gui->space("UPnPContentDirectory", "ContentDirectory");
		$gui->space("UPnPRenderingControl", "RenderingControl");
		
		$B = $gui->addSideButton("Info\nabrufen", "lieferschein");
		$B->popup("", "Info abrufen", "UPnP", $this->getID(), "loadInfo");
		
		
		if($this->A("UPnPAVTransport") == "1"){
			$B = $gui->addSideButton("Steuerung", "./fheME/UPnP/controls.png");
			$B->popup("", "Steuerung", "UPnP", $this->getID(), "controls");
		}
		
		if($this->A("UPnPContentDirectory") == "1"){
			$B = $gui->addSideButton("Verzeichnis", "./fheME/UPnP/directory.png");
			$B->popup("", "Verzeichnis", "UPnP", $this->getID(), "directory", "0", "", "{width:800}");
			
			#$B = $gui->addSideButton("Suche", "./fheME/UPnP/search.png");
			#$B->popup("", "Suche", "UPnP", $this->getID(), "suche", "0", "", "{width:800}");
		}
		
		return $gui->getEditHTML();
	}
	
	/*function suche(){
		$result = $this->Search("0$3$35", "(dc:title contains \"Bitten.S01E02\")", "");
		$xml = new SimpleXMLElement($result["Result"]);
		echo "<pre>";
		print_r($xml);
		echo "</pre>";
		$L = new HTMLList();
		foreach($xml->item AS $item){#".OnEvent::popup("", "UPnP", $this->getID(), "readSetStart", array("'".$item->attributes()->id."'"))."
			$L->addItem("<a href=\"#\" onclick=\"return false;\">".$item->children("http://purl.org/dc/elements/1.1/")."</a>");
		}
		echo $L;
		#echo htmlentities(print_r($result, true));
	}*/
	
	private function findSeries(&$entries){
		$series = array();
		#$lastName = null;
		foreach($entries AS $newName => $item){
			preg_match("/(.*) S[0-9]{2}E[0-9]{2}/", $newName, $matches);
			#print_r($matches);
			if(isset($matches[1])){
				if(!isset($series[$matches[1]]))
					$series[$matches[1]] = array();
			
				$series[$matches[1]][$newName] = $item;
				unset($entries[$newName]);
			}
		}
		
		return $series;
	}
	
	private function findEntries($xml){
		$entries = array();
		foreach($xml->item AS $item){
			$newName = $item->children("http://purl.org/dc/elements/1.1/");
			$newName = prettifyDB::apply("seriesEpisodeNameDownloaded", $newName);
			
			$entries[$newName] = $item;
		}
		ksort($entries);
		
		return $entries;
	}
	
	public function directoryTouch($ObjectID, $UPnPTargetID = null, $isBack = false, $seriesName = null){
		$result = $this->Browse($ObjectID, "BrowseDirectChildren", "*");
		$xml = new SimpleXMLElement($result["Result"]);
		
		#$L = new HTMLList();
		$L = "";
		$ex = explode("$", $ObjectID);
		if($seriesName == null)
			array_pop($ex);
		
		#$B = new Button("Zurück", "back");
		#$B->popup("", "", "UPnP", $this->getID(), "directory", implode("$", $ex));
		#$B->style("margin:10px;");
		$B = "
		<div class=\"UPnPBackButton\" style=\"width:66%;display:inline-block;cursor:pointer;overflow:hidden;position:fixed;background-color:black;left:0;top:0;\">
			<div  onclick=\"".OnEvent::rme($this, "directoryTouch", array("'".implode("$", $ex)."'", $UPnPTargetID, 1), "function(transport){ \$j('.UPnPItem, .UPnPSeries').remove(); \$j('#UPnPMediaSelection').prepend(transport.responseText); \$j('.UPnPDirectory:first').animate({'margin-left': '0'}, 600, function(){ \$j('.UPnPDirectory:last').remove(); \$j('.UPnPItem, .UPnPSeries').css('display', 'inline-block'); }); }")."\">
				<div style=\"font-family:Roboto;font-size:30px;padding:10px;\"><span class=\"iconic iconicL arrow_left\" style=\"color:#bbb;margin-right:10px;float:left;margin-top:5px;\"></span> Zurück</div>
			</div>
		</div>";
		
		if(count($ex) == 0)
			$B = "<div class=\"UPnPBackButton\" style=\"width:66%;display:inline-block;overflow:hidden;position:fixed;background-color:black;left:0;top:0;\">
			<div style=\"font-family:Roboto;font-size:30px;padding:10px;\">&nbsp;</div>
		</div>";
		
		if($seriesName == null)
			foreach($xml->container AS $container){#\$j('.UPnPItem').appear(); \$j('.UPnPItem').on('appear', function(){ \$j(this).children().show(); }); \$j.force_appear();
				$action = OnEvent::rme($this, "directoryTouch", array("'".$container->attributes()->id."'", $UPnPTargetID), "function(transport){ \$j('.UPnPItem, .UPnPSeries').remove(); \$j('#UPnPMediaSelection').append(transport.responseText); \$j('.UPnPDirectory:first').animate({'margin-left': '-50%'}, 600, function(){ \$j('.UPnPDirectory:first').remove(); \$j('.UPnPItem, .UPnPSeries').css('display', 'inline-block'); });  }");
				$L .= "
					<div style=\"width:33%;display:inline-block;cursor:pointer;overflow:hidden;margin-bottom:30px;\" ontouchmove=\"\$j(this).removeClass('highlight'); UPnP.skipNext = true;\" onclick=\"if(UPnP.skipNext) { UPnP.skipNext = false; return; } ".$action."\">
						<div style=\"font-family:Roboto;font-size:30px;padding:10px;\">
							<span class=\"iconic iconicL folder_stroke\" style=\"color:#bbb;margin-right:10px;float:left;margin-top:5px;\"></span> ".$container->children("http://purl.org/dc/elements/1.1/")."
						</div>
					</div>";
			}
		
		$L .= "<div style=\"width:100%;display:inline-block;\">&nbsp;</div>";
		
		$entries = $this->findEntries($xml);
		
		$series = $this->findSeries($entries);

		if($seriesName == null)
			foreach($series AS $name => $list){
				$action = OnEvent::rme($this, "directoryTouch", array("'$ObjectID'", $UPnPTargetID, 0, "'$name'"), "function(transport){ \$j('.UPnPItem, .UPnPSeries').remove(); \$j('#UPnPMediaSelection').append(transport.responseText); \$j('.UPnPDirectory:first').animate({'margin-left': '-50%'}, 600, function(){ \$j('.UPnPDirectory:first').remove(); \$j('.UPnPItem, .UPnPSeries').css('display', 'inline-block'); });  }");
				$L .= "
					<div class=\"UPnPSeries\" style=\"cursor:pointer;width:33%;display:none;overflow:hidden;margin-bottom:30px;\" ontouchmove=\"\$j(this).removeClass('highlight'); UPnP.skipNext = true;\" onclick=\"if(UPnP.skipNext) { UPnP.skipNext = false; return; } ".$action."\">
						<div style=\"font-family:Roboto;font-size:17px;padding:10px;overflow:hidden;\">
							<span class=\"iconic iconicL list\" style=\"margin-right:10px;float:left;color:#bbb;\"></span><div style=\"white-space: nowrap;margin-left:40px;display:block;overflow:hidden;\">".$name."</div>
							<div style=\"font-size:12px;\">".count($list)." Folge".(count($list) != 1 ? "n" : "")."</div>
						</div>
					</div>";
			}
		
		if($seriesName != null)
			$entries = $series[$seriesName];
		
		foreach($entries AS $newName => $item)
			$L .= $this->printItem($item, $newName);
		
		echo "<div class=\"UPnPDirectory\" style=\"".($isBack ? "margin-left:-50%;" : "")."width:50%;display:inline-block;vertical-align:top;\">".$B."<div class=\"UPnPDirectoryBrowser\" style=\"background-color:#2F2F2F;\">".$L."</div>".OnEvent::script("UPnP.start();")."</div>";
	}
	
	private function printItem($item, $newName){
		return "
			<div class=\"UPnPItem\" data-oid=\"".$item->attributes()->id."\" ontouchmove=\"\$j(this).removeClass('highlight'); UPnP.skipNext = true;\" onclick=\"if(UPnP.skipNext) { UPnP.skipNext = false; return; }  ".OnEvent::rme(new UPnP($this->getID()), "readSetStart", array("'".$item->attributes()->id."'", "UPnP.currentTargetID"))." \$j(this).find('.iconic.check').css('display', 'inline'); \$j.jStorage.set('phynxUPnPPlayed".$item->attributes()->id."', true);\" style=\"cursor:pointer;width:33%;display:none;overflow:hidden;margin-bottom:30px;\">
				<div style=\"font-family:Roboto;font-size:17px;padding:10px;overflow:hidden;\">
					<span class=\"iconic iconicL check\" style=\"color:#bbb;margin-right:10px;float:right;color:#88cf00;display:none;\"></span>
					<span class=\"iconic iconicL document_alt_stroke\" style=\"color:#bbb;margin-right:10px;float:left;\"></span><div style=\"white-space: nowrap;margin-left:40px;display:block;overflow:hidden;\">".$newName."</div>
					<div style=\"font-size:12px;\">".$item->children("http://www.pv.com/pvns/")->extension[0].""."<span style=\"float:right;\">".Util::formatSeconds(Util::parseTime("de_DE", $item->res->attributes()->duration[0].""))."</span></div>
				</div>
			</div>";
	}
	
	public function details($ObjectID){
		$result = $this->Browse($ObjectID, "BrowseMetadata", "*");
		
		$xml = new SimpleXMLElement($result["Result"]);
		echo "<pre style=\"max-height:300px;overflow:auto;padding:5px;\">";
		$this->prettyfy($result["Result"]);
		echo "Dauer: ".Util::formatSeconds(Util::parseTime("de_DE", $xml->item[0]->res->attributes()->duration[0].""))."\n";
		echo "Typ: ".$xml->item[0]->children("http://www.pv.com/pvns/")->extension[0]."\n";
		echo "</pre>";
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
		echo "<p class=\"prettyTitle\">".$this->A("UPnPName")."</p>";
		#$desiredCommands = array("Play", "Pause", "Stop", "Next", "Previous");
		$icons = array(
			"Play" => "play",
			"Pause" => "pause",
			"Stop" => "stop",
			"Next" => "arrow_right",
			"Previous" => "arrow_left",
			"Mute" => "volume_mute",
			"UnMute" => "volume",
			"Shutdown" => "download");
		
		if(file_get_contents($this->A("UPnPLocation")) === false){
			$B = new Button("Achtung", "notice", "icon");
			$B->style("float:left;margin:10px;");
			echo $B."<p>Der Server ist nicht erreichbar!</p>";
		}
		#$info = file_get_contents($url["scheme"]."://".$url["host"].":".$url["port"].$this->A("UPnPAVTransportSCPDURL"));
		#$xml = new SimpleXMLElement($info);
		#echo "<pre style=\"padding:5px;font-size:9px;overflow:auto;height:400px;\">";
		/*foreach ($xml->actionList->action AS $action){
			$name = $action->name[0]."";
			if(!in_array($name, $desiredCommands))
				continue;
			$B = new Button($name, $icons[$name], "touch");
			$B->rmePCR("UPnP", $this->getID(), $name, array("'0'"));
			echo $B;
			#echo "<p><a href=\"#\" onclick=\"".OnEvent::rme($this, $name, array("'0'"))." return false;\">".$name."</a></p>";
		}*/
		#$this->prettyfy($info);
		#echo "</pre>";
		
		#print_r($info);
		
		echo "<div style=\"clear:both;height:10px;\"></div>";
		
		$B = new Button("Play", $icons["Play"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "Play", array("'0'"));
		#$B->style("display:inline-block;width:30%;");
		echo $B;
		
		$B = new Button("Pause", $icons["Pause"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "Pause", array("'0'"));
		#$B->style("display:inline-block;width:30%;");
		echo $B;
		
		$B = new Button("Stop", $icons["Stop"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "Stop", array("'0'"));
		#$B->style("display:inline-block;width:30%;");
		echo $B;
		
		echo "<br />";
		echo "<br />";
		
		$B = new Button("Previous", $icons["Previous"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "Previous", array("'0'"));
		$B->style("display:inline-block;width:47%;");
		echo $B;
		
		$B = new Button("Next", $icons["Next"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "Next", array("'0'"));
		$B->style("display:inline-block;width:47%;");
		echo $B;
		
		
		$B = new Button("Mute", $icons["Mute"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "SetMute", array("'0'", "'Master'", "'1'"), "function(){ \$j('#UPnPControlsMute').hide(); \$j('#UPnPControlsUnMute').show(); }");
		$B->style("width:47%;".($this->GetMute(0, "Master") == "0" ? "" : "display:none;"));
		$B->id("UPnPControlsMute");
		echo $B;
		
		$B = new Button("UnMute", $icons["UnMute"], "touch");
		$B->rmePCR("UPnP", $this->getID(), "SetMute", array("'0'", "'Master'", "'0'"), "function(){ \$j('#UPnPControlsMute').show(); \$j('#UPnPControlsUnMute').hide(); }");
		$B->style("width:47%;".($this->GetMute(0, "Master") == "1" ? "" : "display:none;"));
		$B->id("UPnPControlsUnMute");
		echo $B;
		
		/*if(strpos($this->A("UPnPModelName"), "XBMC") !== false){
			$B = new Button("Shutdown", $icons["Shutdown"], "touch");
			$B->doBefore("if(confirm('Möchten Sie das Gerät herunterfahren?')) %AFTER");
			$B->rmePCR("UPnP", $this->getID(), "VendorShutdown");
			$B->style("display:inline-block;width:47%;");
			echo $B;
		}*/
		
		echo "<div style=\"height:1px;\"></div>";
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