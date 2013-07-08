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
class JSLoaderGUI extends JSLoader implements iGUIHTML2 {
	public function getHTML($id){
		if($_SESSION["S"]->checkIfUserLoggedIn() == true) return "";
		
		$js = "";
		$scripts = $_SESSION["JS"]->getScripts();
		$folders = $_SESSION["JS"]->getFolders();
		$apps = $_SESSION["JS"]->getApps();
		
		for($i = 0;$i<count($scripts);$i++) $js .= "
./".$apps[$i]."/".(is_array($folders[$i]) ? $folders[$i][0] : $folders[$i])."/".$scripts[$i]."?r=".rand();
		
		$js .= "\n./javascript/DynamicJS.php?r=".rand();
		
		return $js;

	}
}
?>