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
class Applications {
	private $apps = array();
	private $icons = array();
	private $activeApp = "nil";
	private $versions = array();

	private static $sessionVariable = "applications";

	public static function init(){
		$_SESSION[self::$sessionVariable] = new Applications();
	}
	
	/**
	 * @return Applications 
	 */
	public static function i(){
		return $_SESSION[self::$sessionVariable];
	}
	
	public function __construct() {
		$this->scanApplications();
	}

	public function scanApplications(){
		$_SESSION["messages"]->startMessage("checking for directory ./applications/: ");
		
		if(is_dir(Util::getRootPath()."applications/")){
			$_SESSION["messages"]->endMessage("found");
			$apps = array();
			$fp = opendir(Util::getRootPath()."applications/");
			while(($file = readdir($fp)) !== false) {
				if(strpos($file, "Application") === false) continue;
				
				$apps[] = $file;
			}
			sort($apps);
				
			$allowedApplications = Environment::getS("allowedApplications", null);

			foreach($apps as $key => $file){
				
				require Util::getRootPath()."applications/$file";
				
				$f = explode(".",$file);
				if($f[0]{0} == "-") continue;
				
				$_SESSION["messages"]->startMessage("trying to register application $f[0]: ");
				$f = $f[0];
				$c = new $f;
				if($allowedApplications != null AND !in_array($c->registerName(), $allowedApplications))
					continue;
				
				$this->apps[$c->registerName()] = $c->registerFolder();
				
				if(method_exists($c,"registerIcon"))
					$this->icons[$c->registerName()] = $c->registerIcon();
				
				if(method_exists($c,"registerVersion"))
					$this->versions[$c->registerName()] = $c->registerVersion();
				
				$_SESSION["messages"]->endMessage("loaded");
				unset($c);
			}
		} else $_SESSION["messages"]->endMessage("not found");

		
		foreach($this->apps AS $name => $folder){
			$newName = Environment::getS("renameApplication:$name", $name);
			if($name != $newName){
				$this->apps[$newName] = $folder;
				unset($this->apps[$name]);

				if(isset($this->icons[$name])){
					$this->icons[$newName] = $this->icons[$name];
					unset($this->icons[$name]);
				}

				if(isset($this->versions[$name])){
					$this->versions[$newName] = $this->versions[$name];
					unset($this->versions[$name]);
				}
			}
		}
	}

	public static function isAppLoaded($App){
		return in_array($App, self::getList());
	}
	
	public static function getList(){
		return $_SESSION[self::$sessionVariable]->getApplicationsList();
	}

	public static function activeApplication(){
		return $_SESSION[self::$sessionVariable]->getActiveApplication();
	}
	
	public static function activeVersion(){
		return $_SESSION[self::$sessionVariable]->getRunningVersion();
	}

	public function getApplicationsList(){
		return $this->apps;
	}
	
	public function numAppsLoaded(){
		return count($this->apps);
	}
	
	public function setActiveApplication($appName){
		$this->activeApp = $appName;
	}
	
	public function getActiveApplication(){
		return $this->activeApp;
	}
	
	public function getApplicationIcon($appName){
		if(array_search($appName,$this->apps) !== false) $appName = array_search($appName,$this->apps);
		return isset($this->icons[$appName]) ? $this->icons[$appName] : "";
	}
	
	public function getRunningVersion(){

		$appCheck = $this->activeApp;
		if(array_search($appCheck,$this->apps) !== false) $appCheck = array_search($appCheck,$this->apps);

		return isset($this->versions[$appCheck]) ? $this->versions[$appCheck] : null;
	}
	
	public function getHTMLOptions($selected = null){
		$o = "";
		foreach($this->apps as $key => $value)
			$o .= "<option ".(($selected != null AND $selected == $value) ? "selected=\"selected\"" : "")." value=\"$value\">$key</option>";
		return $o;
	}
	
	public function getGDL(){
		$o = "";
		foreach($this->apps as $key => $value)
			$o .= "
			<app value=\"$value\">$key</app>";
		return $o;
	}
}
?>