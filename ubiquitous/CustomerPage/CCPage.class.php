<?php
/**
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class CCPage {
	protected $loggedIn = false;
	
	function __construct() {
		$_SESSION["viaInterface"] = true;
		if(isset($_GET["cloud"]) AND !isset($_SESSION["phynx_customer"]))
			$_SESSION["phynx_customer"] = $_GET["cloud"];
		
		$this->loggedIn = Session::currentUser() != null;
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	public function loadPlugin($app, $folder, $optional = false){
		if(!file_exists(Util::getRootPath()."$app/$folder/plugin.xml") AND !$optional)
			throw new Exception("Required plugin $app/$folder not available (1)");
			
		if(!file_exists(Util::getRootPath()."$app/$folder/plugin.xml"))
			return false;
		
		$xml = new XMLPlugin(Util::getRootPath()."$app/$folder/plugin.xml");
		
		$allowedPlugins = Environment::getS("allowedPlugins", false);
		$extraPlugins = Environment::getS("pluginsExtra", false);
		$allow = false;
		if($allowedPlugins !== false AND in_array($xml->registerClassName(), $allowedPlugins))
			$allow = true;
		
		if($extraPlugins !== false AND in_array($xml->registerClassName(), $extraPlugins))
			$allow = true;
		
		if($allowedPlugins !== false AND !$allow){
			if(!$optional)
				throw new Exception("Required plugin $app/$folder not available (2)");

			return false;
		}
		
		require_once Util::getRootPath()."$app/$folder/".$xml->registerClassName().".class.php";
		$this->addClassPath(Util::getRootPath()."$app/$folder");
		
		return true;
	}
	
	function addClassPath($absolutePath){
		$dir = new DirectoryIterator($absolutePath);

		if($absolutePath[strlen($absolutePath) - 1] != "/")
			$absolutePath .= "/";

		foreach ($dir as $fileinfo) {
			if($fileinfo->isDot()) continue;
			if(strpos($fileinfo->getFilename(), ".class.php") === false) 
				continue;

			registerClassPath(str_replace(".class.php", "", $fileinfo->getFilename()), $fileinfo->getPathname());
		}
	}
	
	function formLogin(){
		$T = new HTMLForm("login", array("benutzer", "password", "action"), "Anmeldung");
			
		$T->setValue("action", "login");
		$T->setType("action", "hidden");
		$T->setType("password", "password");

		$T->setLabel("password", "Passwort");

		$T->setSaveCustomerPage("Anmelden", "", false, "function(){ document.location.reload(); }");

		return $T.OnEvent::script("\$j(function(){ \$j('[name=benutzer]').trigger('focus'); });");;
	}
	
	function logout(){
		$U = new Users();
		$U->doLogout();
	}
	
	function handleForm($valuesAssocArray){
		switch($valuesAssocArray["action"]){
			case "login":
				if(!Users::login($valuesAssocArray["benutzer"], sha1($valuesAssocArray["password"]), "open3A", "default", true))
					Red::errorD("Benutzer/Passwort unbekannt");
			break;
		}
	}
	
	public function customize(){
		if(defined("PHYNX_FORBID_CUSTOMIZERS"))
			return;
		
		try {
			$active = mUserdata::getGlobalSettingValue("activeCustomizer");
			if($active == null) return;

			$this->customizer = new $active();
		} catch (Exception $e){ }
	}
}
?>