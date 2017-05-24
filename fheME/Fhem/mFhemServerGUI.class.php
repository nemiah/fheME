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
class mFhemServerGUI extends anyC implements iGUIHTML2 {
	public function __construct(){
		$this->setCollectionOf("FhemServer");
	}
	
	public function getHTML($id){
		$gui = new HTMLGUI();
		$gui->VersionCheck("mFhem");
		
		$this->lCV3($id);
		
		$gui->setName("Server");
		$gui->setObject($this);
		
		$gui->setShowAttributes(array("FhemServerName"));
		
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
}
?>
