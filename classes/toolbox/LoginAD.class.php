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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class LoginAD extends Collection {
	public function lCV3($id = -1, $returnCollector = true, $lazyload = false){
		
	}
	
	static function GUIDtoInt($binary_guid) {
	  $unpacked = unpack('Va/v2b/n2c/Nd', $binary_guid);
	  $str = sprintf('%08X-%04X-%04X-%04X-%04X%08X', $unpacked['a'], $unpacked['b1'], $unpacked['b2'], $unpacked['c1'], $unpacked['c2'], $unpacked['d']);

	  $ex = explode("-", $str);

	  $int = "";
	  foreach($ex AS $s){
		  $e = str_split($s);
		  $sint = 0;
		  foreach($e AS $c)
			$sint += ord($c);

		  $int .= $sint;
	  }

	  return $int % 2147483647;
	}

	private static function getADEntry($result){
		$U = new stdClass();

		$U->UserID = self::GUIDtoInt($result["objectguid"][0]);
		$U->username = $result["samaccountname"][0];
		$U->name = $result["cn"][0];
		$U->isAdmin = 0;
		$U->language = "de_DE";
		$U->UserEmail = isset($result["mail"]) ? $result["mail"][0] : "";
		$U->UserType = 0;
		
		return $U;
	}
	
	private static function getADConnection($username = null, $password = null){
		if(!function_exists("ldap_connect"))
			return null;
		
		$LD = LoginData::get("ADServerUserPass");
		if($LD == null)
			return null;
		
		$adServer = "ldap://".$LD->A("server");
		$ex = explode("@", $LD->A("benutzername"));
		if($username == null)
			$username = $LD->A("benutzername");
		else
			$username = $username."@".$ex[1];
		
		if($password == null)
			$password = $LD->A("passwort");

		$ldap = ldap_connect($adServer);

		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		$bind = ldap_bind($ldap, $username, $password);

		if(!$bind) 
			throw new Exception("Keine Verbindung zu AD-Server");

		return $ldap;
	}
	
	public function getUsers(){
		if(Session::currentUser() == null)
			throw new Exception("No user logged in!");
		
		$this->collector = array();
		$collection = array();
		try {
			$ldap = self::getADConnection();
			if($ldap == null)
				return;
			
			$LD = LoginData::get("ADServerUserPass");
			$result = ldap_search($ldap, $LD->A("optionen"), "(&(objectCategory=person)(samaccountname=*))");

			if(function_exists("ldap_sort"))
				ldap_sort($ldap, $result, "sn");
			$info = ldap_get_entries($ldap, $result);

			foreach($info AS $user){
				if(!isset($user["samaccountname"]))
					continue;
				
				$R = self::getADEntry($user);
				
				$U = new User($R->UserID);
				$U->setA($R);
				$collection[] = $U;
			}
			
			
		} catch(Exception $e){}
		
		
		$this->collector = array_merge($this->collector, $collection);
	}
	
	public static function getUserById($userID){
		$U = new LoginAD();
		$U->getUsers();
		
		while($User = $U->n()){
			if($User->getID() == $userID)
				return $User->getA();
		}
		
		return null;
	}
	
	public static function getUser($username, $password){
		try {
			$ldap = self::getADConnection($username, $password);
			if($ldap == null)
				return null;
			
			$LD = LoginData::get("ADServerUserPass");
			$result = ldap_search($ldap, $LD->A("optionen"), "(&(objectCategory=person)(samaccountname=$username))");

			#ldap_sort($ldap, $result, "sn");
			$info = ldap_get_entries($ldap, $result);

			foreach($info AS $user){
				if(!isset($user["samaccountname"]))
					continue;
				
				$R = self::getADEntry($user);
				
				$U = new User($R->UserID);
				$U->setA($R);
				
				return $U;
			}
			
			
		} catch (Exception $e){}

		return null;
	}
}
?>