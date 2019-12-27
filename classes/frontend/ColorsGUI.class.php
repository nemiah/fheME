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
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */

class ColorsGUI implements icontextMenu {
	public function getContextMenuHTML($identifier){
		$kal = array();
		
		#if(!isset($_COOKIE["phynx_color"])) $sk = "standard";
		#else $sk = $_COOKIE["phynx_color"];		
		try {
			$sk = mUserdata::getUDValueS("phynxColor", "standard");
		} catch (Exception $e){
			$sk = "standard";
		}
		
		$fp = opendir("../styles/");
		while(($file = readdir($fp)) !== false) {
			if($file{0} == ".") 
			continue;
			if(!is_dir("../styles/$file")) 
				continue;
			
			if($file == "tinymce")
				continue;
			if($file == "darkMode")
				continue;
			
			$label = ucfirst($file);
			if($file == "yellow")
				$label = ucfirst(T::_ ("gelb"));
			
			if($file == "grey")
				$label = ucfirst(T::_ ("grau"));
			
			if($file == "blue")
				$label = ucfirst(T::_ ("blau"));
			
			if($file == "green")
				$label = ucfirst(T::_ ("grün"));
			
			if($file == "lightBlue")
				$label = ucfirst(T::_ ("hellblau"));
			
			if($file == "future")
				$label = ucfirst(T::_ ("weiß"));
			
			$kal[$file] = $label;
		}
		
		$gui = new HTMLGUI();
		$message = "Achtung: Die Seite muss neu geladen werden, damit die Einstellungen wirksam werden. Jetzt neu laden?";
		echo '<div class="backgroundColor2" style="padding:5px;font-weight:bold;">Farbe:</div>';
		echo $gui->getContextMenu($kal, 'Colors','1',$sk,"phynxContextMenu.stop(); Interface.setup();");
		
		
		$sk2 = mUserdata::getUDValueS("phynxLayout", "horizontal");
		echo '<div class="backgroundColor2" style="padding:5px;font-weight:bold;">Layout:</div>';
		echo $gui->getContextMenu(array("horizontal" => "horizontal", "vertical" => "vertikal", "desktop" => "Desktop", "fixed" => "fixiert"), "Colors", "2", $sk2, 'phynxContextMenu.stop(); Interface.setup(); Menu.refresh();');
		
		$ud = new mUserdata();
		$al = $ud->getUDValue("noAutoLogout","false");
		echo '<div class="backgroundColor2" style="padding:5px;font-weight:bold;">Automatisch abmelden:</div>';
		echo $gui->getContextMenu(array("false" => "ja", "true" => "nein"), "Colors", "3", $al, 'phynxContextMenu.stop(); if(confirm(\''.$message.'\')) document.location.reload();');
		
	}

	public function getInterface(){
		$M = new MenuGUI();
		
		$r = new stdClass();
		$r->title = $M->getActiveApplicationName(true);
		try {
			$r->colors = mUserdata::getUDValueS("phynxColor", Environment::getS("cssColorsDir", "standard"));
			$r->layout = mUserdata::getUDValueS("phynxLayout", "horizontal");
		} catch (Exception $e){
			$r->colors = "standard";
			$r->layout = "horizontal";
		}
		
		
		echo json_encode($r, JSON_UNESCAPED_UNICODE);
	}
	
	public function saveContextMenu($identifier, $key){
		switch($identifier){
			case "1":
				//setcookie("phynx_color", $key, time() + 3600 * 24 * 3650, "/");
				mUserdata::setUserdataS("phynxColor", $key);
			break;
			
			case "2":
				//setcookie("phynx_layout", $key, time() + 3600 * 24 * 3650, "/");
				mUserdata::setUserdataS("phynxLayout", $key);
			break;
			
			case "3":
				$ud = new mUserdata();
				$ud->setUserdata("noAutoLogout",$key);
			break;
		}
	}
	
}
?>