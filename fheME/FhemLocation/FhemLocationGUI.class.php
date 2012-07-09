<?php
/**
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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class FhemLocationGUI extends FhemLocation implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("FhemLocation");

		$gui->setLabel("FhemLocationName", "Name");
		$gui->setLabel("FhemLocationBindHosts", "Hosts");

		$gui->setType("FhemLocationBindHosts", "textarea");
		$gui->setFieldDescription("FhemLocationBindHosts", "Enter a line for each hostname or IP here, wich will automatically display the controls for this location. Your current Location is <a href=\"#\" onclick=\"$('FhemLocationBindHosts').value = ($('FhemLocationBindHosts').value != '' ? $('FhemLocationBindHosts').value+String.fromCharCode(10) : $('FhemLocationBindHosts').value)+'$_SERVER[REMOTE_ADDR]'; return false;\">".$_SERVER["REMOTE_ADDR"]."</a>");
		$gui->setStandardSaveButton($this);
	
		return $gui->getEditHTML();
	}
}
?>