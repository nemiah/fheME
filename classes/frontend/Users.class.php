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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class Users extends anyC {
	function __construct(){
		$this->setCollectionOf("User");

		$this->customize();
	}

	public static function getUsers($type = 0){
		$LD = LoginData::get("ADServerUserPass");
		if(mUserdata::getGlobalSettingValue("AppServer", "") != ""){
			$U = new Users();
			$U->getAppServerUsers();
		} elseif($LD != null AND $LD->A("server") != ""){
			$U = new LoginAD();
			$U->getUsers();
		} else {
			$U = new Users();
			$U->addAssocV3("isAdmin", "=", "0");
			$U->addAssocV3("UserType", "=", $type);
			
			Aspect::joinPoint("alterSystem", __CLASS__, __METHOD__, [$U]);
		}

		return $U;
	}
	
	public static function getUsersArray($zeroEntry = null, $includeDeleted = false){
		$AC = self::getUsers(0);
		$AC->addOrderV3("name");
		
		$U = array();
		if($zeroEntry)
			$U[0] = $zeroEntry;
		
		while($R = $AC->n())
			$U[$R->getID()] = $R->A("name");
		
		if($includeDeleted){
			$AC = anyC::get("UserOld");
			$AC->addAssocV3("isAdmin", "=", "0");
			$AC->addAssocV3("UserType", "=", "0");
			
			while($R = $AC->n())
				$U[$R->A("UserOldUserID")] = $R->A("name");
		}
		
		return $U;
	}

	public static function login($username, $password, $application, $language = "default", $isCustomerPage = false, $isPWEncrypted = true){
		$U = new Users();
		return $U->doLogin(array("loginUsername" => $username, "loginSHAPassword" => $password, "anwendung" => $application, "loginSprache" => $language, "isCustomerPage" => $isCustomerPage, "loginPWEncrypted" => $isPWEncrypted)) > 0;
	}
	
	public function getUser($username, $password, $isSHA = false){
		if($password == ";;;-1;;;")
			return null;
		
		$user = $this->getAppServerUser($username, !$isSHA ? sha1($password) : $password);
		if($user != null)
			return $user;
		
		if(class_exists("ZLog", false))
			ZLog::Write(LOGLEVEL_DEBUG, "lcrm (".__LINE__.")::Logon():No appserver user! SHA? ".($isSHA ? "yes" : "no")."; $username");
		
		$user = LoginAD::getUser($username, $password);
		if($user != null)
			return $user;
		
		$this->addAssocV3("username","=",$username);
		if(!$isSHA)
			$this->addAssocV3("SHApassword","=",sha1($password),"AND","1");
		else
			$this->addAssocV3("SHApassword","=",$password,"AND","1");
		
		$this->addAssocV3("password","=",$password,"OR","1");
		$this->addAssocV3("UserType", "=", "0");

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
		} catch (Exception $e){
			if(class_exists("ZLog", false))
				ZLog::Write(LOGLEVEL_WARN, "lcrm (".__LINE__.")::Logon():Exception: ".get_class($e).": ".$e->getMessage());
		}

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
	
	public function doLogin($ps){
		$validUntil = Environment::getS("validUntil", null);
		if($validUntil != null and $validUntil < time())
			Red::errorD("Diese Version ist abgelaufen. Bitte wenden Sie sich an den Support.");
		
		if(!is_array($ps)) 
			parse_str($ps, $p);
		else 
			$p = $ps;

		$this->doLogout();
		
		$_SESSION["DBData"] = $_SESSION["S"]->getDBData(null, isset($p["loginMandant"]) ? $p["loginMandant"] : null);

		
		try {
			if(isset($p["loginMandant"]) AND file_exists(Util::getRootPath()."plugins/multiInstall/plugin.xml")){
				$DB = new DBStorage();
				$DB->renewConnection();
			}

			$U = $this->getUser($p["loginUsername"], $p["loginSHAPassword"], $p["loginPWEncrypted"]);
			if($U === null) return 0;

			if(get_class($U) == "phynxAltLogin") $p["anwendung"] = $U->A("UserApplication");

			if($U->A("allowedApplications") != null AND is_array($U->A("allowedApplications")) AND !in_array($p["anwendung"], $U->A("allowedApplications")))
				return 0;

			
			$AC = anyC::get("Userdata", "name", "loginTo".((isset($p["isCustomerPage"]) AND $p["isCustomerPage"]) ? "customerPage" : $p["anwendung"]));
			$AC->addAssocV3("UserID", "=", $U->getID());
			$UD = $AC->n();
			if($UD != null AND $UD->A("wert") == "0")
				return 0;
			
			/*$AC = anyC::get("Userdata", "name", "loginToApplication");
			$AC->addAssocV3("UserID", "=", $U->getID());
			$UD = $AC->n();
			if($UD != null AND $UD->A("wert") == "0")
				return 0;*/
			
			$UA = $U->getA();
			
		} catch (Exception $e){
			if($p["loginUsername"] == "Admin" AND $p["loginSHAPassword"] == "4e7afebcfbae000b22c7c85e5560f89a2a0280b4"){#"Admin"){
				$tu = new User(-1);
				$UA = $tu->newAttributes();
				$UA->name = "Installations-Benutzer";
				$UA->username = "Admin";
				$UA->password = "Admin";
				if($p["loginSprache"] != "default")
					$UA->language = $p["loginSprache"];
				$UA->isInstall = true;
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
	
	public function doLogout(){
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

	public function changePassword($username, $oldPassword, $newPassword1, $newPassword2){
		if($newPassword1 == sha1("") OR $newPassword2 == sha1(""))
			Red::errorD ("Bitte geben Sie neue Passwörter ein");
		
		if($newPassword1 != $newPassword2)
			Red::errorD ("Die Passwörter stimmen nicht überein");
		
		$U = $this->getUser($username, $oldPassword, true);
		if(!$U)
			Red::errorD ("Benutzer unbekannt");
		
		if($U->A("isAdmin"))
			Red::errorD ("Benutzer unbekannt");
		
		$U->changeA("SHApassword", $newPassword1);
		$U->saveMe(false, false, false);
		
		Red::messageD("Passwort geändert!");
	}
	
	public function lostPassword($username){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>
				
		if($username == "") 
			Red::errorC("User", "lostPasswordErrorUser");


		$ac = anyC::get("User", "username", $username);
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

		
		if(
			file_exists(Util::getRootPath()."/ubiquitous/Passwort/Passwort.class.php") 
			AND (strpos(Environment::getS("pluginsExtra", ""), "mPasswort") !== false 
				OR strpos(Environment::getS("allowedPlugins", ""), "mPasswort") !== false)){
			require_once Util::getRootPath()."/ubiquitous/Passwort/Passwort.class.php";
			
			$P = new Passwort();
			$P->request($U);
			Red::alertD("Sie haben eine neue Passwortanforderung per E-Mail erhalten.");
		}
			
		
		$Admin = new anyC();
		$Admin->setCollectionOf("User");
		$Admin->addAssocV3("isAdmin", "=", "1");

		$Admin = $Admin->getNextEntry();
		if($Admin == null) Red::errorC("User", "lostPasswordErrorAdmin");
		if($Admin->A("UserEmail") == "") Red::errorC("User", "lostPasswordErrorAdmin");

		$mail = new htmlMimeMail5();
		$mail->setFrom("open3A@".$_SERVER["HTTP_HOST"]);
		$mail->setSubject("[open3A] Password recovery for user $username");
		$mail->setText(wordwrap("Dear ".$Admin->A("name").",

you receive this email because the user '$username' of the open3A installation at $_SERVER[HTTP_HOST] has lost his password and is requesting a new one.

Best regards
	open3A", 80));
		if(!$mail->send(array($Admin->A("UserEmail"))))
			Red::errorC("User", "lostPasswordErrorAdmin");

		Red::alertC("User", "lostPasswordOK");
	}
}
?>
