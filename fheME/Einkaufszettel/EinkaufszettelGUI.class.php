<?php
/**
 *  This file is part of wasGibtsMorgen.

 *  wasGibtsMorgen is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  wasGibtsMorgen is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class EinkaufszettelGUI extends Einkaufszettel implements iGUIHTML2 {
	
	function __construct($ID) {
		parent::__construct($ID);
		$this->setParser("EinkaufszettelTime", "Util::CLDateTimeParser");
		$this->setParser("EinkaufszettelBoughtTime", "Util::CLDateTimeParser");
	}
	
	function getHTML($id){
		if($this->getID() == -1){
			$this->loadMeOrEmpty();
			$this->changeA("EinkaufszettelBought", 1);
		}
		
		$gui = new HTMLGUIX($this);
		$gui->name("Einkaufszettel");
	
		$gui->attributes(array(
			"EinkaufszettelName",
			"EinkaufszettelEinkaufszettelKategorieID",
			"EinkaufszettelImmer",
			"EinkaufszettelBought"
		));
		
		$gui->label("EinkaufszettelImmer", "Immer anzeigen?");
		$gui->label("EinkaufszettelEinkaufszettelKategorieID", "Kategorie");
		
		$gui->type("EinkaufszettelBought", "hidden");
		$gui->type("EinkaufszettelEinkaufszettelKategorieID", "select", anyC::get("EinkaufszettelKategorie"), "EinkaufszettelKategorieName", "Bitte auswählen...");
		
		$gui->type("EinkaufszettelImmer", "checkbox");
		
		return $gui->getEditHTML();
	}
}
?>