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
class BPS {
	public static $variable = "BPS";

	public static function getAllProperties($className){
		if(is_object($className))
			$className = get_class($className);
		
		$_SESSION[self::$variable]->setActualClass($className);
		return $_SESSION[self::$variable]->getAllProperties();
	}

	public static function setProperty($className, $propertyName, $propertyValue){
		$_SESSION[self::$variable]->setProperty($className, $propertyName, $propertyValue);
	}

	public static function getProperty($className, $propertyName, $defaultValue = null){
		if(!isset($_SESSION[self::$variable]))
			return null;
		
		return $_SESSION[self::$variable]->getProperty($className, $propertyName, $defaultValue);
	}

	public static function popProperty($className, $propertyName, $defaultValue = null){
		if(!isset($_SESSION[self::$variable]))
			return null;
		
		$v = $_SESSION[self::$variable]->getProperty($className, $propertyName, $defaultValue);
		
		self::unsetProperty($className, $propertyName);
		
		return $v;
	}

	public static function unsetProperty($className, $propertyName){
		$_SESSION[self::$variable]->setActualClass($className);
		$_SESSION[self::$variable]->unsetACProperty($propertyName);
	}
}
?>
