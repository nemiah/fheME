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
 *  2007, 2008, 2009, 2010, 2011, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class Einkaufszettel extends PersistentObject {
	function saveMe($checkUserData = true, $output = false) {
		$O = new Einkaufszettel($this->getID());
		
		if($O->A("EinkaufszettelBought") == "0" AND $this->A("EinkaufszettelBought") == "1")
			$this->changeA("EinkaufszettelBoughtTime", $this->hasParsers ? Util::CLDateTimeParser(time()) : time());
		
		if($O->A("EinkaufszettelBought") == "1" AND $this->A("EinkaufszettelBought") == "0")
			$this->changeA("EinkaufszettelBoughtTime", "");
		
		parent::saveMe($checkUserData, $output);
	}
	
	public function setBought(){
		$this->changeA("EinkaufszettelBought", "1");
		$this->saveMe();
	}
	
	public function setUnBought(){
		$this->changeA("EinkaufszettelBought", "0");
		$this->saveMe();
	}
}
?>