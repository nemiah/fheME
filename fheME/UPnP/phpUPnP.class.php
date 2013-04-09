<?php

/**
 * Communicate with UPnP devices.
 *
 * Not static for being able to have instances for different devices.
 *
 * @author Morten Hekkvang <artheus@github>
 *
 * @todo Create config file.
 * @todo Better commenting.
 * @todo Add security checks for eg. arguments.
 * @todo Response parsing before return in SOAP-method calls.
 */
class phpUPnP {

	const USER_AGENT = 'MacOSX/10.8.2 UPnP/1.1 PHPUPnP/0.0.1a';
	const UPNP_SERVICE_RENDERCONTROL = 'RenderingControl:1';

	private $curlHandle = null;
	private $defaultURL = null;

	/**
	 * Perform an M-SEARCH multicast request for detecting UPnP-devices in network.
	 *
	 * @todo Allow unicasting.
	 * @todo Sort arguments better.
	 */
	public function mSearch($st = 'ssdp:all', $deviceIp = '239.255.255.250', $mx = 1, $man = 'ssdp:discover', $from = null, $port = null, $sockTimout = '2') {
		$hostIp = '239.255.255.250';

		if ($deviceIp != $hostIp) {
			$hostIp = gethostbyname(trim('mylocalip'));
		}

		// BUILD MESSAGE
		$msg = 'M-SEARCH * HTTP/1.1' . "\r\n";
		$msg .= 'HOST: ' . $hostIp . ':1900' . "\r\n";
		$msg .= 'MAN: "' . $man . '"' . "\r\n";
		$msg .= 'MX: ' . $mx . "\r\n";
		$msg .= 'ST: ' . $st . "\r\n";
		$msg .= 'USER-AGENT: ' . static::USER_AGENT . "\r\n";
		$msg .= '' . "\r\n";

		// MULTICAST MESSAGE
		$sock = socket_create(AF_INET, SOCK_DGRAM, getprotobyname('udp'));
		$opt_ret = socket_set_option($sock, 1, 6, TRUE);
		$send_ret = socket_sendto($sock, $msg, strlen($msg), 0, $deviceIp, 1900);

		// SET TIMEOUT FOR RECIEVE
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $sockTimout, 'usec' => '0'));
		echo "<pre style=\"max-height:500px;overflow:auto;\">";
		// RECIEVE RESPONSE
		$response = array();
		do {
			unset($buf);
			@socket_recv($sock, $buf, 1024, MSG_WAITALL); //, $from, $port );
			if (!is_null($buf)){
				print_r($buf);
				$response[] = $this->parseHeaders($buf);
			}
		} while (!is_null($buf));
echo "</pre>";
		// CLOSE SOCKET
		socket_close($sock);

		return $response;
	}
	
	public function mServer(){
		$sock = socket_create(AF_INET, SOCK_DGRAM, getprotobyname('udp'));
		$mIP = '239.255.255.250';
		if (!socket_bind($sock, $mIP, 1900)) {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			
			die("Could not bind socket : [$errorcode] $errormsg \n");
		}
		
		socket_set_option($sock, IPPROTO_IP, MCAST_JOIN_GROUP, array("group" => '239.255.255.250', "interface" => 0));

		while (1) {
			echo "Waiting for data ... \n";
			socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
			echo "$remote_ip : $remote_port -- " . $buf;
			
			$query = $this->parseHeaders($buf);
			if(!isset($query["m-search"]))
				continue;
			
			$response = "HTTP/1.1 200 OK\r\n";
			$response .= "CACHE-CONTROL: max-age=1810\r\n";
			$response .= "DATE: ".date("r")."\r\n";
			$response .= "EXT:\r\n";
			$response .= "LOCATION: http://192.168.7.123:9000/TMSDeviceDescription.xml\r\n";
			$response .= "SERVER: Linux/3.x, UPnP/1.1, fheME/0.6\r\n";
			$response .= "ST: urn:fheme.de:service:X_FIT_HomeAutomation:1\r\n";
			$response .= "USN: uuid:f6da16ab-0d1b-fe1c-abca-82aacf4afcac::urn:fheme.de:service:X_FIT_HomeAutomation:1\r\n";
			$response .= "Content-Length: 0\r\n";
			$response .= "\r\n";
			
			//Send back the data to the client
			socket_sendto($sock, $response, strlen($response), 0, $mIP, $remote_port);
		}

		socket_close($sock);
	}

	private function parseHeaders($response) {
		$responseArr = explode("\r\n", $response);

		$parsedResponse = array();

		foreach ($responseArr as $row) {
			if (stripos($row, "m-search") === 0)
				$parsedResponse['m-search'] = $row;
					
			if (stripos($row, 'http') === 0)
				$parsedResponse['http'] = $row;

			if (stripos($row, 'cach') === 0)
				$parsedResponse['cache-control'] = str_ireplace('cache-control: ', '', $row);

			if (stripos($row, 'date') === 0)
				$parsedResponse['date'] = str_ireplace('date: ', '', $row);

			if (stripos($row, 'ext') === 0)
				$parsedResponse['ext'] = str_ireplace('ext:', '', $row);

			if (stripos($row, 'loca') === 0)
				$parsedResponse['location'] = str_ireplace('location: ', '', $row);

			if (stripos($row, 'serv') === 0)
				$parsedResponse['server'] = str_ireplace('server: ', '', $row);

			if (stripos($row, 'st:') === 0)
				$parsedResponse['st'] = str_ireplace('st: ', '', $row);

			if (stripos($row, 'usn:') === 0)
				$parsedResponse['usn'] = str_ireplace('usn: ', '', $row);

			if (stripos($row, 'cont') === 0)
				$parsedResponse['content-length'] = str_ireplace('content-length: ', '', $row);
		}

		return $parsedResponse;
	}

	/**
	 * Get the curl handle for performing soap requests.
	 */
	public function getCurlHandle() {
		if (is_null($this->curlHandle)) {
			$this->curlHandle = curl_init();
		}

		return $this->curlHandle;
	}

	public function setVolume($desiredVolume, $url = null, $channel = 'Master', $instanceId = 0) {
		return $this->sendRequestToDevice('SetVolume', array(
					'InstanceID' => $instanceId,
					'Channel' => $channel,
					'DesiredVolume' => $desiredVolume,
						), static::UPNP_SERVICE_RENDERCONTROL, $url);
	}

	public function setMute($desiredMute, $url = null, $channel = 'Master', $instanceId = 0) {
		if (is_bool($desiredMute))
			$desiredMute = $desiredMute ? 1 : 0;

		return $this->sendRequestToDevice('SetMute', array(
					'InstanceID' => $instanceId,
					'Channel' => $channel,
					'DesiredMute' => $desiredMute,
						), static::UPNP_SERVICE_RENDERCONTROL, $url);
	}

	public function sendRequestToDevice($method, $arguments, $service, $url = null, $hostIp = null, $hostPort = '80') {
		if (is_null($url))
			$url = $this->getDefaultURL();

		if (is_null($hostIp)) {
			$hostIp = gethostbyname(trim('mylocalip'));
		}

		$body = '<?xml version="1.0" encoding="utf-8"?>';
		$body .='<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">';
		$body .='<s:Body>';
		$body .='<u:' . $method . ' xmlns:u="urn:schemas-upnp-org:service:' . $service . '">';

		foreach ($arguments as $arg => $value) {
			//if( is_string($value) ) $value = '"'.$value.'"';
			$body .='<' . $arg . '>' . $value . '</' . $arg . '>';
		}

		$body .='</u:' . $method . '>';
		$body .='</s:Body>';
		$body .='</s:Envelope>';

		$body = utf8_encode($body);

		$header = array(
			'HOST: ' . $hostIp . ':' . $hostPort,
			'CONTENT-LENGTH: ' . strlen($body),
			'CONTENT-TYPE: text/xml; charset="utf-8"',
			'USER-AGENT: ' . static::USER_AGENT,
			'SOAPACTION: "urn:schemas-upnp-org:service:' . $service . '#' . $method . '"',
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	public function getDefaultURL() {
		if (is_null($this->defaultURL))
			throw new Exception('You must set a Default URL.');
	}

	public function setDefaultURL($url) {
		$this->defaultURL = $url;
	}

}

?>