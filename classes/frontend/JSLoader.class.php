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
class JSLoader {

	private $scripts = array();
	private $folders = array();
	private $blacklist = array();
	private $plugins = array();
	private $apps = array();

	private static $sessionVariable = "JS";
	
	public static function init(){
		$_SESSION[self::$sessionVariable] = new JSLoader();
	}
	
	function __construct(){ 
		$_SESSION["messages"]->addMessage(__CLASS__."-Singleton loaded");
	}
		
	public static function addScriptS($name, $folder, $plugin, $app){
		if(isset($_SESSION[self::$sessionVariable]))
			$_SESSION[self::$sessionVariable]->addScript($name, $folder, $plugin, $app);
	}
	
	public function addScript($name, $folder, $plugin, $app){
		if(in_array($name, $this->scripts) AND in_array($folder, $this->folders)) return;
		$this->scripts[] = $name;
		$this->folders[] = $folder;
		$this->plugins[] = $plugin;
		$this->apps[] = $app;
	}
	
	public function removeScript($plugin){
		$this->blacklist[] = $plugin;
	}
	
	public function getScripts(){
		$o = array();
		for($i=0;$i<count($this->plugins);$i++){
			if(!in_array($this->plugins[$i],$this->blacklist))
				$o[] = $this->scripts[$i];
		}
		return $o;
	}
	
	public function getFolders(){
		$o = array();
		for($i=0;$i<count($this->plugins);$i++){
			if(!in_array($this->plugins[$i],$this->blacklist))
				$o[] = $this->folders[$i];
		}
		return $o;
	}
	
	public function getApps(){
		$o = array();
		for($i=0;$i<count($this->plugins);$i++){
			if(!in_array($this->plugins[$i],$this->blacklist))
				$o[] = $this->apps[$i];
		}
		return $o;
	}
}
?>
