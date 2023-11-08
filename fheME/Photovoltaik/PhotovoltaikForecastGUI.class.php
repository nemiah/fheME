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
class PhotovoltaikForecastGUI extends PhotovoltaikForecast implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Vorhersage");
	
		$B = $gui->addSideButton("Daten\nanzeigen", "new");
		$B->popup("", "Daten anzeigen", "PhotovoltaikForecast", $this->getID(), "showData", "", "", "{width:800}");
		
		return $gui->getEditHTML();
	}
	
	public function showData(){
		echo "<pre>";
		$data = json_decode($this->A("PhotovoltaikForecastData"));
		print_r($data->data);
		#echo json_encode(json_decode($this->A("PhotovoltaikForecastData")), JSON_PRETTY_PRINT);
		echo "</pre>";
	}
}
?>