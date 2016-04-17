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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class mphimGUI extends anyC implements iGUIHTMLMP2 {
	static $users;
	public function getHTML($id, $page){
		$TID = BPS::getProperty("mphimGUI", "with", 0);
		
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
		#$this->addJoinV3("User", $field1)
		#$this->addOrderV3("phimToUserID", "DESC");
		#$this->addOrderV3($order)
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
		
		$B = $gui->addSideButton("Gruppen", "./ubiquitous/phim/group.png");
		$B->loadPlugin("contentRight", "mphimGruppe");
		
		$B = $gui->addSideButton("System-\nBenutzer", "./ubiquitous/phim/phimUser.png");
		$B->loadPlugin("contentRight", "mphimUser");
		
		$B = $gui->addSideButton("Benutzer\nausblenden", "./ubiquitous/phim/hidden.png");
		$B->loadPlugin("contentRight", "mphimUserHidden");
		
		$B = $gui->addSideButton("phim\nanzeigen", "new");
		$B->onclick("windowWithRme('mphim', -1, 'chatPopup', [], '', 'window', {height: 300, width:550, left: \$j.jStorage.get('phimX', 20), top: \$j.jStorage.get('phimY', 20), name: 'phim', scroll: false});");
		
		$B = $gui->addSideButton("Config-Datei", "new");
		$B->windowRme("mphim", "-1", "getConfigFile");
		
		$users = self::$users = Users::getUsersArray("Alle", true);

		$T = new HTMLTable(1, "Konversation mit");
		$T->setTableStyle("width:100%");
		$T->weight("light");
		$T->useForSelection(false);
		foreach($users AS $ID => $U){
			$T->addRow(array($U));
			$T->addRowEvent("click", OnEvent::frame("contentRight", "mphim", "-1", 0, "", "mphimGUI;with:$ID"));
			if($ID."" === $TID."")
				$T->addRowClass ("highlight");
			
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
			
		}
		
		$gui->addSideRow($T);
		$gui->addSideRow($TG);
		
		return $gui->getBrowserHTML($id);
	}
	
	public function getConfigFile(){
		$T = mUserdata::getGlobalSettingValue("phimServerToken");
		if(!$T){
			mUserdata::setUserdataS("phimServerToken", Util::genPassword(100), "", -1);
			$T = mUserdata::getGlobalSettingValue("phimServerToken");
		}
		
		header('Content-disposition: attachment; filename="phim.exe.config"');
		header('Content-type: "text/xml"; charset="utf8"');
		
		echo '﻿<?xml version="1.0" encoding="utf-8" ?>
<configuration>
    <configSections>
        <sectionGroup name="userSettings" type="System.Configuration.UserSettingsGroup, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" >
            <section name="phim.Properties.Settings" type="System.Configuration.ClientSettingsSection, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" allowExeDefinition="MachineToLocalUser" requirePermission="false" />
        </sectionGroup>
    
        <sectionGroup name="applicationSettings" type="System.Configuration.ApplicationSettingsGroup, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" >
            <section name="phim.Properties.Settings" type="System.Configuration.ClientSettingsSection, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" requirePermission="false" />
        </sectionGroup>
    </configSections>
    <startup> 
        <supportedRuntime version="v4.0" sku=".NETFramework,Version=v4.5.2" />
    </startup>
    <userSettings>
        <phim.Properties.Settings>
            <setting name="server" serializeAs="String">
                <value />
            </setting>
            <setting name="user" serializeAs="String">
                <value />
            </setting>
            <setting name="password" serializeAs="String">
                <value />
            </setting>
            <setting name="cloud" serializeAs="String">
                <value />
            </setting>
            <setting name="position" serializeAs="String">
                <value>20, 20</value>
            </setting>
        </phim.Properties.Settings>
    </userSettings>
<applicationSettings>
        <phim.Properties.Settings>
                <setting name="authServerUrl" serializeAs="String">
                        <value>'.$_SERVER["HTTP_HOST"].'</value>
                </setting>
                <setting name="authServerToken" serializeAs="String">
                        <value>'.$T.'</value>
                </setting>
                <setting name="authServerCloud" serializeAs="String">
                        <value>'.(isset($_SESSION["phynx_customer"]) ? $_SESSION["phynx_customer"] : "").'</value>
                </setting>
        </phim.Properties.Settings>
    </applicationSettings>
</configuration>';
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
	
	public function chatPopup($root = ""){
	
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
	
	
		$I = new HTMLInput("newMessage");
		$I->style("width:100%;background-color:white;");
		$I->onEnter("phimChat.send();");
		
		$IC = new HTMLInput("channel", "hidden", "0");
		$IC->id("channel");
		
		$B = new Button("Neue Nachricht", "star", "iconic");
		$B->style("color:orange;float:right;margin-top:-3px;");
		$B->className("newMessage");
		
		$BOn = new Button("Online", "$root../ubiquitous/phim/userOnline.png", "icon");
		$BOn->style("float:left;display:none;margin-right:3px;margin-top:-2px;");
		$BOn->className("online");
		
		$BOff = new Button("Offline", "$root../ubiquitous/phim/userOffline.png", "icon");
		$BOff->style("float:left;margin-right:3px;margin-top:-2px;");
		$BOff->className("offline");
		
		$AC = anyC::get("phimUserHidden");
		$hidden = array();
		while($h = $AC->n())
			$hidden[$h->A("phimUserHiddenUserID")] = true;
		
		$Users = Users::getUsersArray();
		$L = new HTMLList();
		$L->setListID("userList");
		$L->addListStyle("overflow:auto;box-sizing:border-box;");
		$L->noDots();
		
		$L->addItem($B."Alle");
		$L->addItemEvent("onclick", "\$j(this).removeClass('highlight'); \$j('.chatWindow').hide(); \$j('#chatText0').show(); phimChat.scroll('chatText0'); \$j('#userList .backgroundColor0').removeClass('backgroundColor0'); \$j(this).addClass('backgroundColor0'); \$j('#channel').val('0');");
		$L->addItemStyle("cursor:pointer;margin-left:0;padding:5px;");
		$L->addItemClass("backgroundColor0");
		$L->setItemID("user0");
		
		
		
		$content = "";
		$AC = anyC::get("phim");
		$AC->addAssocV3("phimToUserID", "=", "0");
		$AC->addAssocV3("phimphimGruppeID", "=", "0");
		$AC->addOrderV3("phimID", "DESC");
		$AC->setLimitV3(50);
		while($M = $AC->n())
			$content = "<div><span class=\"username\">".(isset($Users[$M->A("phimFromUserID")]) ? $Users[$M->A("phimFromUserID")] : "Unbekannt").": </span>".$M->A("phimMessage")."</div>".$content;
		
		
		$chatAll = "<div class=\"chatWindow\" id=\"chatText0\">$content</div>";
		
		
		$AC = anyC::get("phimGruppe");
		$AC->addAssocV3("INSTR(phimGruppeMembers, ';".Session::currentUser()->getID().";')", ">", "0");
			
		$groups = array();
		$chatGroups = "";
		while($G = $AC->n()){
			$L->addItem($B.$G->A("phimGruppeName"));
			$L->addItemEvent("onclick", "\$j(this).removeClass('highlight'); \$j('.chatWindow').hide(); \$j('#chatTextg".$G->getID()."').show(); phimChat.scroll('chatTextg".$G->getID()."'); \$j('#userList .backgroundColor0').removeClass('backgroundColor0'); \$j(this).addClass('backgroundColor0'); \$j('#channel').val('g".$G->getID()."');");
			$L->addItemStyle("cursor:pointer;margin-left:0;padding:5px;");
			#$L->addItemClass("backgroundColor0");
			$L->setItemID("groupg".$G->getID());
			
			$content = "";
			$ACS = anyC::get("phim");
			$ACS->addAssocV3("phimToUserID", "=", "0");
			$ACS->addAssocV3("phimphimGruppeID", "=", $G->getID());
			$ACS->addOrderV3("phimID", "DESC");
			$ACS->setLimitV3(50);
			while($M = $ACS->n())
				$content = "<div><span class=\"username\">".$Users[$M->A("phimFromUserID")].": </span>".$M->A("phimMessage")."</div>".$content;
			
			$groups[] = $G->getID();
			
			$chatGroups .= "<div class=\"chatWindow\" style=\"display:none;\" id=\"chatTextg".$G->getID()."\">$content</div>";
		}
		
		asort($Users);
		
		$chatUsers = "";
		foreach($Users AS $ID => $U){
			if($ID == Session::currentUser()->getID())
				continue;
			
			if(isset($hidden[$ID]))
				continue;
			
			$unread = false;
			$content = "";
			$AC = anyC::get("phim");
			$AC->addAssocV3("phimFromUserID", "=", Session::currentUser()->getID(), "AND", "1");
			$AC->addAssocV3("phimToUserID", "=", $ID, "AND", "1");
			$AC->addAssocV3("phimFromUserID", "=", $ID, "OR", "2");
			$AC->addAssocV3("phimToUserID", "=", Session::currentUser()->getID(), "AND", "2");
			$AC->addOrderV3("phimID", "DESC");
			$AC->setLimitV3(50);
			while($M = $AC->n()){
				$content = "<div><span class=\"username\">".$Users[$M->A("phimFromUserID")].": </span>".$M->A("phimMessage")."</div>".$content;
			
				if(!$M->A("phimRead") AND $M->A("phimToUserID") == Session::currentUser()->getID())
					$unread = true;
			}
			
			$L->addItem($BOn.$BOff.$B.$U);
			$L->addItemEvent("onclick", OnEvent::rme("phim", "setRead", $ID)."\$j(this).removeClass('highlight'); \$j('.chatWindow').hide(); \$j('#chatText$ID').show(); phimChat.scroll('chatText$ID'); \$j('#userList .backgroundColor0').removeClass('backgroundColor0'); \$j(this).addClass('backgroundColor0');\$j('#channel').val('$ID');");
			$L->addItemStyle("cursor:pointer;margin-left:0;padding:5px;");
			$L->setItemID("user$ID");
			if($unread)
				$L->addItemClass ("highlight");
			
			
			$chatUsers .= "<div style=\"display:none;\" class=\"chatWindow\" id=\"chatText$ID\">$content</div>";
		}
		
		
		$content = "<div style=\"width:68%;display:inline-block;vertical-align:top;\">
				$chatAll
				$chatUsers
				$chatGroups
				<div>$I</div>
			</div><div style=\"width:32%;display:inline-block;vertical-align:top;\">$L$IC</div>";
		
		
		$rp = str_replace("interface/rme.php", "", $_SERVER["SCRIPT_NAME"]);
		if(strpos($_SERVER["SCRIPT_NAME"], "phim.php") !== false)
			$rp = "../../";
		
		$physion = "default";
		if(isset($_GET["physion"]))
			$physion = $_GET["physion"];
		
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>phim</title>
		<script type="text/javascript" src="'.$root.'../libraries/jquery/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="'.$root.'../libraries/jquery/jquery-ui-1.10.1.custom.min.js"></script>
		<script type="text/javascript" src="'.$root.'../libraries/iconic/iconic.min.js"></script>
		<script type="text/javascript" src="'.$root.'../libraries/jquery/jquery.qtip.min.js"></script>
		<script type="text/javascript" src="'.$root.'../javascript/P2J.js"></script>
		<script type="text/javascript" src="'.$root.'../javascript/Aspect.js"></script>
		<script type="text/javascript" src="'.$root.'../javascript/handler.js"></script>
		<script type="text/javascript" src="'.$root.'../javascript/contentManager.js"></script>
		<script type="text/javascript" src="'.$root.'../javascript/Interface.js"></script>
		<script type="text/javascript" src="'.$root.'../javascript/Overlay.js"></script>
		<script type="text/javascript" src="'.$root.'../libraries/webtoolkit.base64.js"></script>
		
		<script type="text/javascript" src="'.$root.'../ubiquitous/phim/autobahn.min.js"></script>
		<script type="text/javascript" src="'.$root.'../ubiquitous/phim/phimChat.js"></script>
		<script type="text/javascript">
			contentManager.setRoot("'.$rp.'");
			$j(function(){
				Ajax.physion = "'.$physion.'";
					
				phimChat.init(
					"ws'.($S->A("WebsocketSecure") ? "s" : "").'://'.$S->A("WebsocketServer").":".$S->A("WebsocketServerPort").'/",
					"'.$S->A("WebsocketRealm").'", 
					'.Session::currentUser()->getID().', 
					"'.Session::currentUser()->A("name").'",
					"'.$S->A("WebsocketToken").'",
					['.implode(",", $groups).'],
					"'.$root.'");
						
				phimChat.scroll("chatText0");
				$j("#userList").css("height", $j(window).height());
			});
		</script>
		<link rel="stylesheet" type="text/css" href="'.$root.'../libraries/jquery/jquery.qtip.min.css" />
		<link rel="stylesheet" type="text/css" href="'.$root.'../styles/'.(isset($_COOKIE["phynx_color"])? $_COOKIE["phynx_color"] : "standard").'/colors.css"></link>
		<link rel="stylesheet" type="text/css" href="'.$root.'../styles/standard/general.css"></link>
		<style type="text/css">
			p {
				padding:5px;
			}
			
			body {
				background-color:#ddd;
			}
			
			html {
				overflow-y: auto;
			}

			.username {
				font-weight:bold;
			}
			
			.chatWindow {
				padding:5px;
				box-sizing:border-box;
				overflow-y: auto;
				height:270px;
				background-color:white;
				border-bottom:3px solid grey;
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
			
			#userList li {
				white-space: nowrap;
				overflow:hidden;
				margin-top:0;
			}
		</style>
	</head>
	<body>
		<div id="darkOverlay" style="background-color:rgba(0,0,0,.7);color:white;"></div>
		'.$content.'
	</body>
</html>';
	}

	/*public function getInit(){

		$U = Users::getUsers();
		# = array();
		#while($us = $U->getNextEntry())
		#	$users[$us->getID()] = $us->A("name");
		
		$L = new HTMLList();
		$L->addListStyle("list-style-type:none;margin-bottom:10px;");
		#$AC = anyC::get("phim", "phimUserID", Session::currentUser()->getID());
		#while($p = $AC->getNextEntry()){
		while($us = $U->getNextEntry()){
			if($us->getID() == Session::currentUser()->getID())
				continue;
			
			$B = new Button("Status", "./ubiquitous/phim/userOffline.png", "icon");
			$B->style("float:left;margin-right:5px;margin-top:-2px;margin-left:-15px;");
			$B->className("phimUserStatus");
			$B->id("phimUserStatus".$us->getID());
				
			$L->addItem($B.$us->A("name")." <span id=\"phimUserUnread".$us->getID()."\"></span>");
			$L->addItemEvent("onclick", "phim.getChatWindow(".$us->getID().", '".$this->user2id($us->getID())."');");
			$L->addItemStyle("cursor:pointer;");
		}
		
		echo "<div class=\"\" style=\"width:180px;margin-left:0px;margin-top:20px;\">".$L.OnEvent::script("phim.currentUser = ".Session::currentUser()->getID())."</div>";
	}
	
	public function user2id($id){
		$U = new User($id);
		return $U->A("name");
	}
	
	public function getChatWindow($phimTargetUserID){
		$I = new HTMLInput("phimSendTo$phimTargetUserID", "text");
		$I->style("width:99%;");
		$I->onEnter("{ phim.send(this.value, '".Session::currentUser()->getID()."', $phimTargetUserID); this.value = ''; }");
		$I->id("phimSendTo$phimTargetUserID");
		
		$other = new User($phimTargetUserID);
		
		$AC = anyC::get("phim");
		$AC->addAssocV3("phimFromUserID", "=", Session::currentUser()->getID(), "AND", "1");
		$AC->addAssocV3("phimToUserID", "=", $phimTargetUserID, "AND", "1");
		
		$AC->addAssocV3("phimFromUserID", "=", $phimTargetUserID, "OR", "2");
		$AC->addAssocV3("phimToUserID", "=", Session::currentUser()->getID(), "AND", "2");
		
		$AC->setOrderV3("phimTime", "DESC");
		$AC->setLimitV3("30");
		
		$messages = array();
		while($M = $AC->getNextEntry()){
			$from = $M->A("phimFromUserID");
			if($from == $phimTargetUserID)
				$from = $other->A("name");
			else
				$from = Session::currentUser()->A("name");
			
			$messages[] = "<p style=\"padding:3px;line-height:1.5;\"><span style=\"color:grey;\">(".Util::CLDateTimeParser($M->A("phimTime")).")</span> <b>$from:</b> ".$M->A("phimMessage")."</p>";
		}
		
		sort($messages);
		
		$html = "<div id=\"phimMessages$phimTargetUserID\" class=\"borderColor1\" style=\"border-bottom-style:solid;border-bottom-width:3px;height:200px;overflow:auto;\">".  implode("", $messages)."</div>$I";
		
		echo $html.OnEvent::script("setTimeout(function(){ $('phimMessages$phimTargetUserID').scrollTop = $('phimMessages$phimTargetUserID').scrollHeight; \$j('#phimSendTo$phimTargetUserID').trigger('focus'); },100); phim.ids2users['$phimTargetUserID'] = '".$this->user2id($phimTargetUserID)."'; phim.ids2users['".Session::currentUser()->getID()."'] = '".Session::currentUser()->A("name")."';");
	}*/
}
?>