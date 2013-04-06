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
class DesktopGUI extends UnpersistentClass implements iGUIHTML2 {
	public function getHTML($id){
		
		if($_SESSION["S"]->isUserAdmin()) {
			$D = new ADesktopGUI();
			return $D->getHTML($id);
		}
		
		$c = Applications::activeApplication()."DesktopGUI";

		try {
			$c = new $c();
			
			if($id == "1")
				return "
					<div style=\"float:right;width:33.3333%;\"><div id=\"desktopRight\" style=\"padding:10px;\">".$c->getHTML($id)."</div></div>
					<div style=\"float:right;width:33.3333%;\"><div id=\"desktopMiddle\" style=\"padding:10px;width:90%;margin:auto;\"></div></div>
					<div style=\"float:right;width:33.3333%;\"><div id=\"desktopLeft\" style=\"padding:10px;\"></div></div>
					".OnEvent::script(OnEvent::frame("desktopLeft", "Desktop", "2").OnEvent::frame("desktopMiddle", "Desktop", "3"))."<div style=\"clear:both;\"></div>";
			
			return $c->getHTML($id);
		} catch(ClassNotFoundException $e) {}
	}
}
?>