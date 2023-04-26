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
class NenaHomeGUI extends NenaHome implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("NenaHome");
		
		$gui->label("NenaHomeOpenWeatherMapID", "Wetter");
		$gui->label("NenaHomeWechselrichterID", "Wechselrichter");
		$gui->label("NenaHomeFhemServerID", "Fhem-Server");
		
		$gui->type("NenaHomeWechselrichterID", "select", anyC::get("Wechselrichter"), "WechselrichterName", "Bitte auswählen…");
		$gui->type("NenaHomeOpenWeatherMapID", "select", anyC::get("OpenWeatherMap"), "OpenWeatherMapName", "Bitte auswählen…");
		$gui->type("NenaHomeFhemServerID", "select", anyC::get("FhemServer"), "FhemServerName", "Bitte auswählen…");
	
		return $gui->getEditHTML();
	}
}
?>