<?php
/**
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
class LoginData extends Userdata {
	public function loadMe(){
		parent::loadMe();

		$this->parseData();
	}

	public function  loadMeOrEmpty() {
		parent::loadMeOrEmpty();

		$this->parseData();
	}

	public function parseData(){
		$values = explode("::::",$this->A->wert);

		$this->A->server = "";
		$this->A->benutzername = "".isset($values[0]) ? $values[0] : "";
		$this->A->passwort = "".isset($values[1]) ? $values[1] : "";
		$this->A->optionen = "";


		foreach($values AS $va){
			if(strpos($va, "o:") === 0)
				$this->A->optionen = preg_replace("/^o:/","",$va);

			if(strpos($va, "s:") === 0)
				$this->A->server = preg_replace("/^s:/","",$va);
		}
	}

	public static function getButtonU($preset, $label, $icon){
		$ID = -1;
		$LD = self::getU($preset);
		if($LD != null)
			$ID = $LD->getID();
		
		#if($name == "GoogleAccountUserPass")
		#	$preset = "googleData";
		#else
		#	$preset = $name;
		
		$B = new Button($label, $icon);
		$B->popup("edit", "Benutzerdaten", "LoginData", $ID, "getPopup", "", "LoginDataGUI;preset:$preset");

		return $B;
	}

	/**
	 * Get global login data
	 *
	 * @param string $name
	 * @return LoginData
	 */
	public static function get($name){
		$UD = new mUserdata();
		$UD->addAssocV3("UserID", "=", "-1");
		$UD->addAssocV3("name", "=", $name);

		$e = $UD->getNextEntry();
		if($e == null) return null;

		$LD = new LoginData($e->getID());
		$LD->loadMe();
		return $LD;
	}

	/**
	 * Get login data for current user
	 * 
	 * @param string $name
	 * @return LoginData
	 */
	public static function getU($name, $userID = null){
		if($userID == null AND Session::currentUser() != null)
			$userID = Session::currentUser()->getID();
		
		if($userID == null)
			return null;
		
		$UD = new mUserdata();
		$UD->addAssocV3("UserID", "=", $userID);
		$UD->addAssocV3("name", "=", $name);

		$e = $UD->getNextEntry();
		if($e == null) return null;

		$LD = new LoginData($e->getID());
		$LD->loadMe();
		return $LD;
	}

	public function saveMe($checkUserData = true, $output = false){

		unset($this->A->server);
		unset($this->A->passwort);
		unset($this->A->optionen);
		unset($this->A->benutzername);

		parent::saveMe($checkUserData, $output);
	}

	function getClearClass() {
		return "Userdata";
	}

	public static function getNames($w = ""){
		$n = array(
			"PLuserAndPass" => "PixelLetter",
			"SPuserAndPass" => "signaturportal",
			"LDAPServerUserPass" => "LDAP-Server",
			"MailServerUserPass" => "Mail-Server",
			"GoogleAccountUserPass" => "Google",
			"AmazonAPIKey" => "Amazon API key",
			"klickTelAPIKey" => "klickTel API key",
			"GemeinschaftServerUserPass" => "Gemeinschaft-Server");

		if($w == "") return $n;
		else return $n[$w];
	}
}
?>