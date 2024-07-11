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
class Kategorien extends anyC {
	function __construct() {
		$this->setCollectionOf("Kategorie");
	}

	public function addKategorie($name,$typeName){
		if(!isset($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"]))
			$_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"] = array();
			
		$_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"][$name] = $typeName;
	}

	public static function getDefault($type, $defaultValue = null) {
		$AC = anyC::get("Kategorie", "type", $type);
		$AC->addAssocV3("isDefault", "=", "1");
		$AC->setLimitV3("1");
		$M = $AC->getNextEntry();
		
		if($M == null) return $defaultValue;
		return $M->A("name");
	}
	
	public function getArrayWithKeys(){
		if($this->A == null) $this->lCV3();
		
		$keys = array();
		
		for($i=0;$i < count($this->collector);$i++){
			$keys[] = $this->collector[$i]->getID();
		}
		
		return $keys;
	}
	
	public function getArrayWithValues($zeroEntry = null){
		if($this->A == null) $this->lCV3();
		$values = array();
		if($zeroEntry != null)
			$values[0] = T::_($zeroEntry);
		
		for($i=0;$i < count($this->collector);$i++){
			$A = $this->collector[$i]->getA();
			$values[] = $A->name;
		}
		return $values;
	}
	
	public function getArrayWithKeysAndValues($zeroName = null){
		$kv = array();
		$kv[0] = "-";
		if($zeroName != null)
			$kv[0] = $zeroName;
		
		if($this->A == null) $this->lCV3();
		
		for($i=0;$i < count($this->collector);$i++){
			$A = $this->collector[$i]->getA();
			$kv[$this->collector[$i]->getID()] = $A->name;
		}
		
		return $kv;
	}
}
?>
