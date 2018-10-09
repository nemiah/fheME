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

class mPremiumizeGUI extends UnpersistentClass implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$bps = $this->getMyBPSData();
		
		if(time() - mUserdata::getGlobalSettingValue("mPremiumizeLastScan") > 4 * 3600)
			$this->scan();
		
		$target = mUserdata::getGlobalSettingValue("mPremiumizeTarget", "");
		
		$login = LoginData::get("P.meIDAndPIN");
		
		$BL = new Button("Login", "wrench", "iconicL");
		$BL->popup("", "Zugangsdaten", "LoginData", !$login ? "-1" : $login->getID(), "getPopup", "", "LoginDataGUI;preset:P.meIDAndPIN");
		$BL->style("margin:10px;display:inline-block;");
		
		#var_dump($login);
		
		$BL2 = new Button("Zugangsdaten", "system");
		$BL2->popup("", "Zugangsdaten", "LoginData", !$login ? "-1" : $login->getID(), "getPopup", "", "LoginDataGUI;preset:P.meIDAndPIN");
		$BL2->style("margin:10px;display:inline-block;");
			
		if(!$login)
			die($BL2);
		
		define("PREMIUMIZE_CUSTOMER_ID", $login->A("benutzername"));
		define("PREMIUMIZE_PIN", $login->A("passwort"));
		
		$BG = new Button("Play", "play", "touch");
		$BG->style("width:150px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BG->onclick(OnEvent::rme($this, "restart"));
		
		$BP = new Button("Pause", "pause", "touch");
		$BP->style("width:150px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BP->onclick(OnEvent::rme($this, "pause"));
		
		$BS = new Button("Stop", "stop", "touch");
		$BS->style("width:150px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BS->onclick(OnEvent::rme($this, "stop"));
		
		try {
			$info = PremiumizeAPI::getFolderInfo(isset($bps["id"]) ? $bps["id"] : "");
		} catch (Exception $e){
			die("<p class=\"highlight\">".$e->getMessage()."</p>".$BL2);
		}
		
		$BB = new Button("Zurück", "arrow_left", "touch");
		$BB->style("width:120px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BB->onclick(OnEvent::reload("Screen", "mPremiumizeGUI;id:".$info->parent_id));
		
		$BD = new Button($target == "" ? "Abspielen auf" : $target, "target", "touch");
		$BD->style("width:200px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BD->popup("", "Abspielen auf", "mPremiumize", -1, "devicePopup");
		
		$BX = new Button("Schließen", "x", "touch");
		$BX->style("width:120px;margin:10px;display:inline-block;border:1px solid #ccc;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
		$BX->loadPlugin("contentScreen", "mfheOverview");
		
		
		echo "<div style=\"height:60px;\" class=\"backgroundColor4\">
			<div style=\"float:right;\">".$BG.$BP.$BS."</div>";
		
		$showSeries = BPS::getProperty("mPremiumizeGUI", "series", "");
		
		if($info->name != "root" OR $showSeries){
			if($showSeries)
				$BB->onclick(OnEvent::reload("Screen", "mPremiumizeGUI;-"));
			
			echo $BB;
		} else
			echo $BX;
		
		echo "$BD$BL</div>";
		
		$items = PremiumizeAPI::getFolderContent(isset($bps["id"]) ? $bps["id"] : "");
		
		echo "<div><div style=\"margin:10px;margin-left:0px;box-sizing:border-box;\">";
		echo "<div style=\"height:10px;\"></div>";
		
		
		if($showSeries){
			echo "<h1 class=\"prettyTitle\" style=\"margin-top:5px;padding-top:0;\">$showSeries</h1>";
			
			$series = $this->findSeries($items);
			$items = $series[$showSeries];
			
			foreach($items AS $k => $f){
				if(get_class($f) != "PremiumizeFileFile")
					continue;

				$played = anyC::getFirst("Userdata", "wert", basename($f->stream_link));

				$BF = new Button(prettifyDB::apply("seriesEpisodeNameDownloaded", $f->name), $played ? "check" : "document_alt_stroke", "touch");
				$BF->onclick(OnEvent::rme($this, "play", array("'$f->stream_link'"), "function(){ \$j('#button$k span').removeClass('document_alt_stroke').addClass('check'); }"));
				$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
				$BF->id("button$k");

				echo trim($BF);
			}
		} else {
			foreach($items AS $f){
				if(get_class($f) != "PremiumizeFolder")
					continue;

				$BF = new Button($f->name, "folder_stroke", "touch");
				$BF->onclick(OnEvent::reload("Screen", "mPremiumizeGUI;id:".$f->id));
				$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");

				echo trim($BF);
			}

			echo "<div style=\"height:15px;\"></div>";

			#print_r($items);
			$series = $this->findSeries($items);

			foreach($series AS $seriesName => $content){

				$BF = new Button($seriesName."<br><small style=\"color:grey;\">".count($content)." Folge".(count($content) != 1 ? "n" : "")."</small>", "list", "touch");
				$BF->onclick(OnEvent::reload("Screen", "_mPremiumizeGUI;series:$seriesName"));
				$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
				#$BF->id("button$k");

				echo trim($BF);
			}

			echo "<div style=\"height:15px;\"></div>";

			foreach($items AS $k => $f){
				if(get_class($f) != "PremiumizeFileFile")
					continue;

				$played = anyC::getFirst("Userdata", "wert", basename($f->stream_link));

				$BF = new Button(prettifyDB::apply("seriesEpisodeNameDownloaded", $f->name), $played ? "check" : "document_alt_stroke", "touch");
				$BF->onclick(OnEvent::rme($this, "play", array("'$f->stream_link'"), "function(){ \$j('#button$k span').removeClass('document_alt_stroke').addClass('check'); }"));
				$BF->style("width:calc(25% - 10px);margin-left:10px;display:inline-block;vertical-align:top;box-sizing:border-box;text-overflow:hidden;overflow: hidden;white-space: nowrap;");
				$BF->id("button$k");

				echo trim($BF);
			}
		}
		
		echo "</div></div>";
		
	}

	private function findSeries(&$items){
		$series = array();
		foreach($items AS $k => $f){
			if(get_class($f) != "PremiumizeFileFile")
				continue;
			
			$newName = prettifyDB::apply("seriesEpisodeNameDownloaded", $f->name);
			
			preg_match("/(.*) S[0-9]{2}E[0-9]{2}/", $newName, $matches);
			#var_dump($matches);
			
			if(isset($matches[1])){
				if(!isset($series[$matches[1]]))
					$series[$matches[1]] = array();
			
				$series[$matches[1]][$newName] = $f;
				unset($items[$k]);
			}
			
		}
		#$lastName = null;
		/*foreach($items AS $newName => $item){
			preg_match("/(.*) S[0-9]{2}E[0-9]{2}/", $newName, $matches);
			#print_r($matches);
			if(isset($matches[1])){
				if(!isset($series[$matches[1]]))
					$series[$matches[1]] = array();
			
				$series[$matches[1]][$newName] = $item;
				unset($entries[$newName]);
			}
		}*/
		
		return $series;
	}
	
	private function scan(){
		require_once Util::getRootPath()."fheME/Chromecast/CastV2inPHP/Chromecast.php";
		$devices = Chromecast::scan();
		
		mUserdata::setUserdataS("mPremiumizeLastScan", time(), "", -1);
		mUserdata::setUserdataS("mPremiumizeDevices", json_encode($devices, JSON_UNESCAPED_UNICODE), "", -1);
	}
	
	public function play($url){
		list($ip, $port) = $this->findIPAndPort();

		$F = new Factory("Userdata");
		$F->sA("UserID", "-1");
		$F->sA("name", "PremiumizePlayed");
		$F->sA("wert", basename($url));
		$F->store();
		
		
		$message = new stdClass();
		$message->action = "play";
		$message->server = $ip;
		$message->port = $port;
		$message->url = $url;
		
		$this->send($message);
	}

	public function restart(){
		list($ip, $port) = $this->findIPAndPort();
		
		$message = new stdClass();
		$message->action = "restart";
		$message->server = $ip;
		$message->port = $port;
		
		$this->send($message);
	}

	public function stop(){
		list($ip, $port) = $this->findIPAndPort();
		
		$message = new stdClass();
		$message->action = "stop";
		$message->server = $ip;
		$message->port = $port;
		
		$this->send($message);
	}

	public function pause(){
		list($ip, $port) = $this->findIPAndPort();
		
		$message = new stdClass();
		$message->action = "pause";
		$message->server = $ip;
		$message->port = $port;
		
		$this->send($message);
	}
	
	public function send($message){
		$ip = msg_get_queue(12340);
		var_dump(msg_send($ip, 8, $message, true, false, $err)); 

		var_dump(msg_receive($ip, 0, $msgtype, 1000, $message, true, null, $err));
		var_dump($message);
		
	}
	
	private function findIPAndPort(){
		if(time() - mUserdata::getGlobalSettingValue("mPremiumizeLastScan") > 4 * 3600)
			$this->scan();
		
		$target = mUserdata::getGlobalSettingValue("mPremiumizeTarget", "");
		$devices = json_decode(mUserdata::getGlobalSettingValue("mPremiumizeDevices", "[]"));
		foreach($devices AS $D){
			if($D->friendlyname != $target)
				continue;
			
			return array($D->ip, $D->port);
		}
		
		return null;
	}
	
	public function devicePopup(){
		#if(time() - mUserdata::getGlobalSettingValue("mPremiumizeLastScan") > 4 * 3600)
		$this->scan();
		
		$devices = json_decode(mUserdata::getGlobalSettingValue("mPremiumizeDevices", "[]"));
		$T = new HTMLTable(1);
		$T->useForSelection(false);
		
		foreach($devices AS $D){
			$T->addRow(array($D->friendlyname." (".$D->ip.")"));
			$T->addCellStyle(1, "padding:15px;");
			
			$T->addCellEvent(1, "click", OnEvent::rme($this, "deviceSave", array("'".$D->friendlyname."'"), OnEvent::closePopup("mPremiumize").OnEvent::reload("Screen")));
		}
		
		echo $T;
	}
	
	public function deviceSave($name){
		mUserdata::setUserdataS("mPremiumizeTarget", $name, "", -1);
	}
}
?>