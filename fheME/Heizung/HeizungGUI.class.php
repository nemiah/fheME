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
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */
class HeizungGUI extends Heizung implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Heizung");
	
		$gui->type("HeizungFhemServerID", "select", anyC::get("FhemServer"), "FhemServerName", "Bitte auswählen…");
		
		$gui->label("HeizungFhemServerID", "Server");
		
		#$B = $gui->addSideButton("Sonne", "new");
		#$B->popup("", "Datenanzeigen", "Heizung", $this->getID(), "sun");
		
		$B = $gui->addSideButton("Daten\nanzeigen", "new");
		$B->popup("", "Datenanzeigen", "Heizung", $this->getID(), "showData", "", "", "{width:800}");
		
		return $gui->getEditHTML();
	}
	
	function showData(){
		$T = new HTMLTable(3);
		
		$xml = $this->getData();
		foreach($xml->THZ_LIST->THZ[0]->STATE AS $STATE)
			$T->addRow([$STATE["key"], $STATE["value"], $STATE["measured"]]);
		
		echo $T;
	}
}
?>