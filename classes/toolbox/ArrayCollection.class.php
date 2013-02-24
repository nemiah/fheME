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

class ArrayCollection extends Collection {
	
	protected $counter = 0;
	
	function __construct(){
		$this->storage = "TempFiles";
	}
	
	public function add($element){
		$element->setID($this->counter);
		$this->collector[$this->counter++] = $element;
	}
	
	public function remove($ID){
		unset($this->collector[$ID]);
		
		$this->collector = array_values($this->collector);
		
		foreach($this->collector AS $key => $value)
			$this->collector[$key]->setID($key);
			
		$this->counter = count($this->collector);
	}
	
	public function get($ID){
		return $this->collector[$ID];
	}
	
	public function getCollector(){
		return $this->collector;
	}
}
?>