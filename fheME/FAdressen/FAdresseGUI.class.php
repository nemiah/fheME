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
class FAdresseGUI extends AdresseGUI {
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->setParser("geb", "Util::CLDateParserE");
	}
	
    function getHTML($id) {
		$gui = new HTMLGUIX($this);
		$gui->name("Adresse");

		$gui->attributes(array("vorname", "nachname", "strasse", "nr", "plz", "ort", "land", "tel", "mobil", "email", "geb"));

		$gui->label("plz", "PLZ");
		$gui->label("strasse", "Straße");
		$gui->label("ort", "Ort");
		$gui->label("vorname", "Vorname");
		$gui->label("nachname", "Nachname");
		$gui->label("land", "Land");
		$gui->label("tel", "Telefon");
		$gui->label("email", "E-Mail");
		$gui->label("mobil", "Mobil");
		$gui->label("nr", "Nr");
		$gui->label("geb", "Geburtstag");
		
		$gui->type("geb", "date");
		#$gui->type("land", "select", ISO3166::getCountries());
		
		$gui->space("tel");
		$gui->space("geb");
		$gui->space("strasse");
		
		return $gui->getEditHTML();
    }

    public function getClearClass($mode = "default") {
		if ($mode == "GUI")
			return "FAdresse";

		return parent::getClearClass();
    }

}
?>