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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */

class SpellbookGUI implements iGUIHTMLMP2 {
	public static function getSpell($requestPlugins){
		$connection = true;
		$options = array(
			"location" => "http://www.open3a.de/page-SOAP",
			"uri" => "http://www.open3a.de/page-SOAP");
		try {
			#throw new Exception();
			$S = new SoapClient(null, $options);
			$I = $S->getPluginDescription($requestPlugins, false, Applications::activeApplication());
		} catch (Exception $e){
			$connection = false;
		}
		
		mUserdata::setUserdataS("SpellbookLastRequest", time(), "", -1);
		
		if($connection){
			try {
				return (new SimpleXMLElement($I));
			} catch (Exception $e){
				
			}
		}
		
		return false;
	}
	
	public function getHTML($id, $page) {
		$entries = $_SESSION["CurrentAppPlugins"]->getMenuEntries();
		$icons = $_SESSION["CurrentAppPlugins"]->getIcons();
		$targets = $_SESSION["CurrentAppPlugins"]->getMenuTargets();
		
		$appMenuDisplayed = MenuGUI::getAppMenuOrder("appMenuDisplayed");
		$userHiddenPlugins = mUserdata::getHiddenPlugins(true);
		
		if($appMenuDisplayed != "")
			$appMenuDisplayed = explode(";", $appMenuDisplayed);
		else
			$appMenuDisplayed = $entries;

		ksort($entries);
		$request = array_values($entries);
		$xml = false;
		if(time() - mUserdata::getGlobalSettingValue("SpellbookLastRequest", 0) > 3600 * 24 * 7)
			$xml = self::getSpell($request);

		#$AP3 = new AppPlugins("customer");
		#$plugins = array_merge($plugins, $AP3->getAllPlugins());
		#$menu = array_merge($menu, array_flip($AP3->getAllMenuEntries()));
		#$icons = array_merge($icons, $AP3->getIcons());
		#$plugins3 = array_flip($AP3->getAllPlugins());
		#print_r($plugins3);
		
		
		$AP = new AppPlugins(Applications::activeApplication());
		$plugins = array_flip($AP->getAllPlugins());
		
		$html = "";
		$html .= "<div style=\"float:right;width:160px;padding-top:20px;\" id=\"SpellbookSortTabs\">".$this->getSortable(false)."</div>
			<div class=\"SpellbookContainer\">";
		
			
		$U = new mUserdata();
		$U->addAssocV3("typ","=","TTP");
		$collapsedTabs = Environment::getS("collapsedTabs", "0") == "1";
		
		$packages = array();
		if(file_exists(Util::getRootPath()."Zeus/CloudKunden/CloudKunde.class.php")){
			require_once Util::getRootPath()."Zeus/CloudKunden/CloudKunde.class.php";
			$packages = CloudKunde::getPackagePlugins(Applications::activeApplication());
		}
			
		foreach($entries as $key => $value) {
			if(isset($userHiddenPlugins[$key]))
				continue;
			if(isset($userHiddenPlugins[$value]))
				continue;
			
			$text = "";
			if($xml !== false){
				foreach ($xml->plugin AS $xmlp)
					if($xmlp->name == $value){
						$text = $xmlp->description->asXML();
						$text = strip_tags($text, "a");
						mUserdata::setUserdataS("SpellBookPlugin$value".Applications::activeApplication(), $text, "", -1);
					}
			} else 
				$text = mUserdata::getGlobalSettingValue("SpellBookPlugin$value".Applications::activeApplication(), "");
			
			$paket = "";
			foreach($packages AS $package => $plugins){
				foreach($plugins AS $plugin)
					if($plugin == $value){
						$paket = "<span style=\"color:green;font-weight:bold;\">Ab ".CloudKunde::getAppPackageName(Applications::activeApplication())." Paket ".floor($package / 100)."</span><br>";
						break 2;
					}
			}
					
			if(isset($plugins[$value]))
				unset($plugins[$value]);
			
			#$text = strip_tags($text, "a");
			
			$BG = new Button("Plugin $key öffnen", "navigation", "icon");
			$BG->onclick("contentManager.loadPlugin('".(isset($targets[$value]) ? $targets[$value] : "contentRight")."', '$value', '{$value}GUI;-');");
			$BG->style("float:right;margin-top:-7px;");
			
			$B = new Button($key, $icons[$value], "icon");
			$B->style("float:left;margin-right:10px;margin-top:-7px;margin-left:-5px;width:32px;");
			
			$I = new HTMLInput("usePlugin$value", "checkbox", in_array($value, $appMenuDisplayed) ? "1" : "0");
			$I->id("usePlugin$value");
			$I->onchange("if(this.checked) { Menu.showTab('$value'); \$j('#minPlugin$value').prop('disabled', ''); } else { Menu.hideTab('$value'); \$j('#minPlugin$value').prop('disabled', 'disabled'); }");
			
			
			$t =  !$_SESSION["S"]->isUserAdmin() ? $U->getUDValueCached("ToggleTab$value") : "big";
			if($t == null AND $collapsedTabs)
				$t = "small";

			if($t == null)
				$t = "big";
			
			$IM = new HTMLInput("minPlugin$value", "checkbox", $t == "big" ? "0" : "1");
			$IM->id("minPlugin$value");
			$IM->onchange("Menu.toggleTab('$value');");
			
			$layout = mUserdata::getUDValueS("phynxLayout", "horizontal");
			if($layout == "vertical" OR $layout == "desktop")
				$IM->isDisabled (true);
			
			if(!in_array($value, $appMenuDisplayed))
				$IM->isDisabled (true);
			
			#border-width:1px;border-style:solid;
			$html .= "<div style=\"\" class=\"SpellbookSpell\">
				<div style=\"margin:10px;\" class=\"borderColor1 spell\">
					<div class=\"backgroundColor2\" style=\"padding:10px;padding-bottom:5px;\">
						$BG$B<h2 style=\"margin-bottom:0px;margin-top:0px;\">$key</h2>
					</div>
					<div style=\"padding:7px;\" class=\"SpellbookUsePlugin\">
						$I<label style=\"float:none;width:200px;text-align:left;display:inline;margin-left:10px;font-wight:normal;\" for=\"usePlugin$value\">Plugin verwenden</label>
					</div>
					<div style=\"padding:7px;padding-top:0px;\" class=\"SpellbookMinPlugin\">
						$IM<label style=\"float:none;width:200px;text-align:left;display:inline;margin-left:10px;font-wight:normal;\" for=\"minPlugin$value\">Reiter minimiert</label>
					</div>
					<div style=\"padding:7px;height:115px;overflow:auto;\" class=\"SpellbookDescription\">$paket$text</div>
				</div>
			</div>";
		}
		
		$html .= "</div><h2 style=\"clear:both;padding-top:50px;\">Admin-Plugins und Plugins ohne eigenen Reiter</h2><div class=\"SpellbookContainer\">";

		$icons = $AP->getIcons();
		$plugins = array_flip($plugins);
		
		$menu = array_flip($AP->getAllMenuEntries());
		
		$AP2 = new AppPlugins("plugins");
		$plugins = array_merge($plugins, $AP2->getAllPlugins());
		$menu = array_merge($menu, array_flip($AP2->getAllMenuEntries()));
		$icons = array_merge($icons, $AP2->getIcons());
		
		$plugins2 = array_flip($AP2->getAllPlugins());
		
		$request = array_values($plugins);
		$xml = self::getSpell($request);
		ksort($plugins);

		foreach($plugins as $key => $value) {
			if(isset($userHiddenPlugins[$key]))
				continue;
			if(isset($userHiddenPlugins[$value]))
				continue;
			
			if(isset($menu[$value]))
				$key = $menu[$value];
			$text = "";
			if($xml !== false){
				foreach ($xml->plugin AS $xmlp)
					if($xmlp->name == $value){
						$text = $xmlp->description->asXML();
						$text = strip_tags($text, "a");
						mUserdata::setUserdataS("SpellBookPlugin$value".Applications::activeApplication(), $text, "", -1);
					}
			} else 
				$text = mUserdata::getGlobalSettingValue("SpellBookPlugin$value".Applications::activeApplication(), "");
			

			if($text == "" OR $text == "-")
				continue;
			
			if(!isset($plugins2[$value]) AND substr($AP->getFolderOfPlugin($value), 0, 3) == "../")
				continue;
			
			if(isset($plugins2[$value]) AND substr($AP2->getFolderOfPlugin($value), 0, 3) == "../")
				continue;
			
			
			if(!isset($plugins2[$value]))
				$isAdmin = $AP->getIsAdminOnly($value);
			
			if(isset($plugins2[$value]))
				$isAdmin = $AP2->getIsAdminOnly($value);
			
			
			$B = new Button($key, $icons[$value], "icon");
			$B->style("float:left;margin-right:10px;margin-top:-7px;margin-left:-5px;");
			
			#border-width:1px;border-style:solid;
			$html .= "<div style=\"\" class=\"SpellbookSpell\">
				<div style=\"margin:10px;border-radius:5px;\" class=\"borderColor1 spell\">
					<div class=\"backgroundColor2\" style=\"padding:10px;padding-bottom:5px;\">
						$B<span style=\"float:right;margin-top:7px;\">".($isAdmin ? "Admin!" : "")."</span><h2  style=\"margin-bottom:0px;margin-top:0px;\">$key</h2>
					</div>
					".($text != "" ? "<div style=\"padding:7px;height:115px;overflow:auto;\" class=\"SpellbookDescription\">$text</div>" : "")."
				</div>
			</div>";
		}
		$html .= "</div>";
		#echo "<pre>";
		#print_r($menu);
		
		#echo "</pre>";
		return $html;
	}
	
	public function clearToken(){
		$U = new UserGUI(Session::currentUser()->getID());
		$U->clearWebAuthData();
	}
	
	public function changePasswordPopup(){
		$F = new HTMLForm("changePW", array("oldPW", "newPW1", "newPW2"));
		$F->getTable()->setColWidth(1, 120);
		
		$F->setLabel("oldPW", "Aktuelles Passwort");
		$F->setLabel("newPW1", "Neues Passwort");
		$F->setLabel("newPW2", "Wiederholung");
		
		$F->setType("oldPW", "password");
		$F->setType("newPW1", "password");
		$F->setType("newPW2", "password");
		
		$F->setSaveRMEPCR("Speichern", "", "Spellbook", "-1", "changePasswordDo", OnEvent::closePopup("Spellbook"));
		
		echo $F;
	}
	
	public function changePasswordDo($old, $new1, $new2){
		$U = new User(Session::currentUser()->getID());
		$U->loadMe(false);
		
		if($U->A("SHApassword") != sha1($old))
			Red::alertD("Aktuelles Passwort falsch!");
		
		if($new1 != $new2)
			Red::alertD("Neue Passwörter nicht identisch!");
		
		$U->changeA("SHApassword", $new1);
		$U->saveMe();
	}
	
	public function getSortable($echo){
		
		$entries = $_SESSION["CurrentAppPlugins"]->getMenuEntries();
		#$appMenuHidden = MenuGUI::getAppMenuOrder("appMenuHidden");
		$appMenuDisplayed = MenuGUI::getAppMenuOrder("appMenuDisplayed");
		$userHiddenPlugins = mUserdata::getHiddenPlugins(true);
		
		if($appMenuDisplayed == "")
			$appMenuDisplayed = implode(";", $_SESSION["CurrentAppPlugins"]->getMenuEntries());
		
		$es = MenuGUI::sort($entries, $appMenuDisplayed);#, $appMenuHidden);
		
		$appMenuDisplayed = explode(";", $appMenuDisplayed);
		
		$L = new HTMLList();
		$L->addListStyle("list-style-type:none;margin-left:0px;margin-top:10px;");
		$es = array_reverse($es, true);
		foreach($es AS $key => $value){
			if(isset($userHiddenPlugins[$key]))
				continue;
			if(isset($userHiddenPlugins[$value]))
				continue;
			
			if(!in_array($value, $appMenuDisplayed))
				continue;
			
			$L->addItem($key);
			$L->addItemClass("SpellbookSortTabsHandle");
			$L->addItemStyle("padding:5px;cursor:move;margin:0px;");
			$L->setItemID("appMenu_$value");
		}
		
		#$I = new HTMLInput("showAgain", "checkbox");
		#$I->style("float:left;margin-right:5px;");
		#$I->onclick(addslashes(OnEvent::rme(new mUserdata(-1), "setUserdata", array("'hideTooltipDashboard'", "'1'"))));
		
		$L .= OnEvent::script(OnEvent::sortable("#SpellbookSortTabs ul", ".SpellbookSortTabsHandle", "Spellbook::updateOrder", "y", null, null, "Menu.refresh();")."\$j('#SpellbookSortTabs ul li').hover(function() {
      \$j(this).addClass('backgroundColor2');
    }, function() {
      \$j(this).removeClass('backgroundColor2');
    });
	".((mUserdata::getUDValueS("hideTooltipDashboard", "0") == "0" AND mUserdata::getUDValueS("hideTooltips", "0") == "0") ? "
	\$j('#SpellbookMenuEntry img').qtip(\$j.extend({}, qTipSharedRed, {
		content: {
			text: 'Klicken Sie auf die Weltkugel, um die angezeigten Plugins und ihre Reihenfolge zu ändern.<br /><div style=\"margin-top:10px;\"><a href=\"#\" style=\"color:grey;\" onclick=\"".addslashes(OnEvent::rme(new mUserdata(-1), "setUserdata", array("'hideTooltipDashboard'", "'1'", "''", "0", "1")))." return false;\">Diesen Tipp nicht mehr anzeigen</a></div><div style=\"clear:both;margin-top:5px;\"><a href=\"#\" style=\"color:grey;\" onclick=\"".addslashes(OnEvent::rme(new mUserdata(-1), "setUserdata", array("'hideTooltips'", "'1'", "''", "0", "1")))." return false;\">Keine Tipps mehr anzeigen</a></div>', 
			title: {
				text: 'Dashboard',
				button: true
			}
		}
	}));" : ""));
		
		$L = "<h2 style=\"padding-left:0px;\">Reihenfolge</h2>$L";
		
		if($echo)
			echo $L;
		
		return $L;
	}
	
	public function updateOrder($values){
		$ex = explode(";", $values);
		foreach($ex AS $k => $v){
			$ex[$k] = str_replace("appMenu", "", $v);
		}
		$ex = array_reverse($ex, true);
		
		MenuGUI::saveAppMenuOrder("appMenuDisplayed", implode(";", $ex));
	}
}

?>
