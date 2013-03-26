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
class UPnPCommand {
	private $Device;
	function __construct(UPnP $UPnP) {
		$this->Device = $UPnP;
	}
	
	private function execute($method, $prmArguments, $type){
		$url = parse_url($this->Device->A("UPnPLocation"));
		$controlURL = $url["scheme"]."://".$url["host"].":".$url["port"].$this->Device->A("UPnP{$type}controlURL");
		
		#$prmArguments = $vars[0];
		#$prmService = $vars[1];
		#$controlURL = $vars[2];
		$client = new SoapClient(null, array(
			'soap_version' => SOAP_1_1,
			'location' => $controlURL,
			'uri' => "urn:schemas-upnp-org:service:$type:1",
			"trace" => true));


		#try {
			$result = $client->__soapCall($method, array(
				new SoapVar($prmArguments, XSD_ANYXML)
			));
			#print_r($result);
		#} catch(Exception $e){
		#	echo "<pre>";
		#	print_r($client->__getLastRequestHeaders());
		#	UPnP::prettyfy($client->__getLastRequest());
		#	echo "\n";
		#	UPnP::prettyfy($client->__getLastResponse());
		#	echo "</pre>";
		#}
		
		return $result;
	}
	
	function Browse($ObjectID, $BrowseFlag, $Filter = "*") {
		$args = '<ObjectID>'.$ObjectID.'</ObjectID>' . "\r\n";
		$args .= '<BrowseFlag>'.$BrowseFlag.'</BrowseFlag>' . "\r\n";
		$args .= '<Filter>'.$Filter.'</Filter>' . "\r\n";
		$args .= '<StartingIndex>0</StartingIndex>' . "\r\n";
		$args .= '<RequestedCount>0</RequestedCount>' . "\r\n";
		$args .= '<SortCriteria>'.'</SortCriteria>' . "\r\n";
		return $this->execute(__FUNCTION__, $args, "ContentDirectory");
	}
	
	function GetMute($InstanceID, $Channel){
		$args = '<InstanceID>'.$InstanceID.'</InstanceID>' . "\r\n";
		$args .= '<Channel>'.$Channel.'</Channel>' . "\r\n";
		try {
			$return = $this->execute(__FUNCTION__, $args, "RenderingControl");
		} catch(Exception $e){
			$return = 0;
		}
		
		return $return;
	}
	
	function SetMute($InstanceID, $Channel, $DesiredMute){
		$args = '<InstanceID>'.$InstanceID.'</InstanceID>' . "\r\n";
		$args .= '<Channel>'.$Channel.'</Channel>' . "\r\n";
		$args .= '<DesiredMute>'.$DesiredMute.'</DesiredMute>' . "\r\n";
		return $this->execute(__FUNCTION__, $args, "RenderingControl");
	}
	
	function Next() {
		$args = '<InstanceID>0</InstanceID>' . "\r\n";
		return $this->execute(__FUNCTION__, $args, "AVTransport");
	}

	function Pause() {
		$args = '<InstanceID>0</InstanceID>' . "\r\n";
		return $this->execute(__FUNCTION__, $args, "AVTransport");
	}

	function Play($InstanceID = 0, $prmSpeed = 1) {
		$args = '<InstanceID>'.$InstanceID.'</InstanceID>' . "\r\n";
		$args .= '<Speed>'.$prmSpeed.'</Speed>' . "\r\n";
		return $this->execute(__FUNCTION__, $args, "AVTransport");
	}

	function Stop($InstanceID = 0) {
		$args = '<InstanceID>'.$InstanceID.'</InstanceID>'."\r\n";
		return $this->execute(__FUNCTION__, $args, "AVTransport");
	}
	
	function SetAVTransportURI($InstanceID = 0, $CurrentURI = ""){
		$args = '<InstanceID>'.$InstanceID.'</InstanceID>' . "\r\n";
		$args .= '<CurrentURI>' . $CurrentURI . '</CurrentURI>' . "\r\n";
		$args .= '<CurrentURIMetaData>'.'</CurrentURIMetaData>' . "\r\n";
		return $this->execute(__FUNCTION__, $args, "AVTransport");
	}
}

?>
