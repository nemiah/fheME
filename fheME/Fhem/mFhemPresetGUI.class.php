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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class mFhemPresetGUI extends anyC implements iGUIHTML2 {
	/*function __construct(){
		
		#$this->setAssocV3("type","=","FhemEvent");
		parent::__construct();
	}*/

	function subPointer(){
		$this->i--;
	}

	function getHTML($id){
		$gui = new HTMLGUI();
		$this->addJoinV3("FhemLocation", "FhemPresetLocationID", "=", "FhemLocationID");
		$this->addOrderV3("FhemLocationName");
		$this->lCV3($id);
		
		$gui->setName("Preset");
		$gui->setObject($this);
		$gui->setCollectionOf("FhemPreset","Preset");
		#$gui->setIsDisplayMode(true);
		#$gui->setEditInDisplayMode(true, "contentLeft");
		$gui->setShowAttributes(array("FhemLocationName","FhemPresetName"));
		
		$t = new HTMLTable(1);
		$t->addRow("You'll have to use the \"register settings\"-button in the devices-menu to make this presets known to the server.");
		
		try {
			return ($id == -1 ? $t : "").$gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
}
?>