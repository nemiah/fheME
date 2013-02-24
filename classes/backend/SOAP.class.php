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

class SOAP {
	private $client = null;
	private $key;
	
	public function startServer($className){
		$server = new SoapServer(NULL, array('uri' => "http://".$_SERVER["HTTP_HOST"]."/"));
		$server->setClass($className);
		$server->handle();
	}

	public function authKey($key){
		$this->key = $key;
	}
	
	public function startClient($serverURL){
		$options = array(
		"location" => $serverURL,
		"uri" => "urn:phynxSOAP",
		"style" => SOAP_RPC,
		"use" => SOAP_ENCODED);
		
		if($this->key != null){
			$options["login"] = "SOAPUser";
			$options["password"] = $this->key;
		}
		$this->client = new SoapClient(NULL, $options);
		
		return $this->client;
	}

	function  __call($name,  $arguments) {
		$parameters = array();
		foreach($arguments as $K => $V)
			$parameters[] = new SoapParam($V, "par$K");

		$result = $this->client->__call(
		$name,
		$parameters,
		array(
			"uri" => "urn:phynxSOAP",
			"soapaction" => "urn:phynxSOAP#$name"));

		return $result;
	}
}
?>
