<?php
namespace nemiah\phpSilence;

class api {
	private $email;
	private $password;
	private $apiKey = "AIzaSyAVnxe4u3oKETFWGiWcSb-43IsBunDDSVI";
	
	private $status = ["IDLE", "MovingNoKey!", "City", "Eco", "Sport", "BatteryOut!", "Charge"];
	
	function __construct($email, $password){
		$this->email = $email;
		$this->password = $password;
	}
	
	function getData(){
		$tokenQuery = new \stdClass();
		$tokenQuery->email = $this->email;
		$tokenQuery->returnSecureToken = true;
		$tokenQuery->password = $this->password;

		$ch = curl_init("https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=".$this->apiKey);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tokenQuery));
		curl_setopt($ch, CURLOPT_ENCODING , "");
		$headers = [
			'host: www.googleapis.com',
			'content-type: application/json',
			'accept: */*',
			'x-ios-bundle-identifier: eco.silence.my',
			'connection: keep-alive',
			'x-client-version: iOS/FirebaseSDK/8.8.0/FirebaseCore-iOS',
			'user-agent: FirebaseAuth.iOS/8.8.0 eco.silence.my/1.2.1 iPhone/15.6.1 hw/iPhone9_3',
			'accept-encoding: gzip, deflate, br'
		];

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$login = json_decode(curl_exec($ch));

		curl_close($ch);


		$ch = curl_init("https://api.connectivity.silence.eco/api/v1/me/scooters?details=true&dynamic=true&pollIfNecessary=true");

		curl_setopt($ch, CURLOPT_ENCODING , "");
		$headers = [
			'host: api.connectivity.silence.eco:443',
			'connection: keep-alive',
			'accept: */*',
			'user-agent: Silence/220 CFNetwork/1220.1 Darwin/20.3.0',
			'authorization: Bearer '.$login->idToken,
			'accept-encoding: gzip, deflate, br'
		];

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$scooters = json_decode(curl_exec($ch));
		curl_close($ch);
		
		foreach($scooters AS $scooter)
			$scooter->statusParsed = $this->status[$scooter->status];
		
		
		return $scooters;
	}
}

