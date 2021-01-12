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
class MenuGUI extends UnpersistentClass implements iGUIHTML2, icontextMenu {
	function  __construct() {
		parent::__construct();
		$this->customize();
	}

	public function getHTML($id){
		if($_SESSION["S"]->checkIfUserLoggedIn() == true) return -1;
		
	
		$es = $_SESSION["CurrentAppPlugins"]->getMenuEntries();
		$ts = $_SESSION["CurrentAppPlugins"]->getMenuTargets();
		$icons = $_SESSION["CurrentAppPlugins"]->getIcons();
		
		$appIco = $_SESSION["applications"]->getApplicationIcon($_SESSION["applications"]->getActiveApplication());
		if(isset($_COOKIE["phynx_color"]) AND $_COOKIE["phynx_color"] != "standard"){
			$suffix = strrchr($appIco,".");
			$newLogo = str_replace($suffix, ucfirst($_COOKIE["phynx_color"]).$suffix ,$appIco);
			if(file_exists(".".$newLogo))
				$appIco = $newLogo;
		}

		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		$newAppIco = Aspect::joinPoint("appLogo", $this, __METHOD__, array($_SESSION["applications"]->getActiveApplication()));
		if($newAppIco != null) $appIco = $newAppIco;
		// </editor-fold>
		
		try {
			$layout = mUserdata::getUDValueS("phynxLayout", "horizontal");
		} catch (Exception $e){
			$layout = "horizontal";
		}
		
		$appMenuHidden = "";
		$appMenuDisplayed = "";
		$appMenuActive = (!$_SESSION["S"]->isUserAdmin() AND ($layout == "fixed" OR $layout == "horizontal" OR $layout == "desktop" OR $layout == "vertical"));

		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		$aspectAppMenuActive = Aspect::joinPoint("appMenuActive", $this, __METHOD__);
		if($aspectAppMenuActive !== null) $appMenuActive = $aspectAppMenuActive;
		// </editor-fold>

		$appIco = Environment::getS("ApplicationIcon:".Applications::activeApplication(), $appIco);
		
		if($appIco != "") {
			if(count($_SESSION["applications"]->getApplicationsList()) > 1 AND !$_SESSION["S"]->isAltUser())
				echo "<img src=\"$appIco\" id=\"appLogo\" style=\"margin-left:10px;float:left;\" alt=\"Abmelden/Anwendung wechseln\" title=\"Abmelden/Anwendung wechseln\" onclick=\"".Environment::getS("onLogout", "phynxContextMenu.start(this, 'Menu','1','Anwendung wechseln:','right');")."\" />";
			else
				echo "<img src=\"$appIco\" id=\"appLogo\" style=\"margin-left:10px;float:left;\" alt=\"Abmelden\" title=\"Abmelden\" onclick=\"".Environment::getS("onLogout", "userControl.doLogout();")."\" />";
		}
		

		$bigWorld = false;
		if($layout == "desktop" OR $layout == "vertical")
			$bigWorld = true;
		
		if(!$_SESSION["S"]->isUserAdmin()) {
			$userHiddenPlugins= mUserdata::getHiddenPlugins(true);
			$userHiddenPlugins = Aspect::joinPoint("alterHidden", $this, __METHOD__, array($userHiddenPlugins), $userHiddenPlugins);
			
			$U = new mUserdata();
			$U->addAssocV3("typ","=","TTP");


			$B = new Button(Environment::getS("renameApplication:".$_SESSION["applications"]->getActiveApplication(), $_SESSION["applications"]->getActiveApplication()),"application");
			$B->type("icon");
			$B->className($bigWorld ? "tabImg" : "smallTabImg");#".(($t == null OR $t == "big") ? "class=\"tabImg\"" : "class=\"smallTabImg\"")."
			$B->hasMouseOverEffect(false);
			$B->id("busyBox");

			#$appMenuHidden = $this->getAppMenuOrder("appMenuHidden");
			$appMenuDisplayed = $this->getAppMenuOrder("appMenuDisplayed");
			
			#if($appMenuDisplayed != "" AND $appMenuHidden == "")
			#	$appMenuHidden = implode(";", array_diff(array_values($es), explode(";", $appMenuDisplayed)));
			
		}

		$appMenuH = "
			<li id=\"appMenu_emptyList\" style=\"height:auto;".($appMenuHidden != "emptyList" ? "display:none;" : "")."\">Ziehen Sie Einträge auf diese Seite, um sie aus dem Menü auszublenden und nur hier anzuzeigen.</li>";
		$appMenuD = "";

		if($appMenuActive)
				$es = self::sort($es, $appMenuDisplayed);#, $appMenuHidden);

		echo "
			<div id=\"navTabsWrapper\">";
		if($appMenuActive) echo "
			<div
				class=\"navBackgroundColor navBorderColor ".($bigWorld ? "" : " smallTab")." navTab\"
				id=\"SpellbookMenuEntry\"
			>
				<div onclick=\"contentManager.loadPlugin('contentScreen', 'Spellbook', 'SpellbookGUI;-');\" style=\"padding:3px;padding-right:7px;padding-top:7px;height:18px;\">
				$B".($bigWorld ? Environment::getS("renameApplication:".$_SESSION["applications"]->getActiveApplication(), $_SESSION["applications"]->getActiveApplication()) : "")."
				</div>
			</div>";

		$collapsedTabs = Environment::getS("collapsedTabs", "0") == "1";
		
		foreach($es as $key => $value) {
			if(isset($userHiddenPlugins[$value])) 
				continue;

			T::load(Util::getRootPath().Applications::activeApplication().DIRECTORY_SEPARATOR.AppPlugins::i()->getFolderOfPlugin($value), $value);
			#$single = $_SESSION["CurrentAppPlugins"]->isCollectionOfFlip($value);
			#$anyC = new anyC();
			#$text = $anyC->loadLanguageClass($single);
			#if($text != null AND $text->getMenuEntry() != "") $key = $text->getMenuEntry();
			
			$t =  !$_SESSION["S"]->isUserAdmin() ? $U->getUDValueCached("ToggleTab$value") : "big";

			if($t == null AND $collapsedTabs)
				$t = "small";

			$key = Aspect::joinPoint("renameTab", $this, __METHOD__, array($key), $key);
			
			if($layout == "vertical" OR $layout == "desktop")
				$t = "big";

			#$emptyFrame = "contentLeft";
			#if(isset($ts[$value]) AND $ts[$value] == "contentLeft") $emptyFrame = "contentRight";

			$class = Aspect::joinPoint("alterClass", $this, __METHOD__, array($value), $value);
			
			#$onclick = "contentManager.emptyFrame('contentLeft'); contentManager.emptyFrame('contentRight'); contentManager.emptyFrame('contentScreen'); contentManager.loadFrame('".(isset($ts[$value]) ? $ts[$value] : "contentRight")."', '$value', -1, 0, '{$value}GUI;-');$('windows').update('');";
			$onclick = "contentManager.loadPlugin('".(isset($ts[$value]) ? $ts[$value] : "contentRight")."', '$class', '{$class}GUI;-');";
			
			$B = new Button($key,$icons[$value], "icon");
			$B->style("float:left;margin-right:10px;");

			$BM = new Button("Reihenfolge ändern","./images/i2/topdown.png");
			$BM->type("icon");
			$BM->style("float:right;margin-right:5px;");
			$BM->className("appMenuHandle");

			$appMenu = "
			<li
				id=\"appMenu_$value\"
				onmouseover = \"this.className = 'navBackgroundColor';\"
				onmouseout = \"this.className = '';\"
			>
				$BM
				<div
					onclick=\"appMenu.hide(); $onclick\"
				>
				$B<p>".T::_($key)."</p>
				</div>
			</li>";

			if(strpos($appMenuHidden, $value) !== false)
				$appMenuH .= $appMenu;
			else
				$appMenuD .= $appMenu;



			$style = ((strpos($appMenuHidden, $value) !== false AND $appMenuActive) ? "style=\"display:none;\"" : "");

			$BP = new Button($key, $icons[$value], "icon");
			$BP->id($value."MenuImage");
			if(($t == null OR $t == "big"))
				$BP->className ("tabImg");
			else
				$BP->className ("smallTabImg");
			$BP->style("width:32px;height:32px;");
			
			$hideClass = "";
			if(Session::isUserAdminS() AND Session::isInstallation() AND $value != "mInstallation" AND $value != "mWartung")
				$hideClass = " installHiddenTab";
			
			
			echo "
				
				<div
					id=\"".$class."MenuEntry\"
					class=\"navBackgroundColor navBorderColor ".(($t == null OR $t == "big") ? "" : " smallTab")." navTab$hideClass\"
					$style
					>
					
					<div onclick=\"$onclick\" style=\"padding:3px;padding-right:7px;padding-top:7px;height:18px;\">
						$BP
						".(($t == null OR $t == "big") ? T::_($key) : "")."
					</div>
				</div>";
		}
		echo "
				<div style=\"float:none;clear:both;border:0px;height:0px;width:0px;margin:0px;padding:0px;\"></div>
			</div>";
		/*
			<div id=\"appMenuContainer\" class=\"backgroundColor0 navBorderColor\" style=\"display:none;\">
				<ul style=\"min-height:50px;\" id=\"appMenuHidden\">$appMenuH</ul>
				<p class=\"backgroundColor2\" style=\"cursor:pointer;background-image:url(./images/navi/down.png);background-repeat:no-repeat;background-position:95% 50%;\" onclick=\"if($('appMenuDisplayedContainer').style.display == 'none') new Effect.BlindDown('appMenuDisplayedContainer'); else new Effect.BlindUp('appMenuDisplayedContainer');\">Weitere Reiter</p>
				<div id=\"appMenuDisplayedContainer\" style=\"display:none;\"><ul style=\"min-height:50px;\" id=\"appMenuDisplayed\">$appMenuD</ul><p>Um die Sortierung der Einträge zu übernehmen, muss die Anwendung <a href=\"#\" onclick=\"Installation.reloadApp(); return false;\">neu geladen werden</a>.</p></div>
			</div>";*/
		
		#echo OnEvent::script("");
		
		if(!$_SESSION["S"]->isUserAdmin()) {
			$ud = new mUserdata();
			$al = $ud->getUDValue("noAutoLogout","false");
			
			if($al == "true") echo "<script type=\"text/javascript\">contentManager.startAutoLogoutInhibitor(".(file_exists(Util::getRootPath()."plugins/AppServer/index.php") ? "1" : "0").");</script>";
		}
		
		echo OnEvent::script("contentManager.isAltUser = ".(Session::isAltUserS() ? "true" : "false").";");
		
		$onTimeout = Environment::getS("onTimeout", null);
		if($onTimeout != null)
			echo OnEvent::script("Menu.onTimeout = $onTimeout;");
		try {
			$c = get_class(Session::currentUser());
			$U = new $c(Session::currentUser()->getID());
			$ex = explode("_", $U->A("language"));
			if(isset($ex[2]))
				unset($ex[2]);
			echo "<script type=\"text/javascript\">Interface.application = '".Applications::activeApplication()."'; Interface.locale = '".implode("_", $ex)."'; \$j.datepicker.setDefaults(\$j.datepicker.regional['".implode("_", $ex)."']); ".(Session::physion() ? "\$j('#navigation').hide();" : "")."</script>";
		} catch (Exception $e){ }
	}

	public static function sort($reiter, $appMenuDisplayed){#, $appMenuHidden){
		#$reiterStart = $reiter;
		
		$reiterEnde = array();
		if($appMenuDisplayed == "")
			return $reiter;
		#$entries = explode(";", $appMenuHidden);

		#foreach($entries AS $plugin){
		#	$e = array_search($plugin, $reiterStart);
		#	if($e === false) continue;

		#	$reiterEnde[$e] = $reiterStart[$e];
		#	unset($reiterStart[$e]);
		#}

		$entries = explode(";", $appMenuDisplayed);
/*
		foreach($entries AS $plugin){
			$e = array_search($plugin, $reiterStart);
			if($e === false) continue;

			$reiterEnde[$e] = $reiterStart[$e];
			unset($reiterStart[$e]);
		}

		foreach($reiterStart as $k => $v){
			$reiterEnde[$k] = $v;
			unset($reiterStart[$k]);
		}*/

		
		foreach($entries AS $plugin){
			$e = array_search($plugin, $reiter);
			if($e === false) continue;
			
			$reiterEnde[$e] = $plugin;
		}
		
		return $reiterEnde;
	}
	
	public function autoLogoutInhibitor(){
		/**
		 * Has to do nothing. Just beeing here is enough.
		 */
	}

	public static function saveAppMenuOrder($cat, $order){
		$order1 = substr($order, 0, 150);
		$order2 = substr($order, 150, 150);

		$ud = new mUserdata();
		$ud->setUserdata(Applications::activeApplication().$cat."1", $order1);
		$ud->setUserdata(Applications::activeApplication().$cat."2", $order2 === false ? "" : $order2);
	}

	public static function getAppMenuOrder($cat){
		$ud = new mUserdata();
		$o1 = $ud->getUDValue(Applications::activeApplication().$cat."1", "");

		$ud = new mUserdata();
		$o2 = $ud->getUDValue(Applications::activeApplication().$cat."2", "");

		$o = $o1.$o2;

		#if($cat == "appMenuHidden" AND $o == "") $o = "emptyList";

		return $o;
	}

	public function showTab($plugin){
		#$appMenuHidden = explode(";", self::getAppMenuOrder("appMenuHidden"));
		#if(array_search($plugin, $appMenuHidden) !== false)
		#	unset($appMenuHidden[array_search($plugin, $appMenuHidden)]);
		
		$appMenuDisplayed = explode(";", self::getAppMenuOrder("appMenuDisplayed"));
		$appMenuDisplayed[] = $plugin;
		
		self::saveAppMenuOrder("appMenuDisplayed", implode(";", $appMenuDisplayed));
		#self::saveAppMenuOrder("appMenuHidden", implode(";", $appMenuHidden));
	}
	
	public function hideTab($plugin){
		#$appMenuHidden = explode(";", self::getAppMenuOrder("appMenuHidden"));
		#$appMenuHidden[] = $plugin;
		
		$D = self::getAppMenuOrder("appMenuDisplayed");
		
		if($D == "")
			$D = implode(";", $_SESSION["CurrentAppPlugins"]->getMenuEntries());
		
		$appMenuDisplayed = explode(";", $D);
		if(array_search($plugin, $appMenuDisplayed) !== false)
			unset($appMenuDisplayed[array_search($plugin, $appMenuDisplayed)]);
		
		self::saveAppMenuOrder("appMenuDisplayed", implode(";", $appMenuDisplayed));
		#self::saveAppMenuOrder("appMenuHidden", implode(";", $appMenuHidden));
	}
	
	public function toggleTab($plugin, $mode = null){
		$U = new mUserdata();
		$U->addAssocV3("typ","=","TTP");
		$t = $U->getUDValueCached("ToggleTab$plugin");

		$collapsedTabs = Environment::getS("collapsedTabs", "0") == "1";

		if($t == null AND $collapsedTabs)
			$t = "small";

		if($mode != null)
			$t = $mode;
		
		if($t == null or $t == "big")
			$U->setUserdata("ToggleTab$plugin","small","TTP");
		else
			$U->setUserdata("ToggleTab$plugin","big","TTP");
	}
	
	public function getActiveApplicationName($return = false){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>
		
		$name = Applications::activeApplicationLabel();
		$n = Environment::getS("renameApplication:$name", $name)." ".Applications::activeVersion();
		
		if($return)
			return $n;
		
		echo $n;
	}
	
	public function getContextMenuHTML($identifier){
		$sk = Applications::activeApplication();#$_SESSION["applications"]->getActiveApplication();
		$kal = Applications::getList();#$_SESSION["applications"]->getApplicationsList();
		$kal = array_flip($kal);
		natcasesort($kal);
		#print_r($kal);
		#foreach($kal as $k => $v)
		#	$kal[$k] = $k;
		
		$AC = anyC::get("Userdata", "typ", "loginTo");
		$AC->addAssocV3("UserID", "=", Session::currentUser()->getID());
		while($UD = $AC->n()){
			if($UD->A("wert") != "0")
				continue;
			
			unset($kal[str_replace("loginTo", "", $UD->A("name"))]);
		}
			
		$gui = new HTMLGUI();
		echo "<div style=\"max-height:400px;overflow:auto;\">".$gui->getContextMenu($kal, 'Menu','1',$sk,'phynxContextMenu.stop(); contentManager.switchApplication(\'%KEY\', true);')."</div>";
		echo "<div class=\"backgroundColor1\" id=\"cMLogout\" onclick=\"userControl.doLogout();\" onmouseover=\"this.className='backgroundColor3';\" onmouseout=\"this.className='backgroundColor1';\" style=\"padding:5px;cursor:pointer;\"><img style=\"float:left;\" title=\"Abmelden\" src=\"./images/i2/logout.png\" onclick=\"userControl.doLogout();\" /><p style=\"padding-top:7px;padding-bottom:7px;padding-left:50px;\"><b>Abmelden</b></p></div>";
	}

	public function saveContextMenu($identifier, $key){
		$_SESSION["S"]->switchApplication($key);
	}
}
?>