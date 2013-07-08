<?php
/**
 *  This file is part of plugins.

 *  plugins is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  plugins is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class Environment {
	public static $currentEnvironment;
	private $cloudUser;
	
	public function __construct() {
		#$this->customize(); //No good idea, will create a loop
	}
	
	public function customize(){
		try {
			$active = mUserdata::getGlobalSettingValue("activeCustomizer");
			if($active == null) return;

			$this->customizer = new $active();
			$this->customizer->customizeClass($this);
		} catch (Exception $e){ }
	}
	
	public function cloudUser($user = null){
		if($user != null)
			$this->cloudUser = $user;
		
		$return = Aspect::joinPoint("cloudUser", $this, __METHOD__, array($this->cloudUser), $this->cloudUser);
		
		return $return;
	}
	
	public function get($value, $default){
		$value = str_replace(":", "_", $value);
		if(isset($this->$value)) return $this->$value;

		return $default;
	}
	
	/**
	 * @return iCorporateDesign
	 */
	public static function getCorporateDesign(){
		try {
			$FB = new FileBrowser();
			$FB->addDir(FileStorage::getFilesDir());
			$FB->addDir(Util::getRootPath()."specifics");
			
			$T = $FB->getAsLabeledArray("iCorporateDesign", ".class.php");
			foreach($T AS $c){
				$CD = new $c();
				break;
			}
			
			#throw new ClassNotFoundException();
		} catch (ClassNotFoundException $e){
			$CD = new CorporateDesign();
		}
		
		return $CD;
	}

	public static function getS($value, $default){
		Environment::load();
		
		$return = Environment::$currentEnvironment->get($value, $default);

		switch($value){
			case "onLogout":
				return str_replace("%CLOUDUSER", Environment::$currentEnvironment->cloudUser(), $return);
			break;
			
			case "databaseData":
				if(is_array($return))
					return $return;
				
				$ex = explode(";;", $return);

				$dbData = array();
				$dbData["host"] = $ex[0];
				$dbData["user"] = $ex[1];
				$dbData["password"] = $ex[2];
				$dbData["httpHost"] = "*";
				$dbData["datab"] = $ex[3];
				
				$return = $dbData;
			break;
			
			case "allowedApplications":
				if($return == null)
					return null;
				
				return explode(",", $return);
			break;
			
			case "pluginsExtra":
			case "allowedPlugins":
			case "hiddenPlugins":
				if($return == null)
					return $default;
				
				return explode(",", $return);
			break;
			
			case "customizer":
			case "customizerExtra":
				if($return == null)
					return $default;
				
				return explode(",", $return);
			break;
		}
		
		$return = Aspect::joinPoint("alter", null, __METHOD__, array($value, $default), $return);

		return $return;
	}

	public static function load(){
		if(Environment::$currentEnvironment != null) return;

		if(file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php")/* AND !defined("PHYNX_VIA_INTERFACE")*/){ //!defined("PHYNX_VIA_INTERFACE") removed because of ZPush/lightCRM 03.07.2013
			require_once Util::getRootPath()."plugins/Cloud/Cloud.class.php";
			require_once Util::getRootPath()."plugins/Cloud/mCloud.class.php";
			require_once Util::getRootPath()."plugins/Cloud/CloudStorage.class.php";
			
			Environment::$currentEnvironment = mCloud::getEnvironment();
			return;
		}

		$h = "Environment".str_replace(array(":", "-"), "_", implode("", array_map("ucfirst", explode(".", $_SERVER["HTTP_HOST"]))));
		
		if(defined("PHYNX_VIA_INTERFACE")){
			if(file_exists(Util::getRootPath()."specifics/$h.class.php")){
				require_once(Util::getRootPath()."specifics/$h.class.php");
				Environment::$currentEnvironment = new $h();
			} else
				Environment::$currentEnvironment = new Environment();
			
			return;
		}
		
		try {
			try {
				Environment::$currentEnvironment = new $h();
			} catch (ClassNotFoundException $e){
				Environment::$currentEnvironment = new EnvironmentCurrent();
			}
		} catch (ClassNotFoundException $e){
			Environment::$currentEnvironment = new Environment();
		}
		
	}
}
?>
