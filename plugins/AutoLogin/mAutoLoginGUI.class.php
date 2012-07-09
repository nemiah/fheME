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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mAutoLoginGUI extends mAutoLogin implements iGUIHTML2 {
	public function getHTML($id){
		$this->addJoinV3("User","AutoLoginUserID","=","UserID");
		$gui = new HTMLGUI();
		#$gui->VersionCheck("mAutoLogin");
		
		$this->lCV3($id);
		
		$gui->setName("AutoLogin");
		$gui->setObject($this);
		
		$gui->setShowAttributes(array("username", "AutoLoginIP", "AutoLoginApp"));

		$t = new HTMLTable(1);
		
		$t->addRow("<img src=\"./images/navi/warning.png\" style=\"float: left; margin-right: 10px;\" />Dieses Plugin ist möglicherweise ein Sicherheitsrisiko!");
		try {
			return ($id == -1 ?$t : "").$gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
}
?>
