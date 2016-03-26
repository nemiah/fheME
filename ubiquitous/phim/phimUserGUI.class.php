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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class phimUserGUI extends phimUser implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Benutzer");
	
		$AC = Users::getUsersArray("Bitte auswählen...");
		
		$gui->label("phimUserSystemName", "Windows-Login");
		$gui->label("phimUserUserID", "Benutzer");
		$gui->label("phimUserActive", "Aktiv?");
		
		$gui->type("phimUserUserID", "select", $AC);
		$gui->type("phimUserActive", "checkbox");
		$gui->type("phimUserToken", "readonly");
		
		return $gui->getEditHTML();
	}
}
?>