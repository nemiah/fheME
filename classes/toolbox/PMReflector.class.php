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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class PMReflector {

	public static function getAttributesArray($className) {
		
		if(is_object($className)) return PMReflector::getAttributesArrayAnyObject($className);

		if(trim($className) == "")
			throw new Exception("Empty class name");
		
	    /*$a = array();
	    $class = new ReflectionClass("$className");
		$props = $class->getProperties();
		for($i = 0;$i < count($props);$i++)
			$a[] = $props[$i]->getName();
		return $a;*/
		
		return PMReflector::getAttributesArrayAnyObject(new $className);
	}

	public static function implementsInterface($className, $interfaceName){
		if(trim($className) == "")
			return false;
		
		$r = new ReflectionClass($className);
		
		foreach($r->getInterfaces() as $in)
			if(strtolower($in->getName()) == strtolower($interfaceName)) return true;
		  
		return false;
	}
	
	public static function getAttributesArrayAnyObject($O){
		$vars = array();
		if($O == null)
			return $vars;
		
		foreach($O as $key => $value){
			if(is_array($value))
				continue;
			
			$vars[] = $key;
		}
		
		return $vars;
	}
}
?>
