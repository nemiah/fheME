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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class WechselrichterGUI extends Wechselrichter implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Wechselrichter");
	
		$B = $gui->addSideButton("Daten\nabfragen", "system");
		$B->popup("", "Daten", "Wechselrichter", $this->getID(), "requestDataPopup");
		
		return $gui->getEditHTML();
	}
	
	public function requestDataPopup(){
		echo "<pre>";

		#$C = new SystemCommand();
		echo shell_exec("python3 ".__DIR__."/kostal_modbusquery.py ".$this->A("WechselrichterIP")." ".$this->A("WechselrichterPort")." 2>&1");
		#$C->execute();
		#echo $C->getOutput();
		
		echo "</pre>";
	}
}
?>