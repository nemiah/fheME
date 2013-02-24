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
class Users extends anyC {
	function __construct(){
		$this->setCollectionOf("User");

		$this->customize();
	}

	public static function getUsers(){
		if(mUserdata::getGlobalSettingValue("AppServer", "") != ""){
			$U = new Users();
			$U->getAppServerUsers();
		} else {
			$U = new Users();
			$U->addAssocV3("isAdmin", "=", "0");
		}

		return $U;
	}

	public static function login($username, $password, $application, $language = "default"){
		$U = new Users();
		return $U->doLogin(array("loginUsername" => $username, "loginSHAPassword" => $password, "anwendung" => $application, "loginSprache" => $language)) > 0;
	}
	
	public function getUser($username, $password, $isSHA = false){
		if($password == ";;;-1;;;") return null;
		
		$user = $this->getAppServerUser($username, !$isSHA ? sha1($password) : $password);
		if($user != null) return $user;

		$this->addAssocV3("username","=",$username);
		if(!$isSHA) $this->addAssocV3("SHApassword","=",sha1($password),"AND","1");
		else $this->addAssocV3("SHApassword","=",$password,"AND","1");
		$this->addAssocV3("password","=",$password,"OR","1");

		$user = $this->getNextEntry();
		if($user != null) return $user;

		try {
			if(isset($_SESSION["viaInterface"]) AND $_SESSION["viaInterface"] and !class_exists("mphynxAltLogin"))
				throw new Exception();
			
			$AL = new mphynxAltLogin();
			$AL->addAssocV3("username","=",$username);
			if(!$isSHA) $AL->addAssocV3("SHApassword","=",sha1($password),"AND","1");
			else $AL->addAssocV3("SHApassword","=",$password,"AND","1");

			$user = $AL->getNextEntry();
			if($user != null) return $user;
		} catch (Exception $e){
			return null;
		}

		return null;
	}

	private function getAppServerUsers(){
		$S = Util::getAppServerClient();
		
		if(Session::currentUser() == null)
			throw new Exception("No user logged in!");
		try {
			$collection = array();
			
			$Users = $S->getUsers(Session::currentUser()->getA());
			foreach($Users AS $UL){
				$U = new User($UL->UserID);
				$U->setA($UL);
				$collection[] = $U;
			}
			
		} catch(Exception $e){}

		$this->addAssocV3("isAdmin", "=", "0");
		$this->lCV3();

		$this->collector = array_merge($this->collector, $collection);
	}

	private function getAppServerUser($username, $password){
		try {
			$S = Util::getAppServerClient(false);
			if($S != null){

				$user = $S->getUser($username, $password);

				if($user != null) {
					$U = new User($user->UserID);
					$U->setA($user);
					return $U;
				}
			}
		} catch (Exception $e){}

		return null;
	}
	
	protected function doCertificateLogin($application, $sprache, $cert){
		if(!CertTest::isCertSigner($cert, CertTest::$FITCertificate))
			return 0;
		
		$x509cert = openssl_x509_read($cert);
		$data = openssl_x509_parse($x509cert);
		
		if($data["validFrom_time_t"] > time())
			Red::errorD("Zertifikat noch nicht gültig");
		
		if($data["validTo_time_t"] < time())
			Red::errorD("Zertifikat nicht mehr gültig");
		
		$Users = self::getUsers();
		$foundU = null;
		while($U = $Users->getNextEntry())
			if(trim($U->A("name")) === trim($data["subject"]["CN"]) AND strtolower(trim($U->A("UserEmail"))) === strtolower(trim($data["subject"]["emailAddress"]))) {
				$foundU = $U;
				break;
			}
		
		if($foundU == null)
			return 0;
		
		return $this->doLogin(array("loginUsername" => $foundU->A("username"), "loginSHAPassword" => $foundU->A("SHApassword"), "anwendung" => $application, "loginSprache" => $sprache));
	}
	
	protected function doPersonaLogin($application, $sprache, $assertion){
		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL, "https://verifier.login.persona.org/verify");
		curl_setopt($ch,CURLOPT_POST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, "assertion=$assertion&audience=".$_SERVER["HTTP_HOST"]);

		$result = json_decode(curl_exec($ch));
		
		curl_close($ch);
		
		if($result->status != "okay")
			return 0;
		
		try {
			$Users = self::getUsers();
			$foundU = null;
			while($U = $Users->getNextEntry())
				if(strtolower(trim($U->A("UserEmail"))) === strtolower(trim($result->email))) {
					$foundU = $U;
					break;
				}

			if($foundU == null)
				return 0;
		} catch (Exception $e){
			return 2;
		}
		
		if(Session::currentUser() != null AND Session::currentUser()->A("UserEmail") == strtolower(trim($result->email)))
			return 2;
		
		return $this->doLogin(array("loginUsername" => $foundU->A("username"), "loginSHAPassword" => $foundU->A("SHApassword"), "anwendung" => $application, "loginSprache" => $sprache));
	}
	
	public function doLogin($ps){
		$validUntil = Environment::getS("validUntil", null);
		if($validUntil != null and $validUntil < time())
			Red::errorD("Diese Version ist abgelaufen. Bitte wenden Sie sich an den Support.");
		
		if(!is_array($ps)) parse_str($ps, $p);
		else $p = $ps;
		#if($p["loginPassword"] == ";;;-1;;;") return 0;

		$this->doLogout();

		$_SESSION["DBData"] = $_SESSION["S"]->getDBData();

		try {
			$U = $this->getUser($p["loginUsername"], $p["loginSHAPassword"], true);
			if($U === null) return 0;

			if(get_class($U) == "phynxAltLogin") $p["anwendung"] = $U->A("UserApplication");

			if($U->A("allowedApplications") != null AND is_array($U->A("allowedApplications")) AND !in_array($p["anwendung"], $U->A("allowedApplications")))
				return 0;

			$UA = $U->getA();
			
		} catch (Exception $e){
			if($p["loginUsername"] == "Admin" AND $p["loginSHAPassword"] == "4e7afebcfbae000b22c7c85e5560f89a2a0280b4"){#"Admin"){
				$tu = new User(-1);
				$UA = $tu->newAttributes();
				$UA->name = "Installations-Benutzer";
				$UA->username = "Admin";
				$UA->password = "Admin";
				if($p["loginSprache"] != "default") $UA->language = $p["loginSprache"];
				$UA->isAdmin = 1;
				$U = new User(-1);
				$U->setA($UA);
			} else {
				return -2;
			}
		}
		if($p["loginSprache"] != "default") $U->changeA("language", $p["loginSprache"]);
		if(strtolower($U->getA()->username) != strtolower($p["loginUsername"])) return 0;
		
		$_SESSION["S"]->setLoggedInUser($U);
		$_SESSION["S"]->initApp($p["anwendung"]);

		if(isset($_COOKIE["phynx_customer"]))
			$_SESSION["phynx_customer"] = $_COOKIE["phynx_customer"];
		
		#if($_SESSION["S"]->checkIfUserLoggedIn()) die("Beim Einloggen ist ein Fehler aufgetreten.\nBitte drücken Sie F5 (aktualisieren) und melden Sie sich erneut an.");
		return 1;
	}
	
	protected function doLogout(){
		if(isset($_SESSION["applications"])) $_SESSION["applications"]->setActiveApplication("nil");
		$_SESSION["CurrentAppPlugins"] = null;
		$_SESSION["BPS"] = null;
		$_SESSION["S"]->logoutUser();
		SpeedCache::clearCache();
	}
	
	public function getListOfUsers(){
		$this->lCV3();
		$users = array();
		while(($U = $this->getNextEntry())){
			$UA = $U->getA();
			$users[] = $UA->username;
		}
		
		return $users;
	}

	public function lostPassword($username){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>

		if($username == "") Red::errorC("User", "lostPasswordErrorUser");

		$Lang = $this->loadLanguageClass("User")->getText();

		$ac = new anyC();
		$ac->setCollectionOf("User");
		$ac->addAssocV3("username", "=", $username);
		$ac->lCV3();

		$U = $ac->getNextEntry();

		if($U == null){
			try {
				$AL = new mphynxAltLogin();
				$AL->addAssocV3("username","=",$username);

				$U = $AL->getNextEntry();

			} catch (Exception $e){
				Red::errorC("User", "lostPasswordErrorUser");
			}
		}

		if($U == null) Red::errorC("User", "lostPasswordErrorUser");

		$Admin = new anyC();
		$Admin->setCollectionOf("User");
		$Admin->addAssocV3("isAdmin", "=", "1");

		$Admin = $Admin->getNextEntry();
		if($Admin == null) Red::errorC("User", "lostPasswordErrorAdmin");
		if($Admin->A("UserEmail") == "") Red::errorC("User", "lostPasswordErrorAdmin");

		$mail = new htmlMimeMail5();
		$mail->setFrom("phynx@".$_SERVER["HTTP_HOST"]);
		$mail->setSubject("[phynx] Password recovery for user $username");
		$mail->setText(wordwrap("Dear ".$Admin->A("name").",

you received this email because the user '$username' of the phynx framework at $_SERVER[HTTP_HOST] has lost his password and is requesting a new one.

Best regards
	phynx", 80));
		if(!$mail->send(array($Admin->A("UserEmail"))))
			Red::errorC("User", "lostPasswordErrorAdmin");

		Red::alertC("User", "lostPasswordOK");
	}
}
?>
