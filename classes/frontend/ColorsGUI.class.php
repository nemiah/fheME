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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class ColorsGUI implements icontextMenu {
	public function getContextMenuHTML($identifier){
		$kal = array();
		
		if(!isset($_COOKIE["phynx_color"])) $sk = "standard";
		else $sk = $_COOKIE["phynx_color"];
		
		$fp = opendir("../styles/");
		while(($file = readdir($fp)) !== false) {
			if($file{0} == ".") continue;
			if(!is_dir("../styles/$file")) continue;
			$kal[$file] = ucfirst($file);
		}
		
		$gui = new HTMLGUI();
		$message = "Achtung: Die Seite muss neu geladen werden, damit die Einstellungen wirksam werden. Jetzt neu laden?";
		echo $gui->getContextMenu($kal, 'Colors','1',$sk,"phynxContextMenu.stop(); if(confirm('$message')) document.location.reload();");
		
		
		if(!isset($_COOKIE["phynx_layout"])) $sk2 = "horizontal";
		else $sk2 = $_COOKIE["phynx_layout"];
		echo '<div class="backgroundColor1" style="height: 10px;"></div>';
		echo $gui->getContextMenu(array("horizontal" => "horizontal", "vertical" => "vertikal", "desktop" => "Desktop", "fixed" => "fixiert"), "Colors", "2", $sk2, 'phynxContextMenu.stop(); if(confirm(\''.$message.'\')) document.location.reload();');
		
		$ud = new mUserdata();
		$al = $ud->getUDValue("noAutoLogout","false");
		echo '<div class="backgroundColor1" style="padding:5px;font-weight:bold;">Automatisch ausloggen:</div>';
		echo $gui->getContextMenu(array("false" => "ja", "true" => "nein"), "Colors", "3", $al, 'phynxContextMenu.stop(); if(confirm(\''.$message.'\')) document.location.reload();');
		
	}

	public function saveContextMenu($identifier, $key){
		switch($identifier){
			case "1":
				setcookie("phynx_color", $key, time() + 3600 * 24 * 3650, "/");
			break;
			
			case "2":
				setcookie("phynx_layout", $key, time() + 3600 * 24 * 3650, "/");
			break;
			
			case "3":
				$ud = new mUserdata();
				$ud->setUserdata("noAutoLogout",$key);
			break;
		}
	}
	
}
?>