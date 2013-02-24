<?php
/**
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

class DynamicJSGUI extends UnpersistentClass {
	private static $aspects = array();

	public function  __construct() {
		$this->customize();
		
		while($C = Registry::callNext("DynamicJS"))
			;
	}

	public static function Class_registerNew($className, $classBody){
		if(!isset(self::$aspects[$className]))
			self::$aspects[$className] = "var $className = ".$classBody."";
	}
	
	public static function Attribute_registerNew($className, $attributeName, $attributeDefault = null){
		$default = "null";
		if(is_bool($attributeDefault)) $default = ($attributeDefault == true ? "true" : "false");
		else $default = "\"$attributeDefault\"";

		self::$aspects[] = "var $className = { $attributeName: $default };";
	}

	public static function Aspect_registerOnLoadFrame($targetFrame, $plugin, $isNewEntry, $advice){
		$newAspect = "Aspect.registerOnLoadFrame('$targetFrame', '$plugin', ".($isNewEntry ? "true" : "false").", $advice);";
		if(!in_array($newAspect, self::$aspects)) self::$aspects[] = $newAspect;
	}

	public static function Aspect_registerOnRmePCR($targetClass, $targetMethod, $advice){
		$newAspect = "Aspect.registerOnRmePCR('$targetClass', '$targetMethod', $advice);";
		if(!in_array($newAspect, self::$aspects)) self::$aspects[] = $newAspect;
	}
	
	public static function output(){
		echo "/*
 *
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
 */\n\n";

		foreach(self::$aspects AS $v)
			echo $v."\n";
	}
}
?>