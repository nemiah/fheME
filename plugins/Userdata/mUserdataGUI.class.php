<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class mUserdataGUI extends mUserdata implements iGUIHTML2, icontextMenu {
	function __construct() {
		parent::__construct();

		$this->customize();
	}
	
	public function getHTML($id){
		$this->addOrderV3("name");
		if($this->A == null) $this->lCV3($id);
		
		$singularLanguageClass = $this->loadLanguageClass("Userdata");
		$text = $singularLanguageClass != null ? $singularLanguageClass->getText() : "";
		
		$gui = new HTMLGUI();
		$gui->setName("Benutzereinschränkungen");
		if($this->collector != null) $gui->setAttributes($this->collector);
		$gui->hideAttribute("UserID");
		$gui->hideAttribute("wert");
		$gui->hideAttribute("UserdataID");
		$gui->hideAttribute("typ");
		$gui->setDeleteInDisplayMode(true);
		$gui->setCollectionOf($this->collectionOf);
		$gui->setIsDisplayMode(true);
		$gui->setParser("name","mUserdataGUI::nameParser",array("\$sc->wert"));
		$html = "
		<table>
			<tr>
				<td class=\"backgroundColor3\"><input type=\"button\" style=\"background-image:url(./images/navi/seiten.png);\" class=\"bigButton backgroundColor2\" value=\"".(isset($text["copy"]) ? $text["copy"] : "von Benutzer\nkopieren")."\" onclick=\"phynxContextMenu.start(this, 'mUserdata','copyFromUser','kopieren:', 'right', 'up');\" /></td>
			</tr>
		</table>";
		$gui->addRowAfter("1","addRestriction");
		$gui->setParser("addRestriction","mUserdataGUI::addRestrictionParser");
		$gui->setJSEvent("onDelete","function(){ contentManager.reloadFrame('contentLeft'); }");
		
		try {
			return "<div class=\"prettyTitle\">Rechte</div>".$gui->getBrowserHTML($id).($this->numLoaded() == 0 ? $html : "");
		} catch (Exception $e){ echo $e; }
	}
	
	public static function nameParser($w, $l, $p){
		$ac = new anyC();
		$singularLanguageClass = $ac->loadLanguageClass("Userdata");
		$text = $singularLanguageClass != null ? $singularLanguageClass->getText() : "";
		
		$ps = $_SESSION["CurrentAppPlugins"]->getAllPlugins();
		
		$html = "";
		$isRestricted = false;
		if(stristr($w,"loginTo")) {
			$B = new Button("Kann nicht anmelden", "./plugins/Userdata/login18.png", "icon");
			$B->style("float:left;margin-left:10px;margin-right:5px;");
			
			$html .= $B;
			
			$w = str_replace("loginTo","",$w);
			#$isRestricted = true;
			$w = "Kann sich nicht an Anwendung '$w' anmelden";
			#if($w == "") $w = "Plugin ".str_replace("loginTo","",$p)." nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
		}
		
		if(stristr($w,"antDelete")) {
			$html .= "<img title=\"".(isset($text["kann nicht löschen"]) ? $text["kann nicht löschen"] : "kann nicht löschen")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/delete.gif\" />";
			$w = str_replace("cantDelete","",$w);
			$isRestricted = true;
			$w = array_search($w,$ps);
			if($w == "") $w = "Plugin ".str_replace("cantDelete","",$p)." nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
		}
		
		if(stristr($w,"antCreate") AND !stristr($w,"pluginSpecific")) {
			$html .= "<img title=\"".(isset($text["kann nicht erstellen"]) ? $text["kann nicht erstellen"] : "kann nicht erstellen")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/new.gif\" />";
			$w = str_replace("cantCreate","",$w);
			$isRestricted = true;
			$w = array_search($w,$ps);
			if($w == "") $w = "Plugin ".str_replace("cantCreate","",$p)." nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
		}
		if(stristr($w,"antEdit")) {
			$html .= "<img title=\"".(isset($text["kann nicht bearbeiten"]) ? $text["kann nicht bearbeiten"] : "kann nicht bearbeiten")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/edit.png\" />";
			$w = str_replace("cantEdit","",$w);
			$isRestricted = true;
			$w = array_search($w,$ps);
			if($w == "") $w = "Plugin ".str_replace("cantEdit","",$p)." nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
		}
		
		if(stristr($w,"relabel")) {
			$html .= "<img title=\"".(isset($text["Feld wurde umbenannt"]) ? $text["Feld wurde umbenannt"] : "Feld wurde umbenannt")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/relabel.png\" />";
			$w = str_replace("relabel","",$w);
			$s = explode(":",$w);
			$w = $s[0].": ".$s[1]." = ".$p;
			if($w == "") $w = "Plugin ".str_replace("relabel","",$p)." nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
		}
		
		if(stristr($w,"hideField")) {
			$html .= "<img title=\"".(isset($text["Feld wurde versteckt"]) ? $text["Feld wurde versteckt"] : "Feld wurde versteckt")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/clear.png\" />";
			$w = str_replace("hideField","",$w);
			$s = explode(":",$w);
			$w = $s[0].": ".$s[1];
			if($w == "") $w = "Plugin ".str_replace("hideField","",$p)." nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
		}
		
		if(stristr($w,"pluginSpecific")) {
			$html .= "<img title=\"".(isset($text["Plugin-spezifisch"]) ? $text["Plugin-spezifisch"] : "Plugin-spezifisch")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/lieferschein.png\" />";
			try {
				$C = new $p();
				$pSRs = $C->getPluginSpecificRestrictions();
				$w = array_search($p,$ps).": ".$pSRs[$w];
			} catch(ClassNotFoundException $e){
				$html .= "Plugin $p nicht geladen<br /><small style=\"color:grey;\">Dieses Plugin steht in der aktiven Anwendung nicht zur Verfügung.</small>";
				$w = "";
				#echo "Klasse $p nicht gefunden";
			}
		}
		
		if(stristr($w,"hidePlugin")) {
			$html .= "<img title=\"".(isset($text["Plugin ausblenden"]) ? $text["Plugin ausblenden"] : "Plugin ausblenden")."\" style=\"float:left;margin-left:10px;margin-right:5px;\" src=\"./images/i2/tab.png\" />";
			$w = array_search($p,$ps);
			$isRestricted = true;
			if($w == "") $w = "Plugin ".str_replace("cantDelete","",$p)." nicht geladen";
		}
		if($isRestricted) $html .= "<img style=\"float:left;margin-left:-32px;filter:opacity\" src=\"./images/i2/restriction.png\" />";
		
		
		
		return $html.$w;
	}
	
	public static function addRestrictionParser($w,$l,$p){
		$deText = array();
		$deText["Umbenennung"] = "Umbenennung";
		$deText["Einschränkung"] = "Einschränkung";
		$deText["Ausblenden"] = "Ausblenden";
		$deText["Plugin"] = "Plugin";
		
		$ac = new anyC();
		$singularLanguageClass = $ac->loadLanguageClass("Userdata");
		$text = $singularLanguageClass != null ? $singularLanguageClass->getText() : $deText;
		
		$BA = new Button("Anmeldung", "./plugins/Userdata/login.png");
		$BA->contextMenu("mUserdata", "login", "Anmeldung", "right", "up");
		$BA->style("float:right;");
		
		$BN = new Button("Einschränkung\nhinzufügen", "restrictions");
		$BN->contextMenu("mUserdata", "1", "Einschränkung", "right", "up");
		
		$BS = new Button("Plugin-\nspezifisch", "lieferschein");
		$BS->contextMenu("mUserdata", "4", "Plugin-spezifisch", "right", "up");
		$BS->style("float:right;");
		
		$BP = new Button("Plugin\nausblenden", "tab");
		$BP->contextMenu("mUserdata", "5", "Plugin ausblenden", "right", "up");
		
		$BR = new Button("Rollen", "./plugins/Userdata/role.png");
		$BR->popup("", "Rollen", "mUserdata", "-1", "rolesPopup", array("lastLoadedLeft"));
		
		
		return "<p class=\"highlight\">Achtung: Die möglichen Berechtigungen sind von der geladenen Anwendung und ihren Plugins abhängig. Melden Sie sich an einer anderen Anwendung an, um weitere Berechtigungen zu vergeben.</p>"."
		<!--<input type=\"button\" class=\"bigButton backgroundColor3\" title=\"".(isset($text["Feld\numbenennen"]) ? $text["Feld\numbenennen"] : "Feld\numbenennen")."\" onclick=\"phynxContextMenu.start(this, 'mUserdata','2','".$text["Umbenennung"].":');\" style=\"float:right;background-image:url(./images/navi/relabel.png);\" />
		<button class=\"bigButton backgroundColor3\" value=\"".(isset($text["Einschränkung\nhinzufügen"]) ? $text["Einschränkung\nhinzufügen"] : "")."\" onclick=\"phynxContextMenu.start(this, 'mUserdata','1','".$text["Einschränkung"].":');\" style=\"margin-bottom:10px;background-image:url(./images/navi/restrictions.png);\" /><br />-->
		$BN$BS<br><br>
		<!--<input type=\"button\" class=\"bigButton backgroundColor3\" title=\"".(isset($text["Feld\nausblenden"]) ? $text["Feld\nausblenden"] : "Feld\nausblenden")."\" onclick=\"phynxContextMenu.start(this, 'mUserdata','3','".$text["Ausblenden"].":');\" style=\"float:right;background-image:url(./images/navi/clear.png);\" />
		<button class=\"bigButton backgroundColor3\" title=\"".(isset($text["Plugin-\nspezifisch"]) ? $text["Plugin-\nspezifisch"] : "Plugin-\nspezifisch")."\" onclick=\"phynxContextMenu.start(this, 'mUserdata','4','".$text["Plugin"].":');\" style=\"margin-bottom:10px;background-image:url(./images/navi/lieferschein.png);\" />-->
		$BP$BA<br><br>
		$BR
		<!--<button class=\"bigButton backgroundColor3\" title=\"".(isset($text["Plugin\nausblenden"]) ? $text["Plugin\nausblenden"] : "Plugin\nausblenden")."\" onclick=\"phynxContextMenu.start(this, 'mUserdata','5','".$text["Plugin"].":');\" style=\"background-image:url(./images/navi/tab.png);\" />-->";
	}
	
	public function rolesPopup($UserID){
		echo "<p class=\"highlight\">Die Rollen fassen mehrere Berechtigungen aus unterschiedlichen Plugins zusammen.</p><div style=\"max-height:400px;overflow:auto;\">";
		
		$ps = $_SESSION["CurrentAppPlugins"]->getAllPlugins();
		
		foreach($this->roles() AS $R => $O){
			$T = new HTMLTable(3, $R);
			$T->weight("light");
			$T->setColWidth(1, 70);
			$T->setColWidth(3, 30);
			
			$i = 0;
			foreach($O AS $c => $s){
				if($c == "cantEdit" OR $c == "cantDelete" OR $c == "cantCreate"){
					foreach($s AS $p){
						if($p == "WAdresse")
							continue;
						
						if($c == "cantEdit")
							$T->addRow(array($p, "Kann nicht bearbeiten"));
						
						if($c == "cantDelete")
							$T->addRow(array($p, "Kann nicht löschen"));
						
						if($c == "cantCreate")
							$T->addRow(array($p, "Kann nicht erstellen"));
					}
					
					continue;
				}
				
				if($c == "hidePlugin"){
					foreach($s AS $p)
						$T->addRow(array(array_search($p, $ps), "Plugin ausblenden"));
					
					continue;
				}
				
				$B = new Button("Rolle aktivieren", "./plugins/Userdata/role.png", "icon");
				$B->rmePCR("mUserdata", "-1", "rolesActivate", array($UserID, "'$R'"), OnEvent::reload("Left"));
			
				$class = new $c();
				$pSs = $class->getPluginSpecificRestrictions();
				$l = "";
				foreach($s AS $k => $p){
					$l .= ($k > 0 ? "<br>" : "").$pSs[$p];
				}
				$T->addRow(array(array_search($c, $ps).":", $l, $i == 0 ? $B : ""));
				if($i == 0){
					$T->addColStyle(3, "vertical-align:top;");
					$T->addColRowspan(3, 3);
				}
				
				$i++;
			}
			
			
			#$T->addRow(array($l, $B));
			#$T->addColStyle(2, "vertical-align:top;");
			
			echo $T;
		}
		
		echo "</div>";
		
	}
	
	public function rolesActivate($UserID, $role){
		foreach($this->roles($role) AS $c => $s){
			if($c == "cantEdit" OR $c == "cantDelete" OR $c == "cantCreate"){
				foreach($s AS $k => $p){
					$p2 = "m$p";
					if($p == "Adresse")
						$p2 = "Adressen";
					
					mUserdata::setUserdataS($c.$p2, $c.$p, "uRest", $UserID);
				}
				continue;
			}
			
			if($c == "hidePlugin"){
				foreach($s AS $k => $p){
				mUserdata::setUserdataS($c.$p, $p, "pHide", $UserID);
					
				}
				continue;
			}
			
			foreach($s AS $k => $p){
				mUserdata::setUserdataS($p, $c, "pSpec", $UserID);
			}
		}
	}
	
	public function getContextMenuHTML($identifier){
		$deTexts = array();
		$deTexts["pluginSupport"] = "Bitte beachten Sie, dass ein Plugin diese Einstellungen unterstützen muss, selbst wenn es hier angezeigt wird!";
		$deTexts["selectPlugin"] = "Bitte Plugin wählen";
		$deTexts["add"] = "hinzufügen";
		$deTexts["selectPluginButton"] = "Plugin auswählen";
		$deTexts["save"] = "speichern";
		$deTexts["select"] = "auswählen";
		$deTexts["noPsOptions"] = "keine plugin-spezifischen Optionen vorhanden";
		$deTexts["newFieldName"] = "Neuer Feldname";
		$deTexts["maybeHidden"] = "Bitte beachten Sie, dass hier interne Namen angezeigt werden, die von der Feldbeschriftung abweichen können!<br />Manche internen Felder werden möglichweise gar nicht angezeigt.";
		
		$ac = new anyC();
		$singularLanguageClass = $ac->loadLanguageClass("Userdata");
		$text = $singularLanguageClass != null ? $singularLanguageClass->getText() : $deTexts;
		
		$opts = "";
		$ps = array_flip($_SESSION["CurrentAppPlugins"]->getAllPlugins());
		$ms = $_SESSION["CurrentAppPlugins"]->getAllMenuEntries();
		
		#print_r($ms);
		
		foreach($ps as $key => $value){
			if($key == "mUserdata") continue;
			if($identifier == "4" AND !PMReflector::implementsInterface($key,"iPluginSpecificRestrictions"))
				continue;
			
			if($identifier == "4"){
				$c = new $key();
				if(!$c->getPluginSpecificRestrictions())
					continue;
			}
			
			if($identifier == "5" AND !in_array($key,$ms))
				continue;
			
			if(!$_SESSION["CurrentAppPlugins"]->getIsAdminOnly($key) AND $_SESSION["CurrentAppPlugins"]->isCollectionOfFlip($key) != "")
				$opts .= "<option value=\"$key:".$_SESSION["CurrentAppPlugins"]->isCollectionOfFlip($key)."\">$value</option>";
		}
		$s = explode(":",$identifier);
		if(isset($s[1])) $identifier = $s[0];
		switch($identifier){
			case "1":
				$c = (isset($text["kann nicht erstellen"]) ? $text["kann nicht erstellen"] : "kann nicht\nerstellen");
				$b = (isset($text["kann nicht bearbeiten"]) ? $text["kann nicht bearbeiten"] : "kann nicht\nbearbeiten");
				$l = (isset($text["kann nicht löschen"]) ? $text["kann nicht löschen"] : "kann nicht\nlöschen");
				
				echo "
				<table>
					<tr>
						<td><input type=\"button\" class=\"bigButton backgroundColor2\" value=\"".$c."\" style=\"background-image:url(./images/navi/new.png);\" onclick=\"phynxContextMenu.update('mUserdata','Create','".str_replace(array("\n","'"),array(" ","\'"),$c).":');\" /></td>
					</tr>
					<tr>
						<td><input type=\"button\" class=\"bigButton backgroundColor2\" value=\"".$b."\" style=\"background-image:url(./images/navi/editb.png);\" onclick=\"phynxContextMenu.update('mUserdata','Edit','".str_replace(array("\n","'"),array(" ","\'"),$b).":');\" /></td>
					</tr>
					<tr>
						<td><input type=\"button\" class=\"bigButton backgroundColor2\" value=\"".$l."\" style=\"background-image:url(./images/navi/trash.png);\" onclick=\"phynxContextMenu.update('mUserdata','Delete','".str_replace(array("\n","'"),array(" ","\'"),$l).":');\" /></td>
					</tr>
					<tr>
						<td><img src=\"./images/navi/warning.png\" style=\"float:left;margin-right:4px;\" />".$text["pluginSupport"]."</td>
					</tr>
				</table>";
			break;
			case "Edit":
			case "Delete":
			case "Create":
				echo "
				<input type=\"hidden\" id=\"uRestAction\" value=\"$identifier\" />
				<table>
					<tr>
						<td>".$text["selectPlugin"].":</td>
					</tr>
					<tr>
						<td><select id=\"cant$identifier\">$opts</select></td>
					</tr>
					<tr>
						<td><input type=\"button\" value=\"".$text["add"]."\" onclick=\"addUserRestriction();\" /></td>
					</tr>
				</table>";
			break;
			case "3":
			case "2":
				echo "
				<table>
					<tr>
						<td>".$text["selectPlugin"].":</td>
					</tr>
					<tr>
						<td><select id=\"relabelPlugin\">$opts</select></td>
					</tr>
					<tr>
						<td><input type=\"button\" value=\"".$text["select"]."\" onclick=\"phynxContextMenu.update('mUserdata','".($identifier == "2" ? "relabel" : "hide").":'+$('relabelPlugin').value.split(':')[1], $('relabelPlugin').value.split(':')[1]);\" /></td>
					</tr>
					<tr>
						<td><img src=\"./images/navi/warning.png\" style=\"float:left;margin-right:4px;\" />".$text["maybeHidden"]."</td>
					</tr>
				</table>";
			break;
			case "5":
			case "4":
				if($opts == "")
					die("<p>".$text["noPsOptions"]."</p>");
				
				echo "
				<table>
					<tr>
						<td>".$text["selectPlugin"].":</td>
					</tr>
					<tr>
						<td><select id=\"relabelPlugin\">$opts</select></td>
					</tr>
					".($identifier == 4 ? "<tr>
						<td><input type=\"button\" value=\"".$text["selectPluginButton"]."\" onclick=\"phynxContextMenu.update('mUserdata','pS:'+$('relabelPlugin').value.split(':')[0], $('relabelPlugin').value.split(':')[0]);\" /></td>
					</tr>" : "<tr>
						<td><input type=\"button\" value=\"".$text["selectPluginButton"]."\" onclick=\"addHidePlugin();\" /></td>
					</tr>")."
				</table>";
			break;
			
			case "pS":
				$c = new $s[1]();
				$pSs = $c->getPluginSpecificRestrictions();
				$pSopts = "";
				foreach($pSs as $key => $value)
					$pSopts .= "<option value=\"$key:$s[1]\">$value</option>";
				echo "
				<table>
					<tr>
						<td><select id=\"pSSelect\">$pSopts</select></td>
					</tr>
					<tr>
						<td><input type=\"button\" value=\"".$text["save"]."\" onclick=\"savePluginSpecificRestriction();\" /></td>
					</tr>
				</table>";
					
			break;
			
			case "hide":
			case "relabel":
				try{
				$c = new $s[1](-1);
				$c = $c->newAttributes();
				echo "
				<input type=\"hidden\" id=\"".$identifier."Plugin\" value=\"$s[1]\" />
				<table>
					<tr>
						<td><select id=\"".$identifier."Field\"><option>".implode("</option><option>",PMReflector::getAttributesArray($c))."</option></select></td>
					</tr>
					".($identifier == "relabel" ? "<tr>
						<td>".$text["newFieldName"].":</td>
					</tr>
					<tr>
						<td><input id=\"relabelTo\" type=\"text\" /></td>
					</tr>" : "")."
					<tr>
						<td><input type=\"button\" value=\"".$text["save"]."\" onclick=\"".($identifier == "relabel" ? "saveFieldRelabeling();" : "saveFieldHiding();")."\" /></td>
					</tr>
				</table>";
				} catch(ClassNotFoundException $e){
					echo "<p>Diese Option steht bei diesem Plugin leider nicht zur Verfügung</p>";
				}
			break;
			
			case "copyFromUser":
				$T = new HTMLTable(2);
				$T->useForSelection();
				$T->setColWidth(1, 20);
				$T->maxHeight(200);
				
				$G = new Users();
				$G->addAssocV3("isAdmin","=","0");
				
				$G->setLimitV3("10");
				$G->lCV3();
				while(($t = $G->getNextEntry())){
					$T->addRow(array(new Button("", "./images/i2/copy.png", "icon"), $t->A("username")));
					$T->addRowEvent("click", "copyFromOtherUser('".$t->getID()."');");
				}
				
				echo $T;
			break;
			
			case "login":
				$T = new HTMLTable(2);
				$T->useForSelection();
				$T->setColWidth(1, 20);
				$T->maxHeight(200);
				
				$apps = Applications::getList();
				foreach($apps AS $app){
					#rme("mUserdata","-1","setUserdata",new Array("hidePlugin"+$('relabelPlugin').value.split(":")[0],$('relabelPlugin').value.split(":")[0], "pHide", lastLoadedLeft),"contentManager.reloadFrameLeft()");
					
					$T->addRow(array(new Button("", "./plugins/Userdata/login18.png", "icon"), "Kann sich nicht an '$app' anmelden"));
					$T->addRowEvent("click", OnEvent::rme(new mUserdata(-1), "setUserdata", array("'loginTo$app'", "'0'", "'loginTo'", "lastLoadedLeft"), OnEvent::closeContext().OnEvent::reload("Left")));
					#$T->addRowEvent("click", "copyFromOtherUser('".$t->getID()."');");
				}
				
				echo $T;
			break;
		}
	}
	
	public function saveContextMenu($identifier, $key){	}
}
?>
