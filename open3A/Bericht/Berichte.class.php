<?php
/*
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
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;
class Berichte extends UnpersistentClass implements iPluginSpecificRestrictions {
	protected function getFiles(){
				
		$FB = new FileBrowser();
		$FB->addDir(Util::getRootPath()."open3A/Bericht");
		$FB->addDir(Util::getRootPath()."specifics");
		$FB->addDir(FileStorage::getFilesDir());

		while($return = Registry::callNext("Bericht", "directory"))
			$FB->addDir($return);
		
		$BD = new File(str_replace("open3A".DIRECTORY_SEPARATOR."Bericht","",dirname(__FILE__)).$_SESSION["applications"]->getActiveApplication()."/Customizer");
		$BD->loadMe();

		if($BD->getA() != null)
			$FB->addDir($BD->getID());

		if(isset($_SESSION["berichteOrdner"]) AND count($_SESSION["berichteOrdner"]) > 0)
			foreach($_SESSION["berichteOrdner"] AS $k => $v)
				$FB->addDir($v);
		$files = $FB->getAsLabeledArray("iBerichtDescriptor",".class.php",true, true);
		
		return $files;
	}
	
	private static $restrictions;
	
	public function getPluginSpecificRestrictions() {
		if(self::$restrictions != null)
			return self::$restrictions;
		
		$a = array();
		#echo "<pre>";
		#print_r($this->getFiles());
		
		#echo "</pre>";
		#return array();
		
		foreach($this->getFiles() AS $file){
			try {
				$file = $file[0];
				$c = new $file();
				$a["pluginSpecificH". str_replace("Bericht_", "", $file)] = $c->getLabel()." ausblenden";
			} catch (Exception $ex) {

			}
			
			
		}
		self::$restrictions = $a;
		
		return self::$restrictions;
	}
	
	public static function getTextbausteineData(){
		return array("501", "E-Mail Bericht", array("Anrede"));
	}
	
}
?>