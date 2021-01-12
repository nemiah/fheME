<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */

class mphimGUI extends anyC implements iGUIHTMLMP2 {
	static $users;
	public function getHTML($id, $page){
		if(Session::physion() AND $id == -1)
			return OnEvent::script(OnEvent::frame("Screen", "mphim", "1"));
		
		if(Session::physion() AND $id == 1)
			return $this->chatView();
		
		$TID = BPS::getProperty("mphimGUI", "with", 0);
		$permissions = mUserdata::getPluginSpecificData("mphim");
		
		if($TID[0] != "g"){
			$this->addAssocV3("phimFromUserID", "=", Session::currentUser()->getID(), "AND", "1");
			$this->addAssocV3("phimToUserID", "=", $TID, "AND", "1");
			$this->addAssocV3("phimphimGruppeID", "=", "0", "AND", "1");
			$this->addAssocV3("phimFromUserID", "=", $TID, "OR", "2");
			$this->addAssocV3("phimToUserID", "=", Session::currentUser()->getID(), "AND", "2");
			$this->addAssocV3("phimphimGruppeID", "=", "0", "AND", "2");
		} else {
			$this->addAssocV3("phimphimGruppeID", "=", str_replace("g", "", $TID));
		}

		$this->addOrderV3("phimTime", "DESC");
		$this->addOrderV3("phimID", "DESC");
		$this->setFieldsV3(array(
			"phimRead",
			"phimFromUserID",
			"phimMessage",
			"DATE_FORMAT(FROM_UNIXTIME(phimTime), '%Y-%m-%d') AS grouper"
		));
				
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mphim");
		$gui->colWidth("phimRead", 20);
		$gui->name("phim");
		$gui->displayGroup("grouper", "mphimGUI::parserDG");
		
		$gui->options(true, true, false);
		
		$gui->parser("phimRead", "parserRead");
		$gui->parser("phimFromUserID", "parserFrom");
		
		$gui->attributes(array("phimRead", "phimFromUserID", "phimMessage"));
		
		$B = $gui->addSideButton("phim\nanzeigen", "newWindow");
		$B->newSession("phim", "lightCRM", "mphim", "Nachrichten", "./ubiquitous/phim/phim.ico");

		if($permissions["pluginSpecificCanCreateGroups"]){
			$B = $gui->addSideButton("Gruppen", "./ubiquitous/phim/gruppen.svg");
			$B->loadPlugin("contentRight", "mphimGruppe");
		}
		
		#$B = $gui->addSideButton("System-\nBenutzer", "./ubiquitous/phim/phimUser.png");
		#$B->loadPlugin("contentRight", "mphimUser");
		
		if($permissions["pluginSpecificCanHideUsers"]){
			$B = $gui->addSideButton("Benutzer\nausblenden", "./ubiquitous/phim/hidden.png");
			$B->loadPlugin("contentRight", "mphimUserHidden");
		}
		$AC = anyC::get("phimUserHidden");
		$hidden = $AC->toArray("phimUserHiddenUserID");
		
		$users = self::$users = Users::getUsersArray();

		$T = new HTMLTable(1, "Konversation mit");
		$T->setTableStyle("width:100%");
		$T->weight("light");
		$T->useForSelection(false);
		foreach($users AS $ID => $U){
			if(in_array($ID, $hidden))
				continue;
			
			$T->addRow(array($U));
			$T->addRowEvent("click", OnEvent::frame("contentRight", "mphim", "-1", 0, "", "mphimGUI;with:$ID"));
			if($ID."" === $TID."")
				$T->addRowClass ("highlight");
			
			$T->addCellStyle(1, "padding:3px;");	
		}
		
		$AC = anyC::get("phimGruppe");
		$AC->addAssocV3("INSTR(phimGruppeMembers, ';".Session::currentUser()->getID().";')", ">", "0");
			
		$TG = new HTMLTable(1, "Gruppen");
		$TG->setTableStyle("width:100%");
		$TG->weight("light");
		$TG->useForSelection(false);
		while($G = $AC->n()){
			$TG->addRow(array($G->A("phimGruppeName")));
			$TG->addRowEvent("click", OnEvent::frame("contentRight", "mphim", "-1", 0, "", "mphimGUI;with:g".$G->getID()));
			if("g".$G->getID() === $TID."")
				$TG->addRowClass ("highlight");
			
			$TG->addCellStyle(1, "padding:3px;");
		}
		
		$gui->addSideRow($T);
		$gui->addSideRow($TG);
		
		return $gui->getBrowserHTML($id);
	}
	
	private function chatView(){
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
	
		$Users = Users::getUsersArray();
		
		$AC = anyC::get("phimGruppe");
		$AC->addAssocV3("INSTR(phimGruppeMembers, ';".Session::currentUser()->getID().";')", ">", "0");
		$AC->addAssocV3("INSTR(phimGruppeClosed, ';".Session::currentUser()->getID().";')", "=", "0");
			
		$groups = array();
		$chatGroups = "";
		while($G = $AC->n()){
			$groups[] = $G->getID();
			$chatGroups .= $this->chatPopup("g".$G->getID());
		}
		
		asort($Users);
		
		$hidden = $this->hiddenUsers();
		$chatUsers = "";
		foreach($Users AS $ID => $U){
			if($ID == Session::currentUser()->getID())
				continue;
			
			if(isset($hidden[$ID]))
				continue;
			
			#$I = new HTMLInput("newMessage");
			#$I->style("width:100%;background-color:white;");
			#$I->onEnter("phimChat.send('$ID', \$j(this));");
			
			#$BC = new Button("Fenster schließen", "x", "iconic");
			#$BC->style("float:right;");
			#$BC->rmePCR("mphim", "-1", "windowStatus", [$ID, "'hidden'"], "function(){ \$j('#chatWindow$ID').hide(); }");
			
			$chatUsers .= $this->chatPopup($ID);
		}
		
		$BU = new Button("Benutzer", "users", "icon");
		$BU->style("margin:10px;");
		$BU->popup("", "Benutzer", "mphim", "-1", "usersPopup", ["'users'"]);
		
		$BG = new Button("Gruppen", "./ubiquitous/phim/gruppen.svg", "icon");
		$BG->style("margin:10px;margin-left:0;height:32px;");
		$BG->popup("", "Gruppen", "mphim", "-1", "usersPopup", ["'groups'"]);
		
		$BR = new Button("Rückfragen", "./ubiquitous/phim/callbacks.svg", "icon");
		$BR->style("margin:10px;margin-left:0;height:32px;");
		$BR->popup("", "Rückfragen", "mphim", "-1", "usersPopup", ["'callbacks'"]);
		
		$BNotificationsConfig = new Button("Benachrichtigungen konfigurieren", "rss", "iconicL");
		$BNotificationsConfig->style("margin:10px;margin-left:0;height:32px;");
		$BNotificationsConfig->popup("", "Benachrichtigungen konfigurieren", "mphim", "-1", "configureDesktopNotificationsPopup");
		$BNotificationsConfig->id("BNotificationsConfig");
		
		$BEmoji = new Button("Emojis anzeigen", "./ubiquitous/phim/emoji.svg", "icon");
		$BEmoji->style("margin:10px;margin-left:0;height:32px;");
		$BEmoji->popup("", "Emojis", "mphim", "-1", "emojisPopup", [], "", "{remember: true, hPosition: 'right'}");
		
		$savedPermission = mUserdata::getUDValueS("phimUseNotifications", "default");
		
		$content = '
		<script type="text/javascript" src="./plugins/Websocket/autobahn.min.js"></script>
		<script type="text/javascript" src="./ubiquitous/phim/phimChat.js"></script>
		<script type="text/javascript" src="./ubiquitous/phim/emojiPicker.js"></script>

		<script type="text/javascript">
			$j(function(){
				phimChat.init(
					"ws'.($S->A("WebsocketSecure") ? "s" : "").'://'.$S->A("WebsocketServer").":".$S->A("WebsocketServerPort").'/",
					"'.$S->A("WebsocketRealm").'",
					"'.Util::eK().'",
					'.Session::currentUser()->getID().', 
					"'.Session::currentUser()->A("name").'",
					"'.$S->A("WebsocketToken").'",
					['.implode(",", $groups).'],
					"'.($savedPermission == "granted" ? "true" : "false").'");
						
				phimChat.scroll("chatText0");
				//$j("#userList").css("height", contentManager.maxHeight());
				phimChat.draggable();
				phimChat.updateUnread();
				/*var link = document.querySelector("link[rel*=\'shortcut icon\']");// || document.createElement(\'link\');
				link.href = \'./lightCRM/Mail/mMail.ico\';
				document.getElementsByTagName(\'head\')[0].appendChild(link);*/
			});
			
		</script>';
		
		$content .= "
			<style type=\"text/css\">
			p {
				padding:5px;
			}
			
			.username {
				font-weight:bold;
			}
			
			.time {
				color:grey;
				font-size:.8em;
				display:block;
			}
			
			.chatWindow {
			}
			
			.chatHeader {
				padding:5px;
				cursor:move;
			 }
			
			.chatContent{
				height:320px;
				overflow-y: auto;
				padding-bottom:5px;
				background-color:white;
			}
			
			.chatMessage {
				padding:5px;
			}

			.newMessage {
				display:none;
			}
			
			.highlight .newMessage {
				display:block;
			}
			
			#darkOverlay {
				position:fixed;
				top:0;
				left:0;
			}
			
			.picker {
				position:absolute;
				background-color:white;
				width:400px;
				margin-top:10px;
				overflow:auto;
				max-height:150px;
			}

			.emoji {
				width: auto;
				display: inline-block;
				font-size: 25px;
				padding: 4px 8px;
				margin: 4px;
				border: 2px solid grey;
				cursor: pointer;
			}

			.emoji:hover {
				border-color: hotpink;
			}
		</style>
		<div style=\"\">
			$BU$BG$BR$BEmoji$BNotificationsConfig
			$chatUsers$chatGroups
		</div>
		<div id=\"offlineOverlay\" style=\"background-color:rgba(0,0,0,.7);color:white;z-index:10000;position:absolute;top:0;left:0;\"></div>";
		
		return $content;
	}
	
	public function emojisPopup(){
		$data = file_get_contents(__DIR__."/emojiAll.json");

		$all = json_decode($data);
		
		$category = [];
		foreach($all->emojis AS $emoji){
			$ex = explode("(", $emoji->category);
			$main = trim($ex[0]);
			$sub = trim($ex[1], ")")
					;
			if(!isset($category[$main]))
				$category[$main] = [];
			
			if(!isset($category[$main][$sub]))
				$category[$main][$sub] = [];
			
			$category[$main][$sub][] = $emoji;
		}
		
		$i = 0;
		foreach($category AS $name => $sub){
			if($name == "Component")
				continue;
			
			echo "<p class=\"prettySubtitle\" onmouseover=\"\$j(this).addClass('backgroundColor4')\" onmouseout=\"\$j(this).removeClass('backgroundColor4')\" style=\"cursor:pointer;\" onclick=\"\$j('.emojiCat').hide(); \$j('#emojiCat_$i').toggle();\">$name</p>";
			echo "<p style=\"font-size:23px;display:none;\" id=\"emojiCat_$i\" class=\"emojiCat\">";
			foreach($sub AS $emojis){
				foreach($emojis AS $emoji){
					if(strpos($emoji->shortname, "skin_tone") !== false)
						continue;
					
					echo "<span title=\"$emoji->shortname\">".$emoji->html."</span> ";
				}

			}
			echo "</p>";
			
			$i++;
		}
	}
	
	public function configureDesktopNotificationsPopup(){
		echo "<p>In diesem Fenster können Sie Desktop-Benachrichtungen konfigurieren. Damit erhalten Sie eine Anzeige auf Ihrem Desktop, wenn eine neue E-Mail eingetroffen ist.</p>
			<p>Die Benachrichtigungen sind aktuell <span id=\"DNStatus\" style=\"font-weight:bold;\"></span></p>";
		
		$savedPermission = mUserdata::getUDValueS("phimUseNotifications", "default");
		
		$BActivate = new Button("Aktivieren", "bestaetigung");
		$BActivate->style("margin:10px;display:none;");
		$BActivate->onclick("Interface.notifyRequest(function(perm){ ".OnEvent::rme($this, "saveDesktopNotifications", array("perm"), OnEvent::reloadPopup("mphim"))." });");
		$BActivate->id("DNActivate");
		
		echo $BActivate;
		
		$BDisable = new Button("Deaktivieren", "stop");
		$BDisable->style("margin:10px;display:none;");
		$BDisable->rmePCR("mphim", "-1", "saveDesktopNotifications", array("'denied'"), OnEvent::reloadPopup("mphim"));
		$BDisable->id("DNDisable");
		
		echo $BDisable;
		
		echo OnEvent::script("if(typeof Notification == 'function') {
			if(Interface.notifyPermission() == 'granted' && '$savedPermission' != 'denied'){
				\$j('#DNDisable').show();
				\$j('#DNStatus').html('aktiviert');
				phimChat.showNotification = true;
			} else {
				\$j('#DNActivate').show();
				\$j('#DNStatus').html('deaktiviert');
				phimChat.showNotification = false;
			}
		}");
	}
	
	public function saveDesktopNotifications($permission){
		mUserdata::setUserdataS("phimUseNotifications", $permission);
	}
	
	public function chatPopup($target, $unhide = false, $echo = false){
		$about = "";
		$content = "";
		$Users = Users::getUsersArray();
		#$Users[0] = "Alle";
		$BOn = "";
		$BOff = "";
		if($target[0] == "g"){
			$GID = substr($target, 1);
			$G = new phimGruppe($GID);
			
			#if($GID == 0)
			#	$G->changeA("phimGruppeName", "Alle");
			
			$name = $G->A("phimGruppeName");
			
			if($G->A("phimGruppeClassName") == "DBMail"){
				$BRC = new Button("Erledigt", "bestaetigung", "icon");
				$BRC->style("float:right;margin-left:10px;");
				$BRC->className("backgroundColor2");
				$BRC->rmePCR("mphim", -1, "callbackClose", [$GID], "\$j('#chatWindow".$target."').hide();");

				$Mail = new DBMail($G->A("phimGruppeClassID"));

				$BShow = new Button("Mail anzeigen", "./lightCRM/Mail/Mail.png", "icon");
				$BShow->popup("", "Mail anzeigen", "mDBMail2Object", "-1", "showMail", array($G->A("phimGruppeClassID"), "''", 1), "", "Mail.PopupOptions");
				$BShow->style("float:right;margin-left:10px;");
			
				$BAnswer = new Button("Antworten", "./lightCRM/Mail/images/mail-reply-sender.png", "icon");
				$BAnswer->popup("newMail", "Antworten", "Mail", -1, "writeMail", array($Mail->A("DBMailMailKontoID"), $G->A("phimGruppeClassID"), "'answer'"), null, "Mail.PopupOptions");
				$BAnswer->style("float:right;margin-left:10px;");
				if($Mail->A("DBMailAnswered"))
					$BAnswer->className ("confirm");
				
				$about = "<div class=\"backgroundColor2\" style=\"padding:5px;height:32px;\">$BShow$BAnswer$BRC E-Mail von<br>".$Mail->A("DBMailFromName")."</div>";
			}	
			
			$ACS = anyC::get("phim");
			$ACS->addAssocV3("phimToUserID", "=", "0");
			$ACS->addAssocV3("phimphimGruppeID", "=", $GID);
			$ACS->addOrderV3("phimID", "DESC");
			$ACS->setLimitV3(50);
			while($M = $ACS->n())
				$content = "<div class=\"chatMessage ".(($M->A("phimFromUserID") != Session::currentUser()->getID() AND strpos($M->A("phimReadBy"), ";".Session::currentUser()->getID().";") === false) ? "highlight" : "")."\"><span class=\"time\">".Util::CLDateTimeParser($M->A("phimTime"))."</span><span class=\"username\">".$Users[$M->A("phimFromUserID")].": </span>".$M->A("phimMessage")."</div>".$content;

			if($unhide){
				$G->changeA("phimGruppeClosed", str_replace(";".Session::currentUser()->getID().";", "", $G->A("phimGruppeClosed")));
				$G->saveMe();
			}
		} else {
			$name = $Users[$target];
			
			$BOn = new Button("Online", "./ubiquitous/phim/userOnline.png", "icon");
			$BOn->style("float:left;display:none;margin-right:3px;margin-top:-2px;");
			$BOn->className("online_$target");

			$BOff = new Button("Offline", "./ubiquitous/phim/userOffline.png", "icon");
			$BOff->style("float:left;margin-right:3px;margin-top:-2px;");
			$BOff->className("offline_$target");
		
			$content = "";
			$AC = anyC::get("phim");
			$AC->addAssocV3("phimFromUserID", "=", Session::currentUser()->getID(), "AND", "1");
			$AC->addAssocV3("phimToUserID", "=", $target, "AND", "1");
			$AC->addAssocV3("phimFromUserID", "=", $target, "OR", "2");
			$AC->addAssocV3("phimToUserID", "=", Session::currentUser()->getID(), "AND", "2");
			$AC->addOrderV3("phimID", "DESC");
			$AC->setLimitV3(50);
			while($M = $AC->n())
				$content = phim::formatMessage($M).$content;
			
		}
		
		if($unhide)
			mUserdata::setUserdataS("chatWindow".$target, "visible");
		
		
		$I = new HTMLInput("newMessage", "textarea");
		$I->style("width:100%;background-color:#f4f4f4;border:0px;padding:8px;resize: vertical;font-size:1.4em;height:100px;");
		#$I->onEnter("phimChat.send('$target', \$j(this));");
		$I->onkeyup("if(\$j(this).val().trim() !== '') phimChat.writing('$target'); if(!event.ctrlKey && event.keyCode == 13) { phimChat.send('$target', \$j(this)); }");
		$I->id("newMessageTA$target");

		$BC = new Button("Fenster schließen", "x", "iconic");
		$BC->style("float:right;");
		$BC->rmePCR("mphim", "-1", "windowStatus", ["'$target'", "'hidden'"], "function(){ \$j('#chatWindow".$target."').hide(); }");
			
			
		$content = "<div style=\"width:400px;".mUserdata::getUDValueS("chatWindowPosition".$target, "").(mUserdata::getUDValueS("chatWindow".$target) == "hidden" ? "display:none;" : "")."\" id=\"chatWindow".$target."\" class=\"chatWindow popup\" style=\"\">
				<div class=\"chatHeader backgroundColor1\">$BOn$BOff$BC".$name."<div style=\"clear:both;\"></div></div>
				$about
				<div class=\"chatContent\" style=\"".(!$about ? "height:calc(320px + 32px + 10px);" : "")."\" id=\"chatText".$target."\">$content</div>
				$I
			</div>";
		
		if($echo)
			echo $content;
		
		return $content.OnEvent::script("\$j(function(){ phimChat.scroll('chatText".$target."');}); emojiPicker.init('newMessageTA$target');");
	}
	
	public function callbackClose($GID){
		$G = new phimGruppe($GID);
		$G->changeA("phimGruppeClosed", $G->A("phimGruppeClosed").";".Session::currentUser()->getID().";");
		$G->saveMe();
		
		$this->windowStatus("g$GID", "hidden");
	}
	
	public function usersPopup($target){
		$Users = Users::getUsersArray();
		
		$B = new Button("Neue Nachricht", "star", "iconic");
		$B->style("color:orange;float:right;margin-top:-3px;");
		$B->className("newMessage");
		
		$hidden = $this->hiddenUsers();
		
		$L = new HTMLList();
		$L->setListID("userList");
		$L->addListStyle("overflow:auto;box-sizing:border-box;padding:3px;");
		#$L->addListClass("backgroundColor2");
		$L->noDots();
		
		if($target == "groups" OR $target == "callbacks"){
			$AC = anyC::get("phimGruppe");
			$AC->addAssocV3("INSTR(phimGruppeMembers, ';".Session::currentUser()->getID().";')", ">", "0");
			#$AC->addAssocV3("INSTR(phimGruppeClosed, ';".Session::currentUser()->getID().";')", "=", "0");
			if($target == "callbacks")
				$AC->addAssocV3("phimGruppeClassName", "!=", "");
			else
				$AC->addAssocV3("phimGruppeClassName", "=", "");
			while($G = $AC->n()){
				$L->addItem($B.$G->A("phimGruppeName"));
				$L->addItemEvent("onclick", "phimChat.openWindow('g".$G->getID()."');");#.OnEvent::rme($this, "windowStatus", ["'g".$G->getID()."'", "'visible'"]));
				$L->addItemStyle("cursor:pointer;margin-left:0;padding:5px;");
				$L->setItemID("groupg".$G->getID());
			}
		}
		
		asort($Users);
		if($target == "users"){
			foreach($Users AS $ID => $U){
				if($ID == Session::currentUser()->getID())
					continue;

				if(isset($hidden[$ID]))
					continue;

				$L->addItem($B.$U);
				$L->addItemEvent("onclick", OnEvent::rme("phim", "setRead", $ID)."\$j(this).removeClass('highlight'); \$j('#chatWindow$ID').show(); phimChat.scroll('chatText$ID'); \$j('#userList .backgroundColor0').removeClass('backgroundColor0'); \$j(this).addClass('backgroundColor0');".OnEvent::rme($this, "windowStatus", ["'$ID'", "'visible'"]));
				$L->addItemStyle("cursor:pointer;margin-left:0;padding:5px;");
				$L->setItemID("user$ID");
			}
		}
		
		echo $L;
	}
	
	private function hiddenUsers(){
		$AC = anyC::get("phimUserHidden");
		$hidden = array();
		while($h = $AC->n())
			$hidden[$h->A("phimUserHiddenUserID")] = true;
		
		return $hidden;
	}
	
	public function settingsPopup(){
		$F = new HTMLForm("settings", array("autostart"));
		$F->getTable()->setColWidth(1, 120);
		$F->useRecentlyChanged();
		
		$F->setValue("autostart", mUserdata::getUDValueS("phimAutostart", "0"));
		$F->setType("autostart", "checkbox");
		
		$F->setSaveRMEPCR("Speichern", "", "mphim", "-1", "settingsSave", OnEvent::closePopup("mphim"));
		
		echo $F;
	}
	
	public function settingsSave($autostart){
		mUserdata::setUserdataS("phimAutostart", $autostart);
		
		Red::messageSaved();
	}
	
	public function testAutostart(){
		echo mUserdata::getUDValueS("phimAutostart", "0");
	}
	
	public function windowStatus($name, $state){
		mUserdata::setUserdataS("chatWindow$name", $state);
	}
	
	public function windowPosition($name, $left, $top){
		mUserdata::setUserdataS("chatWindowPosition$name", "left:".$left."px;top:".$top."px;");
	}
	
	public static function parserDG($w){
		$ex = explode("-", $w);
		$D = new Datum(mktime(0, 1, 0, $ex[1], $ex[2], $ex[0]));
		
		return Util::CLDateParser($D->time());
	}
	
	public static function parserFrom($w, $l, $E){
		if($w == Session::currentUser()->getID())
			return "Ich";
		
		return self::$users[$w];
	}
	
	public static function parserRead($w){
		return Util::catchParser($w);
	}
}
?>