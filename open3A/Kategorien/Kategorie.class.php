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
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;
class Kategorie extends PersistentObject implements iCloneable, iNewWithValues, iDeletable {
	public function getA(){
		if($this->A == null) $this->loadMe();
		return $this->A;
	}
	
	public function cloneMe(){
		echo $this->newMe();
	}
	
	public function newMe($checkUserdata = true, $output = false){
		if($this->A("isDefault") == "1"){
			$AC = anyC::get("Kategorie", "isDefault", "1");
			$AC->addAssocV3("type", "=", $this->A("type"));
			$AC->addAssocV3("KategorieID", "!=", $this->getID());
			
			if($AC->getNextEntry() != null)
				Red::alertD ("Es wurde bereits ein anderer Eintrag als Standard für diesen Kategorietyp eingetragen.");
		}
		
		if($this->A("type") == "mwst")
			$this->A->name = Util::formatNumber("de_DE",Util::CLNumberParser(str_replace("%","",$this->A("name")), "store"))."%";

		return parent::newMe($checkUserdata, $output);
	}
	
	public function loadMe(){
		if($this->A != null) //or else this method will get called multiple times and the parsing below will go crazy
			return;
		
		parent::loadMe();
		
		if($this->A != null AND $this->A("type") == "mwst")
			$this->A->name = Util::CLNumberParserZ(Util::parseFloat("de_DE",str_replace("%","",$this->A("name"))))."%";
			#Util::CLNumberParser($this->A->name,"load")."%";
	}
	
	public function saveMe($checkUserdata = true, $output = false){
		if($this->A("isDefault") == "1"){
			$AC = anyC::get("Kategorie", "isDefault", "1");
			$AC->addAssocV3("type", "=", $this->A("type"));
			$AC->addAssocV3("KategorieID", "!=", $this->getID());
			$K = $AC->n();
			if($K){
				$K->changeA("isDefault", "0");
				$K->saveMe();
			}
		}
		
		if($this->A("type") == "mwst")
			$this->A->name = Util::formatNumber("de_DE",Util::CLNumberParser(str_replace("%","",$this->A("name")), "store"))."%";
		
		parent::saveMe($checkUserdata, $output);
	}
}
?>
