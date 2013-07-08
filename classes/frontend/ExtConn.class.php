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
class ExtConn {
	protected $absolutePath;
	protected $paths = array();
	
	protected $currentUser;
	
	protected $errors = array();

	protected $pluginMethods = array();
	protected $pluginInterfaces = array();

	function __construct($absolutePathToPhynx, $propagateViaInterface = true){
		if(!defined("PHYNX_MAIN_STORAGE"))
			if(function_exists("mysqli_connect")) define("PHYNX_MAIN_STORAGE","MySQL");
			else define("PHYNX_MAIN_STORAGE","MySQLo");

		if($propagateViaInterface)
			define("PHYNX_VIA_INTERFACE", true);

		if($absolutePathToPhynx{strlen($absolutePathToPhynx) - 1} != "/") $absolutePathToPhynx .= "/";
		
		$this->absolutePath = $absolutePathToPhynx;

		$this->paths[] = $this->absolutePath."libraries/PhpFileDB.class.php";

		$this->paths[] = $this->absolutePath."classes/backend/Collection.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/Adapter.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/SelectStatement.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/DBStorage.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/DBStorageU.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/Attributes.class.php";
		#$this->paths[] = $this->absolutePath."classes/backend/PersistentClass.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/PersistentObject.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/User.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/anyC.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/Session.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/BackgroundPluginState.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/UnpersistentClass.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/PluginV2.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/XMLPlugin.class.php";
		$this->paths[] = $this->absolutePath."classes/backend/FileStorage.class.php";
		
		$this->paths[] = $this->absolutePath."classes/exceptions/o3AException.class.php";
		$this->paths[] = $this->absolutePath."classes/exceptions/StorageException.class.php";
		$this->paths[] = $this->absolutePath."classes/exceptions/NoDBUserDataException.class.php";
		$this->paths[] = $this->absolutePath."classes/exceptions/AOPNoAdviceException.class.php";
		$this->paths[] = $this->absolutePath."classes/exceptions/ClassNotFoundException.class.php";
		
		$this->paths[] = $this->absolutePath."classes/toolbox/SysMessages.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/SystemCommand.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/PMReflector.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/Datum.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/Util.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/BPS.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/Factory.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/ISO3166.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/Aspect.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/EUCountries.class.php";
		$this->paths[] = $this->absolutePath."classes/toolbox/Registry.class.php";

		$this->paths[] = $this->absolutePath."classes/interfaces/iFileBrowser.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iLDAPExport.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iDesktopLink.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/icontextMenu.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iCloneable.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iDeletable.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iDeletable2.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iRepeatable.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iScrollable.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iNewWithValues.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iGUIHTML2.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iGUIHTMLMP2.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iPluginSpecificRestrictions.class.php";
		#$this->paths[] = $this->absolutePath."classes/interfaces/iFPDF.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iXMLExport.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iUnifiedTable.class.php";
		$this->paths[] = $this->absolutePath."classes/interfaces/iPluginV2.class.php";
		
		$this->paths[] = $this->absolutePath."classes/frontend/Users.class.php";
		#$this->paths[] = $this->absolutePath."classes/frontend/UserAttributes.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/AppPlugins.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/Applications.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/HTMLGUI.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/HTMLGUI2.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/HTML_de_DE.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/HTML_en_US.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/UnifiedTable.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/HTMLTable.class.php";
		$this->paths[] = $this->absolutePath."classes/frontend/JSLoader.class.php";
		
		$this->paths[] = $this->absolutePath."plugins/Userdata/mUserdata.class.php";
		$this->paths[] = $this->absolutePath."plugins/Userdata/Userdata.class.php";
		#$this->paths[] = $this->absolutePath."plugins/Userdata/UserdataAttributes.class.php";

		$this->paths[] = $this->absolutePath."classes/toolbox/LoginData.class.php";//Or else will not find Userdata
		$this->paths[] = $this->absolutePath."classes/toolbox/Environment.class.php";
		
		if(file_exists($this->absolutePath."specifics/EnvironmentCurrent.class.php"))
			$this->paths[] = $this->absolutePath."specifics/EnvironmentCurrent.class.php";
		
		$this->setPaths();
		
		if(session_id() == "") session_start();
	
		if(isset($_SESSION["S"]) AND !is_object($_SESSION["S"]) AND get_class($_SESSION["S"]) != "Session")
			die($this->getErrorMessage("10"));
		
		if(isset($_SESSION["messages"]) AND !is_object($_SESSION["messages"]) AND get_class($_SESSION["messages"]) != "SysMessages")
			die($this->getErrorMessage("11"));
		
		if(isset($_SESSION["BPS"]) AND !is_object($_SESSION["BPS"]) AND get_class($_SESSION["BPS"]) != "BackgroundPluginState")
			die($this->getErrorMessage("12"));
		
		if(!isset($_SESSION["S"]))
			$_SESSION["S"] = new Session();
		
		SysMessages::init();
		$_SESSION["messages"]->startLogging();
		$_SESSION["BPS"] = new BackgroundPluginState();

		$_SESSION["viaInterface"] = true;
	}

	public function loadPlugin($app, $folder, $optional = false){
		if(!file_exists(Util::getRootPath()."$app/$folder/plugin.xml") AND !$optional)
			throw new Exception("Required plugin $app/$folder not available");
			
		if(!file_exists(Util::getRootPath()."$app/$folder/plugin.xml"))
			return false;
		
		$xml = new XMLPlugin(Util::getRootPath()."$app/$folder/plugin.xml");
		
		require_once Util::getRootPath()."$app/$folder/".$xml->registerClassName().".class.php";
		$this->addClassPath(Util::getRootPath()."$app/$folder");
		
		return true;
	}
	
	function autofailer(){
		spl_autoload_register("ExtConn::autofail");
	}
	
	static function autofail($c){
		if(class_exists($c, false))
			return;
		
		if(strpos($c, "Zend_") === 0)
			return;
		
		eval('class '.$c.' { ' .
			'    public function __construct() { ' .
			'        throw new ClassNotFoundException("'.$c.'"); ' .
			'    } ' .
			'} ');
	}
	
	function forbidCustomizers(){
		define("PHYNX_FORBID_CUSTOMIZERS", true);
	}
	
	function addClassPath($absolutePath){
		$dir = new DirectoryIterator($absolutePath);

		if($absolutePath[strlen($absolutePath) - 1] != "/")
			$absolutePath .= "/";

		foreach ($dir as $fileinfo) {
			if($fileinfo->isDot()) continue;
			if(strpos($fileinfo->getFilename(), ".class.php") === false) continue;


			registerClassPath(str_replace(".class.php", "", $fileinfo->getFilename()), $absolutePath.$fileinfo->getFilename());
		}
	}

	public function setFlag($class, $flag, $value){
		BPS::setProperty($class, $flag, $value);
	}
	
	function useDefaultMySQLData($httpHost = "*"){
		$PFDB = new PhpFileDB();
		if(file_exists($this->absolutePath."../phynxConfig"))
			$PFDB->setFolder($this->absolutePath."../phynxConfig/");
		else
			$PFDB->setFolder($this->absolutePath."system/DBData/");
		$Data = false;
		
		if($httpHost != "*"){
			$q = $PFDB->pfdbQuery("SELECT * FROM Installation WHERE httpHost = '$httpHost'");
			$Data = $PFDB->pfdbFetchAssoc($q);
		} else {
			$q = $PFDB->pfdbQuery("SELECT * FROM Installation WHERE httpHost = '".$_SERVER["HTTP_HOST"]."'");
			$Data = $PFDB->pfdbFetchAssoc($q);
		}
		
		if($Data === false){
			$q = $PFDB->pfdbQuery("SELECT * FROM Installation WHERE httpHost = '*'");
			$Data = $PFDB->pfdbFetchAssoc($q);
		
		
		}
		
		$this->setMySQLData($Data["host"], $Data["user"], $Data["password"], $Data["datab"]);
	}

	public function useAdminUser(){
		$ac = anyC::get("User");
		$ac->addAssocV3("isAdmin", "=", "1");
		$ac->setLimitV3("1");

		$u = $ac->getNextEntry();

		if($u == null){
			$this->errors[] = "100";
			return false;
		}

		return $this->login($u->A("username"), $u->A("SHApassword"), true);
	}
	
	public function useUser($username = null){
		$ac = anyC::get("User");
		if($username != null) $ac->addAssocV3("username", "=", $username);
		$ac->addAssocV3("isAdmin", "=", "0");
		$ac->setLimitV3("1");

		$u = $ac->getNextEntry();

		if($u == null){
			$this->errors[] = "100";
			return false;
		}

		return $this->login($u->A("username"), $u->A("SHApassword"), true);
	}

	function setMySQLData($host, $username, $password, $database){

		$_SESSION["DBData"] = array();
		$_SESSION["DBData"]["host"]		= $host;
		$_SESSION["DBData"]["user"]		= $username;
		$_SESSION["DBData"]["password"] = $password;
		$_SESSION["DBData"]["datab"]	= $database;
	}
	
	function setPaths(){
		foreach($this->paths as $k => $v){
			require_once($v);
			unset($this->paths[$k]);
		}
	}

	function loadPluginInterface($relativePath, $interfaceClass = null){
		if($relativePath[strlen($relativePath) - 1] != "/")
			$relativePath .= "/";

		if($interfaceClass == null)
			$interfaceClass = get_class($this).basename($relativePath);

		require_once($this->absolutePath.$relativePath.$interfaceClass.".class.php");

		$reflection = new ReflectionClass($interfaceClass);
		foreach($reflection->getMethods() AS $M)
			$this->pluginMethods[$M->name] = $M;

		$C = new $interfaceClass($this->absolutePath);
		
		$this->pluginInterfaces[$interfaceClass] = $C;
	}

	function __call($name, $arguments) {
		if(!isset($this->pluginMethods[$name]))
			throw new Exception ("The method $name does not exist!");

		$Method = $this->pluginMethods[$name];
		return $Method->invokeArgs($this->pluginInterfaces[$Method->class], $arguments);
	}

	function getErrors(){
		return $this->errors;
	}
	
	function getLastError(){
		return $this->errors[count($this->errors) - 1];
	}
	
	function getErrorMessage($number){
		switch($number){
			case "10":
				return "\$_SESSION[S] is already in use by your program.";
			break;
			case "11":
				return "\$_SESSION[messages] is already in use by your program.";
			break;
			case "12":
				return "\$_SESSION[BPS] is already in use by your program.";
			break;
			
			case "100":
				return "Username/password unknown";
			break;
			case "101":
				return "No user logged in!";
			break;
			
			default:
				return "Error number unknown";
			break;
		}
	}
	
	function login($username, $password, $isSHA = false){
		#$_SESSION["messages"] = new SysMessages();
		$U = new Users();
		$U = $U->getUser($username, $password, $isSHA);

		if(!is_null($U)){
			$this->currentUser = $U;
			$_SESSION["S"]->setLoggedInUser($U);
			return true;
		} else {
			$this->errors[] = "100";
			return false;
		}
	}
	
	public function cleanUp(){
		#if(isset($_SESSION["messages"])) unset($_SESSION["messages"]);
		#if(isset($_SESSION["DBData"])) unset($_SESSION["DBData"]);
		#if(isset($_SESSION["S"])) unset($_SESSION["S"]);
		#if(isset($_SESSION["BPS"])) unset($_SESSION["BPS"]);


		$_SESSION = array();

		if (ini_get("session.use_cookies")) {

			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		
		session_destroy();
	}
}
?>
