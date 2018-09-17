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
 *  2007 - 2018, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class phimGruppeGUI extends phimGruppe implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Gruppe");
	
		$gui->hideLine("phimGruppeMasterUserID");
		
		$gui->label("phimGruppeMembers", "Mitglieder");
		
		$gui->parser("phimGruppeMembers", "parserMembers");
		
		return $gui->getEditHTML();
	}
	
	public static function parserMembers($w, $l, $E){
		$Users = Users::getUsersArray();
		
		$r = "";
		foreach($Users AS $ID => $U){
			$I = new HTMLInput("user_$ID", "checkbox", strpos($w, ";$ID;") !== false ? "1" : "0");
			$I->onchange("if(this.checked) \$j('[name=phimGruppeMembers]').val(\$j('[name=phimGruppeMembers]').val()+';$ID;'); else  \$j('[name=phimGruppeMembers]').val(\$j('[name=phimGruppeMembers]').val().replace(';$ID;', ''));");
			
			$r .= $I." ".$U."<br>";
		}
		
		$I = new HTMLInput("phimGruppeMembers", "hidden", $w);
		
		return $r.$I;
	}
}
?>