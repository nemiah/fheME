<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class mAdresseGUI extends AdressenGUI {
	//This class exists for compatibility reasons (autocomplete)!
	
	function getHTML($id, $page, $frame = null) {
		$this->loadMultiPageMode($id, $page);
		
		$gui = new HTMLGUIX($this);
		
		$tab = "";
		if(Session::isPluginLoaded("mAdressBuch") AND $id == -1)
			$tab = mAdressBuchGUI::getSelectionMenu($this, $frame, false, true);
		
		$gui->displayGroup("KategorieID", "AdressenGUI::DGParser");
		$gui->options(true, true, true, true);
		$gui->parser("firma","mAdresseGUI::parserFirma");
		
		if($frame != null)
			$gui->targetFrame($frame);
		
		$gui->attributes(array("firma"));
		
		return $tab.$gui->getBrowserHTML($id);
	}
	
	public static function parserFirma($w, $E){
		$s = array();
		$s[0] = $E->A("vorname");
		$s[1] = $E->A("nachname");
		$s[2] = $E->getID();
		$s[3] = $E->A("type");
		$s[4] = $E->A("tel");
		$s[5] = $E->A("fax");
		$s[6] = $E->A("email");
		$s[7] = $E->A("mobil");
		$s[8] = $E->A("homepage");
		$s[9] = __CLASS__;
		
		return self::firmaParser($w, "", $s);
	}
}
?>
