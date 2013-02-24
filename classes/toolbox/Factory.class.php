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
class Factory {
	private $className;
	private $object;
	private $attributes;
	private $newParameter;
	private $lastNewID;
	private $setAttributes = array();
	private $onExistsOperator = array();

	public function getLastCreatedID(){
		return $this->lastNewID;
	}

	public function getUsedClassName(){
		return $this->className;
	}

	public function __construct($className, $newParameter = -1){
		$this->className = $className;
		$this->object = new $className($newParameter);
		$this->newParameter = $newParameter;

		if(!$this->object instanceof PersistentObject)
			throw new FactoryException("$className is no instance of PersistentObject!");
	}

	public function fill(array $contents){
		$this->loadAttributes();
		#$this->object->loadMeOrEmpty();
		$allowed = PMReflector::getAttributesArrayAnyObject($this->attributes);

		foreach($allowed AS $v)
			if(isset($contents[$v]))
				$this->sA($v, $contents[$v]);
	}

	public function gO(){
		return $this->object;
	}
	
	private function loadAttributes(){
		if($this->attributes != null) return;
		
		if($this->newParameter == -1) $this->attributes = $this->object->newAttributes();
		else $this->attributes = $this->object->getA();
	}

	public function sA($attributeName, $attributeValue, $onExistsOperator = null){
		if($this->attributes == null) $this->loadAttributes();

		if($this->newParameter == -1) $this->attributes->$attributeName = $attributeValue;
		else $this->object->changeA($attributeName, $attributeValue);

		if($onExistsOperator != null) $this->onExistsOperator[$attributeName] = $onExistsOperator;

		$this->setAttributes[] = $attributeName;
	}

	public function store($checkUserData = true, $output = false){
		if($this->newParameter == -1) {
			$this->object->setA($this->attributes);
			return $this->lastNewID = $this->object->newMe($checkUserData, $output);
		} else {
			$this->object->saveMe($checkUserData, $output);
			return $this->newParameter;
		}
	}

	public function verify($resetParsers = false){
		if($this->lastNewID == null)
			throw new FactoryException("No new entry to verify");

		$O = new $this->className($this->lastNewID);
		if($resetParsers)
			$O->resetParsers();
		
		foreach($this->setAttributes AS $k){
			if($k == $this->className."ID") continue;

			if($O->A($k) != $this->attributes->$k)
				throw new FactoryException("Verify failed! $k: '".$this->attributes->$k."' != '".$O->A($k)."'");
		}

		return true;
	}

	public function exists($returnElement = false){
		$AC = new anyC();
		$AC->setCollectionOf($this->className);

		foreach($this->setAttributes AS $k){
			if($k == $this->className."ID") continue;

			$AC->addAssocV3($k, (isset($this->onExistsOperator[$k]) ? $this->onExistsOperator[$k] : "="), $this->attributes->$k);
		}

		$AC->lCV3();


		if($AC->numLoaded() == 0) return false;
		else {
			if($returnElement) return $AC->getNextEntry();
			return $AC->getNextEntry()->getID();
		}
	}
/*
	public function update($id){
		$this->newParameter = $id;

		$this->store(true, false);
	}*/
}
?>