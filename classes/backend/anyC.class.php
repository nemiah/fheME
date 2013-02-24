<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class anyC extends Collection {
	
	/**
	 * Creates a new instance of this class.
	 * 
	 * @param object $cOf[optional] Sets the type of collection
	 */
	function __construct(/*$cOf = ""*/){
		$collectionOf = str_replace("GUI","",get_class($this));
		$collectionOf[0] = " ";
		$this->collectionOf = trim($collectionOf);
		#}
	}

	/**
	 * @param string $collectionOf
	 * @param string $field
	 * @param string $value
	 * @return anyC
	 */
	public static function get($collectionOf, $field = "", $value = ""){
		$AC = new anyC();
		$AC->setCollectionOf($collectionOf);
		if($field != "")
			$AC->addAssocV3($field, "=", $value);

		return $AC;
	}

	/**
	 * @param string $collectionOf
	 * @param string $field
	 * @param string $value
	 * @return PersistentObject
	 */
	public static function getFirst($collectionOf, $field = "", $value = ""){
		$AC = self::get($collectionOf, $field, $value);
		$AC->setLimitV3("1");

		return $AC->getNextEntry();
	}

	/**
	 * Changes type of the collection
	 * 
	 * @param object $Co Type of collection
	 */
	function setCollectionOf($Co){
		$this->collectionOf = $Co;
	}
	
	##TODO: Add comment
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 * @param object $returnCollector[optional]
	 */
	public function lCV3($id = -1, $returnCollector = true){
		if($this->Adapter == null) $this->loadAdapter();

		$gT = $this->Adapter->getSelectStatement("table");
		if(count($gT) == 0) $this->Adapter->setSelectStatement("table",$this->collectionOf);
		
		if($id != -1)
			$this->setAssocV3((count($gT) == 0 ? $this->collectionOf : $gT[0])."ID","=",$id);

		if($returnCollector) $this->collector = $this->Adapter->lCV4();
		else return $this->Adapter->lCV4();
	}
	
	public function toArray($valueFieldName){
		$r = array();
		
		while($E = $this->getNextEntry())
			$r[$E->getID()] = $E->A($valueFieldName);
		
		return $r;
	}
}
?>