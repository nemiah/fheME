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
class Installation extends PersistentObject {
	private $folder = "./system/DBData/";
	
	public static function getMandanten($uniqueDB = false){
		$CH = Util::getCloudHost();
		$usedDB = array();
		$mandanten = array();
		if(file_exists(Util::getRootPath()."plugins/multiInstall/plugin.xml") AND ($CH == null OR get_class($CH) == "CloudHostAny")){
			list($folder) = Installation::getDBFolder();
			$MI = new mInstallation();
			$MI->changeFolder($folder);
			while($I = $MI->n()){
				if($I->A("httpHost") == "cloudData")
					continue;

				if(trim($I->A("httpHost")) == "")
					continue;
				
				if(trim($I->A("host")) == "")
					continue;
				
				if($uniqueDB AND isset($usedDB[$I->A("host").$I->A("datab")]))
					continue;
				
				$usedDB[$I->A("host").$I->A("datab")] = true;
				
				$mandanten[$I->getID()] = $I->A("httpHost");
			}
		}
		
		return $mandanten;
	}
	
	public static function getDBFolder($folder = null){
		$external = false;
		
		if(file_exists(Util::getRootPath()."../../phynxConfig")){
			$folder = Util::getRootPath()."../../phynxConfig/";
			$external = true;
		}
		
		if(file_exists(Util::getRootPath()."../phynxConfig")){
			$folder = Util::getRootPath()."../phynxConfig/";
			$external = true;
		}
		
		if($folder == null)
			$folder = Util::getRootPath()."system/DBData/";
		
		return array($folder, $external);
	}
	
	public function getA(){
		if($this->A == null) $this->loadMe();
		return $this->A;
	}

	public static function getReloadButton(){
		$B = new Button("Anwendung\nneu laden", "refresh");
		#$B->onclick("Installation.reloadApp();");
		$B->rmePCR("Util", -1, "reloadApplication", [], "function(){ location.reload(true); }");
		
		return $B;
	}

	function __construct($ID) {
		parent::__construct($ID);
		$this->storage = "phpFileDB";	
	}

	function saveMe($checkUserData = true, $output = false){
        parent::saveMe($checkUserData, false);
        
        #$_SESSION["DBData"] = $_SESSION["S"]->getDBData(null, $_SESSION["DBData"]["InstallationID"]);
		Session::reloadDBData();
		
		if(PHYNX_MAIN_STORAGE == "MySQL")
			$DB = new DBStorage();
		else
			$DB = new DBStorageU();
		
		Red::messageSaved();
	}
	
	
	function changeFolder($newFolder){
		$this->folder = $newFolder;
	}
	
	/*function loadAdapter(){
		parent::loadAdapter();
		#$this->Adapter->setStorage("SQLiteStorage");
		if(is_file("../system/DBData/ConData2.db")) $this->Adapter->setDBFile("../system/DBData/ConData2.db");
		else $this->Adapter->setDBFile("./system/DBData/ConData2.db");
	}

	function loadAdapter(){
		parent::loadAdapter();
		if(is_file("../system/DBData/Installation.pfdb.php")) $this->Adapter->setDBFolder("../system/DBData/");
		else $this->Adapter->setDBFolder("./system/DBData/");
	}*/
	
	function loadAdapter(){
		parent::loadAdapter();
		if(is_file($this->folder."Installation.pfdb.php")) $this->Adapter->setDBFolder($this->folder);
		else $this->Adapter->setDBFolder(".".$this->folder);
	}

	function makeNewInstallation(){
		if(trim($_SERVER["HTTP_HOST"]) == "")
			return;
		
		#$e = new Exception();
		#file_put_contents("/var/www/phynxConfig/log.txt", file_get_contents("/var/www/phynxConfig/log.txt")."\n\n".$e->getTraceAsString());
		
		$this->A = $this->newAttributes();
		$this->A->httpHost = $_SERVER["HTTP_HOST"];
		$_SESSION["messages"]->addMessage("Setting up new Installation on host ".$_SERVER["HTTP_HOST"]);
		$this->newMe();
		$this->forceReload();
	}
}
?>
