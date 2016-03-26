<?php

class ClientPhimAuthenticator implements Thruway\Authentication\ClientAuthenticationInterface {
	private $authID;
	private $key;
	private $realm;

	function __construct($realm, $authID, $key){
		$this->authID = $authID;
		$this->key = $key;
		$this->realm = $realm;
	}

	public function getAuthId() {
		return $this->authID;
	}

	public function setAuthId($authid) {

	}

	public function getAuthMethods() {
		return ["phimAuth_".$this->realm];
	}

	public function getAuthenticateFromChallenge(Thruway\Message\ChallengeMessage $msg)	{
		return new \Thruway\Message\AuthenticateMessage($this->key);
	}
}
?>