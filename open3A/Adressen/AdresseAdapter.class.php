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
class AdresseAdapter extends Adapter {
	function makeNewLine2($forWhat,  $A){
		$id = parent::makeNewLine2($forWhat, $A);
		
		/*if($A->AuftragID != -1 AND ($A->type == "auftragsAdresse" OR $A->type == "default")){
			$_SESSION["messages"]->addMessage("Updating Auftrag $A->AuftragID to use AdresseID $id...");
			$Auftrag = new Auftrag($A->AuftragID);
			$Auftrag->updateAdressID($id);
		}*/
		
		if($A->AuftragID != -1 AND $A->type == "lieferAdresse"){
			$_SESSION["messages"]->addMessage("Updating GRLBM $A->AuftragID to use AdresseID $id...");
			$GRLBM = new GRLBM($A->AuftragID);
			$GRLBM->changeA("lieferAdresseID",$id);
			$GRLBM->saveMe();
		}
		return $id;
	}
	
	function saveSingle2($forWhat, $A){
		parent::saveSingle2($forWhat, $A);
		
		if($A->AuftragID != -1 AND $A->type == "lieferAdresse"){
			$_SESSION["messages"]->addMessage("Updating GRLBM $A->AuftragID to use AdresseID $this->ID...");
			$GRLBM = new GRLBM($A->AuftragID);
			$GRLBM->changeA("lieferAdresseID",$this->ID);
			$GRLBM->saveMe();
		}
	}	
}
?>