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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class UPnP extends PersistentObject {

	function __call($name, $arguments) {
		$C = new UPnPCommand($this);
		
		$vars = $C->$name();
		print_r($vars);
		$prmArguments = $vars[0];
		$prmService = $vars[1];
		$controlURL = $vars[2];
		$client = new SoapClient(null, array(
			'soap_version' => SOAP_1_1,
			'location' => $controlURL,
			'uri' => "urn:schemas-upnp-org:service:$prmService:1",
			"trace" => true));


		try {
			$result = $client->__soapCall($name, array(
				new SoapVar($prmArguments, XSD_ANYXML)
			));
			print_r($result);
		} catch(Exception $e){
			echo "<pre>";
			print_r($client->__getLastRequestHeaders());
			$this->prettyfy($client->__getLastRequest());
			echo "\n";
			$this->prettyfy($client->__getLastResponse());
			echo "</pre>";
		}
	}
	
	function prettyfy($response){
		$xml = new SimpleXMLElement($response);

		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());
		
		echo htmlentities($dom->saveXML());
	}
}
?>