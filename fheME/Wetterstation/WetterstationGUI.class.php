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
class WetterstationGUI extends Wetterstation implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Wetterstation");
	
		$B = $gui->addSideButton("Log", "./fheME/Wetterstation/log.png");
		$B->popup("", "Log", "Wetterstation", $this->getID(), "logShow", "", "", "{width: 800}");
		
		return $gui->getEditHTML();
	}
	
	public function logShow(){
		$AC = anyC::get("WetterstationLog", "WetterstationLogWetterstationID", $this->getID());
		$AC->addOrderV3("WetterstationLogTime", "DESC");
		$AC->setLimitV3(200);
		
		$T = new HTMLTable(5);
		$T->maxHeight(400);
		$T->addHeaderRow([
			"Zeit",
			"T innen",
			"Wind"
		]);
		while($L = $AC->n()){
			$T->addRow([
				Util::CLDateTimeParser($L->A("WetterstationLogTime")),
				$L->A("WetterstationLogIndoorTemp"),
				$L->A("WetterstationLogOutdoorWindSpeed")
			]);
		}
		
		echo $T;
	}
}
?>