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
class PMReflector {

	public static function getAttributesArray($className) {
		
		if(is_object($className)) return PMReflector::getAttributesArrayAnyObject($className);

	    /*$a = array();
	    $class = new ReflectionClass("$className");
		$props = $class->getProperties();
		for($i = 0;$i < count($props);$i++)
			$a[] = $props[$i]->getName();
		return $a;*/
		
		return PMReflector::getAttributesArrayAnyObject(new $className);
	}

	public static function implementsInterface($className, $interfaceName){
		$r = new ReflectionClass($className);

		foreach($r->getInterfaces() as $in)
			if(strtolower($in->getName()) == strtolower($interfaceName)) return true;
		  
		return false;
	}
	
	public static function getAttributesArrayAnyObject($O){
		/*$t = array();
		$v = var_export($O, true);
		
		$s = explode("::__set_state(",$v);
		if(count($s) >= 2){
			$s[1]{strlen($s[1])-1} = ";";
			eval("\$c = ".$s[1]."");
			return array_keys($c);
		}
		*/
		#$s = var_export($O, true);
		
		#$s2 = "";
		#$mode = "copy";
		#$subMode = "none";
		
		#$newword = "";
		#$lastword = "";
		
		$vars = array();
		if($O == null) return $vars;
		foreach($O as $key => $value)
			$vars[] = $key;
		
		/*
		for($i = 0; $i < strlen($s); $i++){
			
			if($s[$i] == "'" AND $mode == "copy" AND $s[$i - 1] != "\\") {
				#$s2 .= $s[$i];
				$mode = "noCopy";
				continue;
			}
			if($s[$i] == "'" AND $mode == "noCopy" AND $s[$i - 1] != "\\") {
				#$s2 .= $s[$i];
				$mode = "copy";
				continue;
			}
			if($mode == "noCopy") continue;
			#echo $s[$i];
			#if($mode == "copy")
			#	$s2 .= $s[$i];
			
			if($s[$i] == " ") {
				$newword = $lastword;
				$lastword = "";
			} else $lastword .= $s[$i];
			
			
			if($subMode == "none" AND $s[$i] == "\$" AND ($newword == "private" OR $newword == "public" OR $newword == "protected") AND $mode == "copy") {
				$vars[] = "";
				$subMode = "variable";
				continue;
			}
			
			if($subMode == "variable" AND $s[$i] == " ") {
				$subMode = "none";
			}
			
			if($subMode == "variable" AND $mode == "copy"){
				$vars[count($vars) - 1] .= $s[$i];
			}
			
			
		}*/
		return $vars;
	}
}
?>
