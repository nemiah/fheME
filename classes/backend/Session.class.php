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
class Session {
	static $instance;
	private $currentUser = null;
	private $afterLoginFunctions = array();
	//public $menu;
	private static $sessionVariable = "S";
	
	public $physion = null;
	
	public static function physion($sessionName = null, $application = null, $plugin = null, $favico = null){
		if($sessionName != null)
			$_SESSION[self::$sessionVariable]->physion = array($sessionName, $application, $plugin, $favico);
		
		return $_SESSION[self::$sessionVariable]->physion;
	}
	
	public static function init(){
		$_SESSION[self::$sessionVariable] = new Session();
		
		SysMessages::init();
		
		if(!isset($_SESSION["DBData"])) 
			self::reloadDBData();

		Applications::init();
		JSLoader::init();

		if(!defined("PHYNX_VIA_INTERFACE"))
			AppPlugins::init();
	}
	
	public static function reloadDBData() {
		$_SESSION["DBData"] = $_SESSION[self::$sessionVariable]->getDBData();
		
		$DBWrite = Environment::getS("databaseDataWrite", null);
		if($DBWrite !== null)
			$_SESSION["DBDataWrite"] = $DBWrite;
	}
	
	public function getDBData($newFolder = null){
		$external = false;
		
		if(file_exists(Util::getRootPath()."../../phynxConfig")){
			$newFolder = Util::getRootPath()."../../phynxConfig/";
			$external = true;
		}
		
		if(file_exists(Util::getRootPath()."../phynxConfig")){
			$newFolder = Util::getRootPath()."../phynxConfig/";
			$external = true;
		}
		
		if($newFolder == null)
			$newFolder = Util::getRootPath()."system/DBData/";
		
		$findFor = "*";
		if(isset($_SERVER["HTTP_HOST"]))
			$findFor = $_SERVER["HTTP_HOST"];
		$data = new mInstallation();
		if($newFolder != "") $data->changeFolder($newFolder);
		$data->setAssocV3("httpHost","=",$findFor);
		#$data->loadCollectionV2();
		
		$n = $data->getNextEntry();
		
		if($n == null) {
			#$data = new mInstallation();
			#if($newFolder != "") $data->changeFolder($newFolder);
			$data = new mInstallation();
			if($newFolder != "") $data->changeFolder($newFolder);
			$data->setAssocV3("httpHost","=","*");
			$n = $data->getNextEntry();
		}

		if($n != null){
			$n->changeFolder($newFolder);
			$d = $n->getA();
		} else {
			if(!isset($_SERVER["HTTP_CLOUD"])){
				$I = new Installation(-1);
				$I->changeFolder($newFolder);
				$I->makeNewInstallation();
				$d = $I->getA();
			} 
		}
		$I2 = new Installation(-1);
		$s = PMReflector::getAttributesArray($I2->newAttributes());
		
		$t = array();
		if(isset($d))
			foreach($s as $key => $value)
				$t[$value] = $d->$value;
		
		$t["external"] = $external;
		
		$rt = Environment::getS("databaseData", $t);
		
		return $rt;
	}
	
	public static function getSI() {
		if (!Session::$instance)
			Session::$instance = new Session();
		return Session::$instance;
	}
	
	public function getCurrentUser(){
		return $this->currentUser;
	}
	
	public function checkIfUserLoggedIn(){
		return $this->currentUser == null;
	}
	
	public function checkIfUserIsAllowed($plugin){
		if($plugin == "Menu") return true;
		if($plugin == "Messages") return true;
		if($plugin == "JSLoader") return true;
		if($plugin == "Printers") return true;
		if($plugin == "Credits") return true;
		if($plugin == "Desktop") return true;
		#if($plugin == "DesktopLink") return true;
		if($plugin == "Userdata" AND $this->isUserAdmin()) return true;
		if($plugin == "BackupManager" AND $this->isUserAdmin()) return true;
		if($plugin == "LoginData" AND $this->isUserAdmin()) return true;
		if($plugin == "LoginDataGUI" AND $this->isUserAdmin()) return true;
		if($plugin == "TempFile" AND $this->isUserAdmin()) return true;

		#if(!SpeedCache::inCache("allowedPlugins"))
		#	SpeedCache::setCache("allowedPlugins", Environment::getS("allowedPlugins", array()));
		#echo "test";
		#print_r(Environment::getS("allowedPlugins", array()));
		#$allowed = SpeedCache::getCache("allowedPlugins", array());

		#if(count($allowed) > 0 AND !in_array($plugin, $allowed))
		#	return false;
		
		return ($this->isUserAdmin() == $_SESSION["CurrentAppPlugins"]->getIsAdminOnly($plugin));
	}

	public function isUserAdmin(){
		$UA = $this->currentUser->getA();
		return $UA->isAdmin;
	}

	public static function isUserAdminS(){
		return Session::currentUser()->A("isAdmin") == "1";
	}

	public function isAltUser(){
		return get_class($this->currentUser) == "phynxAltLogin";
	}
	
	public static function isAltUserS(){
		return get_class(Session::currentUser()) == "phynxAltLogin";
	}
	
	public function setLoggedInUser($U){
		$UA = $U->getA();
		$_SESSION["messages"]->addMessage("User $UA->name logged in, letting system know...");
		$this->currentUser = $U;
	}
	
	public function runOnLoginFunctions(){
		ob_start();
		foreach($this->afterLoginFunctions as $key => $value) {
			try {
				$c = new $key;
				if(!method_exists($c, $value))
					continue;
				
				#$f = "@".$key."::".$value."();";
				#eval($f);
				#$s = explode("::",$value);
				$method = new ReflectionMethod($key, $value);
				$method->invoke(null);
				
			} catch(Exception $e){
				continue;
			}
		}
		ob_end_clean();
	}
	
	public function logoutUser(){
		$this->currentUser = null;
	}
	/*
	public function checkForMainStorage(){
		
		$user = new User(1);
		#try {
		@$user->getA();
		#}
		#catch (DatabaseNotSelectedException $e) { return false; }
		#catch (NoDBUserDataException $e) { return false; }
		#catch (StorageException $e) { return true; }
		return true;
	}*/

	public static function isPluginLoaded($pluginName){

		if(isset($_SESSION["viaInterface"]) AND $_SESSION["viaInterface"] == true)
			return class_exists($pluginName, false);

		if(!isset($_SESSION["CurrentAppPlugins"])) 
			return false;
		
		return $_SESSION["CurrentAppPlugins"]->isPluginLoaded($pluginName);
		#return in_array($pluginName,$_SESSION["CurrentAppPlugins"]->getAllPlugins());
	}

	/**
	 * @return User
	 */
	public static function currentUser(){
		return $_SESSION["S"]->getCurrentUser();
	}

	public function checkForPlugin($pluginName){
		if(!isset($_SESSION["CurrentAppPlugins"])) return false;
		return $_SESSION["CurrentAppPlugins"]->isPluginLoaded($pluginName);
	}

	public function registerOnLoginFunction($class, $function){
		$this->afterLoginFunctions[$class] = $function;
	}

	public static function getLanguage(){
		return $_SESSION["S"]->getUserLanguage();
	}

	public function getUserLanguage(){
		if($this->currentUser == null) {
			return Util::lang_getfrombrowser(array("de", "en", "it"), "de_DE");
			#return "de_DE";
		}
		$l = $this->currentUser->getA()->language;
		return $l == "" ? "de_DE" : $l;
	}
	
	public function initApp($application){
		$_SESSION[$application] = array();
		$_SESSION["BPS"] = new BackgroundPluginState();
		$_SESSION["JS"] = new JSLoader();
		$_SESSION["CurrentAppPlugins"] = new AppPlugins();
		$_SESSION["CurrentAppPlugins"]->scanPlugins();
		$_SESSION["applications"]->setActiveApplication($application);
		$_SESSION["CurrentAppPlugins"]->scanPlugins();
		$_SESSION["classPaths"] = array();
		$this->runOnLoginFunctions();
	}
	
	public function switchApplication($application){
		$allowedApplications = Environment::getS("allowedApplications", null);
		if($allowedApplications != null AND !in_array($application, $allowedApplications))
			Red::errorD("Bitte wenden Sie sich an den Support, wenn Sie $application verwenden möchten");
		
		ob_start();
		$U = new UsersGUI();
		
		$c = $this->getCurrentUser();
		$d = array();
		$d["loginUsername"] = $c->getA()->username;
		$d["loginSHAPassword"] = $c->getA()->SHApassword;
		$d["loginSprache"] = $c->getA()->language;
		$d["anwendung"] = $application;
		$U->doLogin($d);
		ob_end_clean();
	}
	
	function getAgent()	{
		if (strstr($_SERVER['HTTP_USER_AGENT'],'Opera'))
			return "Opera";

		if (strstr($_SERVER['HTTP_USER_AGENT'],'MSIE'))
			return "IE";
			
		if (strstr($_SERVER['HTTP_USER_AGENT'],'Firefox'))
			return "Firefox";
			
		if (strstr($_SERVER['HTTP_USER_AGENT'],'Mozilla'))
			return "Mozilla";
		
		return "unknown";
	}
}
?>
