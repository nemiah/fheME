<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class UPnP extends PersistentObject {

	function __call($name, $args) {
		$C = new UPnPCommand($this);
		$arguments = func_get_args();
		unset($arguments[0]);
		
		if(!is_array($arguments))
			$arguments = array($arguments);
		
		if(is_array($args))
			$arguments = $args;
		#die();
		$R = new ReflectionMethod($C, $name);
		$result = $R->invokeArgs($C, $arguments);
		
		
		return $result;
	}
	
	static function prettyfy($response){
		$xml = new SimpleXMLElement($response);

		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());
		
		echo htmlentities($dom->saveXML());
	}
	
	public function VendorShutdown(){
		$url = parse_url($this->A("UPnPLocation"));
		if(strpos($this->A("UPnPModelName"), "XBMC") !== false)
			echo Util::PostToHost($url["host"], 8080, "/jsonrpc?System.Shutdown", "", '{"jsonrpc":"2.0","method":"System.Shutdown","id":1}', null, null, "application/json");
	}
}
?>