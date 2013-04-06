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
class AppPlugins {
	private $folders = array();
	private $classes = array();
	private $targets = array();
	private $isAdminOnlyByPlugin = array();
	private $deps = array();
	private $collectors = array();
	private $pluginToFolder = array();
	private $menuEntries = array();
	private $appFolder = array();
	private $icons = array();
	private $isGeneric = array();
	private $genericPlugins = array();
	private $versions = array();
	public $blacklist = array();
	
	private static $sessionVariable = "CurrentAppPlugins";

	/**
	 * @return AppPlugins 
	 */
	public static function i(){
		return $_SESSION[self::$sessionVariable];
	}
	
	public static function init(){
		$_SESSION[self::$sessionVariable] = new AppPlugins();
	}
	
	public function  __construct($appFolder = null) {
		$this->scanPlugins($appFolder);
	}

	public static function blacklistPlugin($pluginName){
		if(isset($_SESSION[self::$sessionVariable]))
			$_SESSION[self::$sessionVariable]->blacklist[$pluginName] = true;
	}

	public static function resetBlacklist(){
		if(isset($_SESSION[self::$sessionVariable]))
			$_SESSION[self::$sessionVariable]->blacklist = array();
	}

	public function scanPlugins($appFolder = null){
		#file_put_contents(Util::getRootPath()."debug.txt", print_r(debug_backtrace(), true));
		#echo "<pre>";
		#print_r();
		#echo "</pre>";
		
		
		foreach($this->appFolder AS $key => $value){
			if($value == "plugins") continue;
			
			unset($this->menuEntries[array_search($key, $this->menuEntries)]);
		}
		
		#echo "scanning for plugins...<br />";

		if($appFolder == null){
			$folder = "plugins";
			if($_SESSION["applications"]->getActiveApplication() != "nil") $folder = $_SESSION["applications"]->getActiveApplication();
		} else $folder = $appFolder;

		#$allowedPlugins = "noSaaS";
		$allowedPlugins = Environment::getS("allowedPlugins", array());
		$extraPlugins = Environment::getS("pluginsExtra", array());
		$allowedPlugins = array_merge($allowedPlugins, $extraPlugins);
		#print_r($allowedPlugins);
		/*if($folder != "plugins" AND $_SERVER["HTTP_HOST"] != "dev.furtmeier.lan"){
			try {
				$saas = new SaaS(-1);
				
				$sa = $saas->getCurrent();
				if($sa != null){
					$allowedPlugins = array_map("trim",explode("\n",$sa->getA()->SaaSPlugins));
				} else $allowedPlugins = array();
			} catch(ClassNotFoundException $e){ }
			
		}*/


		#$p = ".".(is_dir("./$folder/") ? "" : ".");
		$p = Util::getRootPath();

		if($p[strlen($p) - 1] == "/")
			$p[strlen($p) - 1] = " ";

		$p = trim($p);

		$_SESSION["messages"]->startMessage("checking for directory $p/$folder/: ");
		
		if(is_dir("$p/$folder/")){
			$_SESSION["messages"]->endMessage("found");
			
			$plugins = array();
			$fp = opendir("$p/$folder/");
			while(($file = readdir($fp)) !== false) {
				if($file == "." OR $file == "..") continue;
				
				if(is_dir("$p/$folder/$file")) {
					if(file_exists("$p/$folder/$file/plugin.xml"))
						$file = "$file/plugin.xml";
					else
						continue;
				}
				if(stripos($file, "plugin") === false) continue;
				
				$plugins[] = $file;
			}


			sort($plugins);

			foreach($plugins as $key => $file){
				$f = explode(".",$file);
				if($f[0]{0} == "-") continue;
				
				if($f[1] == "xml") {
					$c = new XMLPlugin("$p/$folder/$file", $allowedPlugins);
				} else {
					require_once "$p/$folder/$file";
					$f = $f[0];
					$c = new $f();
				}
				
				$_SESSION["messages"]->startMessage("trying to register ".$c->registerName().": ");
				
				if(count($allowedPlugins) > 0 AND !in_array($c->registerClassName(), $allowedPlugins)){
					$_SESSION["messages"]->endMessage(" not allowed");
					continue;
				}
				
				$pFolder = $c->registerFolder();
				if(!is_array($pFolder))
					$this->folders[] = $pFolder;
				else
					foreach($pFolder as $k => $v) $this->folders[] = $v;
				
				$this->pluginToFolder[$c->registerClassName()] = $c->registerFolder();
				
				if($c->registerMenuEntry() != "")
					$this->menuEntries[$c->registerMenuEntry()] = $c->registerClassName();
				
				$this->appFolder[$c->registerClassName()] = $folder;
				
				if($c->registerName() != "noName")
					$this->classes[$c->registerName()] = $c->registerClassName();
					
				if($c->registerName() != "noName" AND $c->registerMenuEntryTarget() != "contentRight")
					$this->targets[$c->registerClassName()] = $c->registerMenuEntryTarget();
				
				$this->icons[$c->registerClassName()] = $c->registerIcon();
				
				if($c->registerPluginIsAdminOnly())
					$this->isAdminOnlyByPlugin[$c->registerClassName()] = $c->registerPluginIsAdminOnly();
				elseif(!$c->registerPluginIsAdminOnly() AND isset($this->isAdminOnlyByPlugin[$c->registerClassName()]))
					unset($this->isAdminOnlyByPlugin[$c->registerClassName()]);


				if($c->registerDependencies() != "none")
					$this->deps[$c->registerClassName()] = $c->registerDependencies();
					
				$this->versions[$c->registerClassName()] = $c->registerVersion();
				
				if($c->registerJavascriptFile() != "" AND isset($_SESSION["JS"])){
					if(is_array($c->registerJavascriptFile())){
						foreach($c->registerJavascriptFile() AS $v)
							JSLoader::addScriptS($v,$c->registerFolder(), $c->registerClassName(), $folder);
					} else
						JSLoader::addScriptS($c->registerJavascriptFile(),$c->registerFolder(), $c->registerClassName(), $folder);
				}
				#if(method_exists($c, "registerUseGenericClasses"))
				#	$this->isGeneric[$c->registerName()] = $c->registerUseGenericClasses();
					
				#if(isset($this->isGeneric[$c->registerName()]) AND $this->isGeneric[$c->registerName()]){
				#	$this->collectors[$c->registerClassName()] = $c->registerName();
				#	$this->genericPlugins[$c->registerName()] = $c;
				#}
					
				$n = $c->registerClassName();
				if($n != "" AND $appFolder == null){
					try {
						$nc = new $n();
						if(method_exists($nc,'getCollectionOf')){
							if(!isset($this->collectors[$c->registerClassName()]))
								$this->collectors[$c->registerClassName()] = $nc->getCollectionOf();
						}
					} catch (ClassNotFoundException $e) {
						if($n != "") {
							try {
								$n = $n."GUI";
								$nc = new $n();
								if(method_exists($nc,'getCollectionOf'))
									if(!isset($this->collectors[$c->registerClassName()]))
										$this->collectors[$c->registerClassName()] = $nc->getCollectionOf();
										
							} catch(ClassNotFoundException $e2){
								
							}
						}
					}
				}
				
				if($f[1] == "xml"){
					$fld = $c->registerFolder();
					if(!is_array($fld))
						$fld = array($fld);
					
					foreach($fld AS $folderName){
						$path = "./$folder/$folderName/".$c->registerClassName().".class.php";
						if(file_exists($path))
						require_once $path;
						elseif(file_exists(".".$path)) require_once ".".$path;
					}
				}
				
				if($appFolder == null) $c->doSomethingElse();
				
				$_SESSION["messages"]->endMessage(" successful");
				unset($c);
			}
		} else $_SESSION["messages"]->endMessage("not found");

	}
	
	public function getFolders(){
		return $this->folders;
	}
	
	public function getMenuEntries(){
		$entries = $this->menuEntries;
		$hidden = Environment::getS("hiddenPlugins", array());
		
		foreach($entries as $key => $value){
			#print_r($this->menuEntries);
			if(in_array($value, $hidden))
				unset($entries[$key]);
			
			if(isset($this->blacklist[$value]))
				unset($entries[$key]);
			#$t = ((!isset($this->classes[$key]) OR !isset($this->isAdminOnlyByPlugin[$this->classes[$key]])) ? 
			#	0 : $this->isAdminOnlyByPlugin[$this->classes[$key]]);
			$t = 0;
			if(isset($this->classes[$key]) AND isset($this->isAdminOnlyByPlugin[$this->classes[$key]])) $t = 1;
			if(isset($this->isAdminOnlyByPlugin[$key])) $t = 1;
			if(isset($this->isAdminOnlyByPlugin[$value])) $t = 1;
			
			if($t != $_SESSION["S"]->isUserAdmin()) unset($entries[$key]);
		}
		return $entries;
	}
	
	public function getAllMenuEntries(){
		return $this->menuEntries;
	}

	public function getMenuTargets(){
		return $this->targets;
	}
	
	public function addAdminOnly($plugin){
		$this->isAdminOnlyByPlugin[$plugin] = true;
	}
	
	public function getIsAdminOnly($plugin){
		$plugin = str_replace("GUI", "", $plugin);
		
		if(isset($this->isAdminOnlyByPlugin[$plugin])) return $this->isAdminOnlyByPlugin[$plugin];

		$c = array();
		foreach($this->collectors AS $k => $v)
			$c[$v] = $k;
		
		#$c = array_flip($this->collectors); //deprecated

		if(!isset($c[$plugin])) return false;

		if(isset($this->isAdminOnlyByPlugin[$c[$plugin]])) return $this->isAdminOnlyByPlugin[$c[$plugin]];

		return false;
	}
	
	public function getIcons(){
		return $this->icons;
	}
	
	public function isPluginLoaded($pluginName){
		if(isset($this->blacklist[$pluginName]))
			return false;
		
		return in_array($pluginName,$_SESSION["CurrentAppPlugins"]->getAllPlugins());
	}
	
	public function getAllPlugins(){
		return $this->classes;
	}
	
	public function addClass($name, $class){
		$this->classes[$name] = $class;
	}
	
	public function isPluginGeneric($pluginClassName){
		$i = 1;
		if(strlen($pluginClassName) > 0 AND $pluginClassName[0] == "m") {
			$i = 0;
			$pluginClassName = substr($pluginClassName, 1);
		}
		return isset($this->isGeneric[$pluginClassName][$i]) ? $this->isGeneric[$pluginClassName][$i] : false;
		
	}
	
	public function getFolderOfPlugin($pluginClassName){
		if(is_array($this->pluginToFolder[$pluginClassName]))
			return $this->pluginToFolder[$pluginClassName][0];
			
		return $this->pluginToFolder[$pluginClassName];
	}
	
	public function getAppFolderOfPlugin($pluginClassName){
		return $this->appFolder[$pluginClassName];
	}
	
	public function getDepsOfPlugin($pluginClassName){
		return isset($this->deps[$pluginClassName]) ? $this->deps[$pluginClassName] : "none";
	}
	
	public function isCollectionOf($plugin){
		return array_search($plugin, $this->collectors);
	}

	public function getCollectionGUI($pluginClassName){
		if(isset($this->genericPlugins[$pluginClassName])) 
			return $this->genericPlugins[$pluginClassName]->getCollectionGUI();
		
		return false;
	}
	
	public function isCollectionOfFlip($plugin){
		return (isset($this->collectors[$plugin]) ? $this->collectors[$plugin] : "");#array_search($plugin, array_flip());
	}
	
	public function getVersionOfPlugin($plugin){
		return $this->versions[$plugin];
	}
}
?>