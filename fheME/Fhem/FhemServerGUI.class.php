<?php
/*
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class FhemServerGUI extends FhemServer implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Server");

		$gui->setLabel("FhemServerName","Name");
		$gui->setLabel("FhemServerIP","IP");
		$gui->setLabel("FhemServerPort","Port");
		$gui->setLabel("FhemServerType","Type");
		$gui->setLabel("FhemServerURL","URL");

		$types = array("fhem","Webservice");

		$gui->setType("FhemServerType", "select");
		$gui->setOptions("FhemServerType", array_keys($types), array_values($types));

		$gui->setStandardSaveButton($this, "mFhemServer");
		$gui->translate($this->loadTranslation());
		return $gui->getEditHTML();
	}
}
?>