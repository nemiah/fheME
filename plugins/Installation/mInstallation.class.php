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
class mInstallation extends anyC {
	private $folder = "./system/DBData/";
	
	function __construct() {
		$this->collectionOf = "Installation";
		$this->storage = "phpFileDB";
	}
	
	public function switchDBToMySQLo(){
		file_put_contents(Util::getRootPath()."system/connect.php", str_replace("\n#define(\"PHYNX_MAIN_STORAGE\",\"MySQLo\");\n", "\ndefine(\"PHYNX_MAIN_STORAGE\",\"MySQLo\");\n", file_get_contents(Util::getRootPath()."system/connect.php")));
	
		#$DB = new DBStorageU();
	}

	public function setupAllTables($echo = 0){
		$apps = Applications::getList();
		$apps["plugins"] = "plugins";
		#$apps["plugins"] = "ubiquitous";

		$currentPlugins = $_SESSION["CurrentAppPlugins"];
		
		$return = array();
		foreach($apps AS $app){
			$AP = $_SESSION["CurrentAppPlugins"] = new AppPlugins($app);
			$AP->scanPlugins("plugins");
			$p = array_flip($AP->getAllPlugins());
			Applications::i()->setActiveApplication($app); //or the autoloader won't work
			
			$return[$app] = "<b>Start</b>";
			
			#$p = array_flip(AppPlugins::i()->getAllPlugins());
			foreach($p as $key => $value){
				if($key == "CIs") continue;
				
				$status = "initialized...";
				
				try {
					$c = new $key();
					$status = "instantialized $key...";
				} catch (ClassNotFoundException $e){
					$key2 = $key."GUI";
					$status = "instantialized {$key}GUI...";

					try {
						$c = new $key2();
					} catch (ClassNotFoundException $e2){
						$return[$key] = "<span style=\"color:red;\">Class ".$e2->getClassName()." not found!</span>";
						continue;
					}
				}

				$return[$key] = $status;

				if($c->checkIfMyDBFileExists()){
					/*$return[$value] = */$c->createMyTable(true);
				}
			}
		}
		
		mUserdata::setUserdataS("DBVersion", Phynx::build(), "", -1);
		$_SESSION["CurrentAppPlugins"] = $currentPlugins;
		
		return $return;
	}
	
	public function updateAllTables(){
		$apps = Applications::getList();
		$apps["plugins"] = "plugins";
		#$apps["plugins"] = "ubiquitous";

		$currentPlugins = $_SESSION["CurrentAppPlugins"];
		
		$return = array();
		foreach($apps AS $app){
			$AP = $_SESSION["CurrentAppPlugins"] = new AppPlugins($app);
			$AP->scanPlugins("plugins");
			$p = array_flip($AP->getAllPlugins());
			#Applications::i()->setActiveApplication($app); //or the autoloader won't work; yes, it does because of addClassPath later on
		
			foreach($p as $key => $value){
				if($key == "CIs") continue;
				
				$return[$value] = "Keine Collection-Klasse!";
				
				addClassPath(Util::getRootPath().$app."/".$AP->getFolderOfPlugin($key)."/");
				
				try {
					$c = new $key();
				} catch (ClassNotFoundException $e){
					$key2 = $key."GUI";

					try {
						$c = new $key2();
					} catch (ClassNotFoundException $e2){
						continue;
					}
				}

				if(!$c->checkIfMyDBFileExists())
					$return[$value] = "Keine DB-Datei!";
				
				if($c->checkIfMyTableExists() AND $c->checkIfMyDBFileExists())
					$return[$value] = $c->checkMyTables(true);
				
				if(!$c->checkIfMyTableExists() AND $c->checkIfMyDBFileExists())
					$return[$value] = $c->createMyTable(true);
				
			}
		}
		
		mUserdata::setUserdataS("DBVersion", Phynx::build(), "", -1);
		$_SESSION["CurrentAppPlugins"] = $currentPlugins;
		return $return;
	}
	
	function changeFolder($newFolder){
		$this->folder = $newFolder;
	}
	
	function loadAdapter(){
		parent::loadAdapter();
		if(is_file($this->folder."Installation.pfdb.php")) $this->Adapter->setDBFolder($this->folder);
		else $this->Adapter->setDBFolder(".".$this->folder);
	}
}
?>
