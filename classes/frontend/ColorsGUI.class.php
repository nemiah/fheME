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

class ColorsGUI {
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
				$ud->setUserdata("noAutoLogout", $key);
			break;
			
			case "4":
				$ud = new mUserdata();
				$ud->setUserdata("phynxIcons", $key);
				SpeedCache::clearCache();
			break;
		}
	}
}
?>