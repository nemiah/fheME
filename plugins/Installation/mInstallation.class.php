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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
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
		$return = array();
		if(file_exists(Util::getRootPath()."system/CI.pfdb.php")){
			$return["all"] = "Using fast setup mode...";
			
			$DBG = new DBStorage();
			$C = $DBG->getConnection();
			
			$DB = new PhpFileDB();
			$DB->setFolder(Util::getRootPath()."system/");
			$Q = $DB->pfdbQuery("SELECT * FROM CI");
			while($R = $DB->pfdbFetchAssoc($Q)){
				if(!trim($R["MySQL"]))
					continue;
				
				$CIA = new stdClass();
				$CIA->MySQL = $R["MySQL"];
				
				$DBG->createTable($CIA);
				
				$return[] = $R["MySQL"];
			}
			
			mUserdata::setUserdataS("DBVersion", Phynx::build(), "", -1);
			return $return;
		}
		
		$currentApp = Applications::activeApplication();
		$apps = Applications::getList();
		$apps["plugins"] = "plugins";
		#$apps["plugins"] = "ubiquitous";

		$currentPlugins = $_SESSION["CurrentAppPlugins"];
		
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
		Applications::i()->setActiveApplication($currentApp);
		return $return;
	}
	
	protected $updateExeptions = [];
	
	public function updateAllTables(){
		$apps = Applications::getList();
		$apps["plugins"] = "plugins";
		#$apps["plugins"] = "ubiquitous";

		$currentPlugins = $_SESSION["CurrentAppPlugins"];
		$done = array();
		
		$return = array();
		foreach($apps AS $app){
			$AP = $_SESSION["CurrentAppPlugins"] = new AppPlugins($app);
			$AP->scanPlugins("plugins");
			$p = array_flip($AP->getAllPlugins());
			#Applications::i()->setActiveApplication($app); //or the autoloader won't work; yes, it does because of addClassPath later on

			foreach($done AS $plugin)
				if(isset($p[$plugin]))
					unset($p[$plugin]);
			
			foreach($p as $key => $value){
				if($key == "CIs") continue;
				if($key == "mInstallation") continue;
				
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
				try {
					if(!$c->checkIfMyDBFileExists())
						$return[$value] = "<span style=\"color:grey;\">Nichts zu tun, keine DB-Datei!</span>";
					else {
						if($c->checkIfMyTableExists())
							$return[$value] = $c->checkMyTables(true);
						else
						#if(!$c->checkIfMyTableExists())
							$return[$value] = $c->createMyTable(true);
					}
					$done[] = $key;
				} catch (RowSizeTooLargeException $e){
					$this->updateExeptions[] = $e;
					$return[$value] = "<span style=\"color:red;\">".get_class($e).": ".get_class($c)." (".$e->getTable().", ".$e->getField().");\n".$e->getTraceAsString()."</span>";
					$done[] = $key;
				} catch (Exception $e){
					$this->updateExeptions[] = $e;
					$return[$value] = "<span style=\"color:red;\">".get_class($e).": ".get_class($c).", ".$e->getMessage().";\n".$e->getTraceAsString()."</span>";
					$done[] = $key;
				}
			}
		}
		#($return);
		
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
	
	public static function getCronjobData(){
		return array("Installation",
			array("/plugins/Installation/backup.php", "00 6 * * *", "php", "Führt die Datensicherung aus"));
	}
}
?>