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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class UPnPPlayerGUI extends UnpersistentClass implements iGUIHTMLMP2 {
	public function getHTML($id, $page) {
		$bps = $this->getMyBPSData();
		
		#if(time() - mUserdata::getGlobalSettingValue("mPremiumizeLastScan") > 4 * 3600)
		#	$this->scan();
		
		
		
		#define("PREMIUMIZE_CUSTOMER_ID", $login->A("benutzername"));
		#define("PREMIUMIZE_PIN", $login->A("passwort"));
		
		
		#try {
		#	$info = PremiumizeAPI::getFolderInfo(isset($bps["id"]) ? $bps["id"] : "");
		#} catch (Exception $e){
		#	die("<p class=\"highlight\">".$e->getMessage()."</p>");
		#}
		
		
		
		if(!isset($bps["folder"]))
			$ObjectID = "0";
		else 
			$ObjectID = $bps["folder"];
		#echo $ObjectID;
	
		$target = mUserdata::getGlobalSettingValue("UPnPPlayerTarget", "");
		$source = mUserdata::getGlobalSettingValue("UPnPPlayerSource", "");
		$UPnPSource = anyC::getFirst("UPnP", "UPnPName", $source);
		if(!$UPnPSource)
			$source = "";
		
		$UPnPTarget = anyC::getFirst("UPnP", "UPnPName", $target);
		if(!$UPnPTarget)
			$target = "";
		
		$BD = new Button($target == "" ? "Ziel" : $target, "target", "touch");
		$BD->style("width:120px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BD->popup("", "Abspielen auf", "UPnPPlayer", -1, "targetsPopup");
		
		$BQ = new Button($source == "" ? "Quelle" : $source, "book_alt2", "touch");
		$BQ->style("width:120px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BQ->popup("", "Abspielen von", "UPnPPlayer", -1, "sourcesPopup");
		
		$BTV = new Button("TV", "aperture", "touch");
		$BTV->style("width:120px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BTV->popup("", "TV", "UPnPPlayer", -1, "tvcontrolPopup");
		
		if($source == "" OR $target == ""){
			echo "<div style=\"height:60px;\" class=\"backgroundColor4\">".$BQ.$BD."</div>";
			
			return;
		}
		
		
		
		
		$UPnPSource  = new UPnPGUI($UPnPSource->getID());
		
		$result = $UPnPSource->Browse($ObjectID, "BrowseDirectChildren", "");
		#var_dump(htmlentities($result["Result"]));
		$xml = new SimpleXMLElement($result["Result"]);
		$entries = $UPnPSource->findEntries($xml);
		$series = $UPnPSource->findSeries($entries);
		
		$filterSeries = false;
		if(isset($bps["series"]) AND $bps["series"] != ""){
			$entries = $series[$bps["series"]];
			$filterSeries = $bps["series"];
		}
		
		$ex = explode("$", $ObjectID);
		if(!$filterSeries)
			array_pop($ex);
		
		
		$BG = new Button("Play", "play", "touch");
		$BG->style("margin:10px;display:inline-block;border:1px solid #ccc;width:150px;");
		$BG->rmePCR("UPnP", $UPnPTarget->getID(), "Play", array("'0'"));
		#$BG->onclick(OnEvent::rme($this, "restart"));
		
		$BP = new Button("Pause", "pause", "touch");
		$BP->style("margin:10px;display:inline-block;border:1px solid #ccc;width:150px;");
		$BP->rmePCR("UPnP", $UPnPTarget->getID(), "Pause", array("'0'"));
		
		$BS = new Button("Stop", "stop", "touch");
		$BS->style("margin:10px;display:inline-block;border:1px solid #ccc;width:150px;");
		$BS->rmePCR("UPnP", $UPnPTarget->getID(), "Stop", array("'0'"));
		
		
		$BB = new Button("Zurück", "arrow_left", "touch");
		$BB->style("margin:10px;display:inline-block;border:1px solid #ccc;width:120px;vertical-align:top;");
		$BB->onclick(OnEvent::reload("Screen", "UPnPPlayerGUI;folder:".implode("$", $ex)));
		
		$BX = new Button("", "x", "touch");
		$BX->style("width:32px;text-overflow:hidden;overflow: hidden;white-space: nowrap;margin:10px;display:inline-block;border:1px solid #ccc;vertical-align:top;");
		$BX->loadPlugin("contentScreen", "mfheOverview");
		
		
		echo "<div style=\"height:60px;\" class=\"backgroundColor4\">
			<div style=\"float:right;\">".$BG.$BP.$BS."</div>";
		
		echo $BX;
		
		if(count($ex) > 0)
			echo $BB;
		#.$BL;
		
		echo $BQ.$BD.$BTV."</div>";
		
		
		
		echo "<div><div style=\"margin:10px;margin-left:0px;box-sizing:border-box;\">";
		echo "<div style=\"height:10px;\"></div>";
		
		
		
		if($ObjectID == "*"){
			$ObjectID = "0";
			
			if($UPnPSource->A("UPnPDefaultMediacenterDirectory") != "")
				$ObjectID = $UPnPSource->A("UPnPDefaultMediacenterDirectory");
		}
		
		
		if(!$filterSeries){
			foreach($xml->container AS $container){
				$BF = new Button($container->children("http://purl.org/dc/elements/1.1/"), "folder_stroke", "touch");
				$BF->onclick(OnEvent::reload("Screen", "UPnPPlayerGUI;folder:".$container->attributes()->id));
				$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");

				echo trim($BF);
			}
		
			echo "<div style=\"height:15px;\"></div>";
		
			foreach($series AS $name => $list){
				$action = OnEvent::reload("Screen", "_UPnPPlayerGUI;series:$name");

				$BF = new Button($name."<br><small style=\"color:grey;\">".count($list)." Folge".(count($list) != 1 ? "n" : "")."</small>", "list", "touch");
				$BF->onclick($action);
				$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");

				echo trim($BF);
			}
			
			echo "<div style=\"height:15px;\"></div>";
		} else 
			echo "<h1 class=\"prettyTitle\" style=\"margin-top:5px;padding-top:0;\">$filterSeries</h1>";
		
		$k = 0;
		foreach($entries AS $newName => $item){
			$played = anyC::getFirst("Userdata", "wert", $item->attributes()->id);
			
			$BF = new Button($newName, $played ? "check" : "document_alt_stroke", "touch");
			$BF->onclick(OnEvent::rme($UPnPSource, "readSetStart", array("'".$item->attributes()->id."'", "'".$UPnPTarget->getID()."'"), "function(){ ".OnEvent::rme($this, "played", array("'".$item->attributes()->id."'"))." \$j('#button$k span').removeClass('document_alt_stroke').addClass('check'); }"));
			$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
			$BF->id("button$k");
			
			echo trim($BF);
			$k++;
		}
		
		echo "</div></div>";
	}
	
	public function tvcontrolPopup(){
		$L = new HTMLTable(1);
		$L->useForSelection(false);
		
		$L->addRow("Aktivieren");
		$L->addCellStyle(1, "padding:15px;");
		$L->addCellEvent(1, "click", OnEvent::rme($this, "tvControl", ["'activate'"]));
		
		$L->addRow("Ausschalten");
		$L->addCellStyle(1, "padding:15px;");
		$L->addCellEvent(1, "click", OnEvent::rme($this, "tvControl", ["'stop_and_standby'"]));
		
		$L->addRow("Umschalten");
		$L->addCellStyle(1, "padding:15px;");
		$L->addCellEvent(1, "click", OnEvent::rme($this, "tvControl", ["'toggle'"]));
		
		$L->addRow("Lauter");
		$L->addCellStyle(1, "padding:15px;");
		$L->addCellEvent(1, "click", OnEvent::rme($this, "tvControl", ["'VolUp'"]));
		
		
		$L->addRow("Leiser");
		$L->addCellStyle(1, "padding:15px;");
		$L->addCellEvent(1, "click", OnEvent::rme($this, "tvControl", ["'VolDown'"]));
		
		echo $L;
	}
	
	public function tvControl($action){
		$target = mUserdata::getGlobalSettingValue("UPnPPlayerTarget", "");
		$UPnPTarget = anyC::getFirst("UPnP", "UPnPName", $target);
		
		$url = parse_url($UPnPTarget->A("UPnPLocation"));
		#$controlURL = $url["scheme"]."://".$url["host"].":".$url["port"].$this->Device->A("UPnP{$type}controlURL");
		
		switch ($action){
			case "activate":
			case "standby":
			case "toggle":
				file_get_contents('http://'.$url["host"].':8080/jsonrpc?request={"jsonrpc":"2.0","method":"Addons.ExecuteAddon","params":{"addonid":"script.json-cec","params":{"command":"'.$action.'"}},"id":1}');
			break;
		
			case "VolUp":
				file_get_contents('http://'.$url["host"].':8080/jsonrpc?request={ "jsonrpc": "2.0", "method": "Application.SetVolume", "params": { "volume": "increment" }, "id": 1 }');
			break;
		
			case "VolDown":
				file_get_contents('http://'.$url["host"].':8080/jsonrpc?request={ "jsonrpc": "2.0", "method": "Application.SetVolume", "params": { "volume": "decrement" }, "id": 1 }');
			break;
		}
	}
	
	public function played($id){
		
		$F = new Factory("Userdata");
		$F->sA("UserID", "-1");
		$F->sA("name", "UPnPPlayed");
		$F->sA("wert", basename($id));
		$F->store();
	}

	public function targetsPopup(){
		$AC = anyC::get("UPnP");
		$AC->addAssocV3("UPnPAVTransport", "=", "1");
		
		$L = new HTMLTable(1);
		$L->useForSelection(false);
		while($T = $AC->n()){
			$L->addRow($T->A("UPnPName"));
			
			$L->addCellStyle(1, "padding:15px;");
			$L->addCellEvent(1, "click", OnEvent::rme($this, "targetsSave", array("'".$T->A("UPnPName")."'"), OnEvent::closePopup("UPnPPlayer").OnEvent::reload("Screen")));
		}
		echo $L;
	}
	
	public function targetsSave($targetName){
		mUserdata::setUserdataS("UPnPPlayerTarget", $targetName, "", -1);
	}
	
	public function sourcesPopup(){
		$AC = anyC::get("UPnP");
		$AC->addAssocV3("UPnPContentDirectory", "=", "1");
		
		$L = new HTMLTable(1);
		$L->useForSelection(false);
		while($T = $AC->n()){
			$L->addRow($T->A("UPnPName"));
			
			$L->addCellStyle(1, "padding:15px;");
			$L->addCellEvent(1, "click", OnEvent::rme($this, "sourceSave", array("'".$T->A("UPnPName")."'"), OnEvent::closePopup("UPnPPlayer").OnEvent::reload("Screen")));
			#$L->addItemEvent("onclick", "UPnP.selectSource('".$T->getID()."', '".$T->A("UPnPName")."');");
			#$L->addItemStyle("cursor:pointer;padding:10px;");
		}
		echo $L;
	}
	
	public function sourceSave($sourceName){
		mUserdata::setUserdataS("UPnPPlayerSource", $sourceName, "", -1);
	}

}
