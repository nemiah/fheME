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
class XML {
	
	private $collection;
	private $xml;
	private $collectionOf;
	private $object;
	
	private $index;
	private $vals;
	private $returned = false;
	private $parsed;
	
	public function setCollection(Collection $C){
		$this->collection = $C;
		$this->collectionOf = $C->getCollectionOf();
	}
	
	public function setObject(PersistentObject $PC){
		$this->collectionOf = $PC->getClearClass();
		$this->object = $PC;
	}
	
	public function setXML($xml){
		$this->xml = $xml;
	}
	
	private function getNextEntry(){
		if($this->collection != null) return $this->collection->getNextEntry();
		if($this->object != null AND !$this->returned) {
			$this->returned = true;
			return $this->object;
		}
		return false;
	}
	
	private function makeXML(){
		if($this->collection == null AND $this->object == null) return;
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>'."
<phynx>";
		
		while($t = $this->getNextEntry()){
			$xml .= "
	<entry class=\"".$this->collectionOf."\">
		<id>".$t->getID()."</id>";
			
			$A = $t->getA();
			#$fields = PMReflector::getAttributesArrayAnyObject($A); //no longer required
			
			foreach($A as $k => $v)
				$xml .= "
		<attribute name=\"$k\"><![CDATA[$v]]></attribute>";
			
			$xml .= "
		
	</entry>";
			
		}
		$xml .= "
</phynx>";
		return $xml;
	}
	
	public function getXML(){
		return $this->makeXML();
	}
	
	public function setXMLHeader(){
		header('Content-type: application/xml; charset="utf-8"',true);
	}
	
	public function getSelectStatement($value){
		
	}
	
	private function parseXML(){
		$this->parsed = new SimpleXMLElement($this->xml);
	}
	
	public function lCV4(){
		if($this->xml == null) return;
		if($this->parsed == null) $this->parseXML();
		
		$class = null;
		$collector = array();
		if(isset($this->parsed->collectionOf))
			$class = $this->parsed->collectionOf."";
		
		foreach($this->parsed->entry AS $entry){
			$ES = $entry->attributes();
			if(isset($ES->class))
				$class = $ES->class."";
			
			$c = new $class(-1);
			$c->loadMeOrEmpty();
			
			foreach($entry->attribute AS $attribute){
				$AS = $attribute->attributes();
				$c->changeA($AS->name."", $attribute."");
			}
			
			$collector[] = $c;
		}
		
		return $collector;
	}
}
?>