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
class Aspect {

	public static $onetimePointCuts = array();
	public static $pointCuts = array();
	
	private static $sessionVariable = "phynx_Aspects";
	
	public static function joinPoint($mode, $class, $method, $args = null, $defaultValue = null){
		$value = Aspect::findPointCut($mode, $class, $method, $args);

		if($value === null)
			return $defaultValue;

		return $value;
	}

	public static function findPointCut($mode, $class, $method, $args = null){
		if($mode == "around" AND !isset(Aspect::$pointCuts[$mode][$method]))
			throw new AOPNoAdviceException();

		if($mode == "after" AND !isset(self::$pointCuts[$mode][$method]))
			return $args;

		if(isset($_SESSION[self::$sessionVariable]) AND count($_SESSION[self::$sessionVariable]) > 0)
			foreach($_SESSION[self::$sessionVariable] AS $PA)
				self::registerPointCut($PA[0], $PA[1], $PA[2]);
			
		
		if(isset(Aspect::$pointCuts[$mode][$method]) AND count(Aspect::$pointCuts[$mode][$method]) > 0){
			$values = array();
			foreach(Aspect::$pointCuts[$mode][$method] AS $k => $advice) {
				$values[] = Aspect::invokeParser($advice, $class, $args);

				if(isset(Aspect::$onetimePointCuts[$mode."_".$method])){
					unset(Aspect::$onetimePointCuts[$mode."_".$method]);
					unset(Aspect::$pointCuts[$mode][$method][$k]);
				}
			}
			if(count($values) > 1 AND $mode != "after") return $values;
			return $values[0];
		}

		return null;
	}

	public static function invokeParser($method, $class, $args = null){
		$c = explode("::", $method);
		try {
			$method = new ReflectionMethod($c[0], $c[1]);
			return $method->invoke(null, $class, $args);
		} catch (ReflectionException $e){
			return null;
		}
	}

	public static function registerPointCut($mode, $pointCut, $advice, $persistent = false){
		if(!isset(Aspect::$pointCuts[$mode]))
			Aspect::$pointCuts[$mode] = array();

		if(!isset(Aspect::$pointCuts[$mode][$pointCut]))
			Aspect::$pointCuts[$mode][$pointCut] = array();

		if(in_array($advice, Aspect::$pointCuts[$mode][$pointCut])) return;

		Aspect::$pointCuts[$mode][$pointCut][] = $advice;

		if($persistent){
			if(!isset($_SESSION[self::$sessionVariable]))
				$_SESSION[self::$sessionVariable] = array();

			$_SESSION[self::$sessionVariable][$mode.$pointCut.$advice] = array($mode, $pointCut, $advice);
		}
		
		#echo "<pre style=\"font-size:8px;\">";
		#print_r(Aspect::$pointCuts);
		#echo "</pre>";
	}

	public static function registerOnetimePointCut($mode, $pointCut, $advice){
		Aspect::registerPointCut($mode, $pointCut, $advice);
		Aspect::$onetimePointCuts[$mode."_".$pointCut] = true;
	}

	public static function unregisterPointCut($mode, $pointCut){
		if(isset(Aspect::$pointCuts[$mode][$pointCut]))
			unset(Aspect::$pointCuts[$mode][$pointCut]);
	}

	public static function clearPointCuts(){
		Aspect::$pointCuts = array();
	}
}
?>
