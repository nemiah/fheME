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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
class AutoLoginGUI extends AutoLogin implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		if($id == -1) $this->A->AutoLoginIP = $_SERVER["REMOTE_ADDR"];
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("AutoLogin");

		$U = new Users();
		$U->addAssocV3("isAdmin", "=", "0");
		$gui->selectWithCollection("AutoLoginUserID", $U, "username");
		
		$gui->setLabel("AutoLoginUserID", "Benutzer");
		$gui->setLabel("AutoLoginIP", "IP");
		$gui->setLabel("AutoLoginApp", "Anwendung");
		
		$apps = $_SESSION["applications"]->getApplicationsList();
		$gui->setType("AutoLoginApp","select");
		$gui->setOptions("AutoLoginApp", array_values($apps), array_keys($apps));
		
		$gui->setStandardSaveButton($this);
	
		return $gui->getEditHTML();
	}
}
?>