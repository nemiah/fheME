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
class SpeedCache {
	public static $sessionVariable = "phynx_SpeedCache";
	private static $staticCache = array();
	
	public static function inCache($name){
		return isset($_SESSION[SpeedCache::$sessionVariable][$name]);
	}
	
	public static function getCache($name, $default = null){
		if(isset($_SESSION[SpeedCache::$sessionVariable][$name]))
			return $_SESSION[SpeedCache::$sessionVariable][$name];

		return $default;
	}

	public static function setCache($name, $values){
		if(!isset($_SESSION[SpeedCache::$sessionVariable]))
			$_SESSION[SpeedCache::$sessionVariable] = array();

		$_SESSION[SpeedCache::$sessionVariable][$name] = $values;
	}

	public static function clearCache(){
		$_SESSION[SpeedCache::$sessionVariable] = null;
	}
	
	
	public static function inStaticCache($name){
		return isset(self::$staticCache[$name]);
	}
	
	public static function getStaticCache($name, $default = null, $setIfDefault = false){
		if(isset(self::$staticCache[$name]))
			return self::$staticCache[$name];

		if($setIfDefault)
			self::setStaticCache ($name, $default);
		
		return $default;
	}

	public static function setStaticCache($name, $values){
		self::$staticCache[$name] = $values;
	}

	public static function clearStaticCache(){
		self::$staticCache = array();
	}
}
?>
