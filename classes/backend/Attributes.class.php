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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class Attributes {
	function __construct(){
	}
	
	/**
	 * Creates a new instance of this object with the supplied fields and values as attributes.
	 * 
	 * @return Attributes New Instance with supplied fields and values
	 * 
	 * @param object $fields Names of the attributes
	 * @param object $values Values of the attributes
	 */
	function newWithValues($fields,$values){
	    $n = get_class($this);
	    $nc = new $n();
		for($i = 0;$i < count($fields);$i++)
			if(isset($values[$fields[$i]])) $nc->$fields[$i] = $values[$fields[$i]];
			
		return $nc;
	}
	
	/**
	 * Creates a new instance of this object with attributevalues set to supplied values.
	 * 
	 * @return Attributes New instance with supplied values
	 * 
	 * @param array $values The new values
	 */
	function newWithAssociativeArray($values){
	    $a = PMReflector::getAttributesArray(get_class($this));
	    
		for($i = 0;$i < count($a);$i++)
			if(isset($values[$a[$i]])) $this->$a[$i] = $values[$a[$i]];
	}
	
	/**
	 * See newWithAssociativeArray.
	 * 
	 * @return Attributes See newWithAssociativeArray
	 * 
	 * @param array $values See newWithAssociativeArray
	 */
	function fillWithAssociativeArray($values){
	    $this->newWithAssociativeArray($values);
	}
	
	function A($fieldName){
		if(!isset($this->$fieldName))
			return null;
		
		return $this->$fieldName;
	}
}
?>
