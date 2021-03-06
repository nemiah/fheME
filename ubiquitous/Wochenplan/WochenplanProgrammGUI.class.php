<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class WochenplanProgrammGUI extends WochenplanProgramm implements iGUIHTML2 {
	function getHTML($id){
		$bps = $this->getMyBPSData();
		
		$this->loadMeOrEmpty();
		$gui = new HTMLGUIX($this);
	
		if(isset($bps["categoria"]) AND $this->getID() == -1)
			$this->changeA ("WochenplanProgrammKategorie", $bps["categoria"]);
		
		$gui->type("WochenplanProgrammKategorie", "select", array(1 => "Vormittag", 2 => "Abend"));
		
		if($this->getID() != -1){
			$B = new Button("Eintrag löschen", "trash", "icon");
			$B->rmePCR("WochenplanProgramm", $this->getID(), "deleteMe", "", "function(){ if(!confirm('Eintrag löschen?')) return; ".OnEvent::closePopup("WochenplanProgramm").OnEvent::reload("Screen")."}");
			$B->style("float:right;margin:5px;");
			$gui->addTopButton($B);
		}
		
		$gui->displayMode("popupS");
		
		return $gui->getEditHTML();
	}
}
?>