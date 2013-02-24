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
class Registry {
	public static $pointers = array();

	public static function setCallback($forPlugin, $methodToCall, $selector = "general"){
		$used = BPS::getProperty("R".$forPlugin."S".$selector, "callbacks", "");

		$methodToCall = str_replace(":", "###DP###", $methodToCall);
		
		if(strpos($used, $methodToCall) !== false) return;

		BPS::setProperty("R".$forPlugin."S".$selector, "callbacks", ($used != "" ? $used."%%" : "").$methodToCall);
	}

	public static function getCallbacks($forPlugin, $selector = "general"){
		if(is_object($forPlugin) AND $forPlugin instanceof PersistentObject)
			$forPlugin = $forPlugin->getClearClass();

		if(is_object($forPlugin) AND !($forPlugin instanceof PersistentObject))
			$forPlugin = get_class($forPlugin);
		
		$used = BPS::getProperty("R".$forPlugin."S".$selector, "callbacks", null);
		$used = str_replace("###DP###", ":", $used);

		if($used == null)
			return null;

		return explode("%%", $used);
	}

	public static function reset($forPlugin){
		self::$pointers[$forPlugin] = 0;
	}
	
	public static function callNext($forPlugin, $selector = "general", $parameters = array()){
		if(!isset(self::$pointers[$forPlugin]))
			self::$pointers[$forPlugin] = 0;

		$callBacks = self::getCallbacks($forPlugin, $selector);
		if(!isset($callBacks[self::$pointers[$forPlugin]]))
			return null;

		$method = explode("::", $callBacks[self::$pointers[$forPlugin]]);

		self::$pointers[$forPlugin]++;
		return Util::invokeStaticMethod($method[0], $method[1], $parameters);
	}
}
?>