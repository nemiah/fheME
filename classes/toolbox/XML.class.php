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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class XML {
	
	private $collection;
	private $xml;
	private $collectionOf;
	private $object;
	
	private $index;
	private $vals;
	private $returned = false;
	
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
<phynx>
	<collectionOf>".$this->collectionOf."</collectionOf>";
		
		while($t = $this->getNextEntry()){
			$xml .= "
	<entry>
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
		if($this->vals == null) $this->parseXML();
		
		switch($value){
			case "table":
				return array($this->vals[$this->index["collectionOf"][0]]["value"]);
			break;
		}
	}
	
	private function parseXML(){
		$p = xml_parser_create();
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($p, $this->xml, $this->vals, $this->index);
		#if(xml_get_error_code($p)) echo xml_error_string(xml_get_error_code($p))." at line ".xml_get_current_line_number($p);
		xml_parser_free($p);
	}
	
	public function lCV4(){
		if($this->xml == null) return;
		if($this->vals == null) $this->parseXML();
		
		$collector = array();
		$class = $this->getSelectStatement("table");
		$class = $class[0];
		if($class == "") return;
		$c = new $class(-1);
		$A = new Attributes();
		
		foreach($this->vals as $k => $v){
			if($this->vals[$k]["tag"] == "entry" && $this->vals[$k]["type"] == "open") {
				$c = new $class(-1);
				$Att = clone $A;
				continue;
			}
			if($this->vals[$k]["tag"] == "id" && $this->vals[$k]["type"] == "complete") {
				$c = new $class($this->vals[$k]["value"]);
				continue;
			}
			if($this->vals[$k]["tag"] == "attribute" && $this->vals[$k]["type"] == "complete") {
				$n = $this->vals[$k]["attributes"]["name"];
				$Att->$n = isset($this->vals[$k]["value"]) ? $this->vals[$k]["value"] : "";
				continue;
			}
			if($this->vals[$k]["tag"] == "entry" && $this->vals[$k]["type"] == "close") {
				$c->setA($Att);
				$collector[] = $c;
				continue;
			}
		}
		
		return $collector;
	}
}
?>