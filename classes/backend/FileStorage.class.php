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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class FileStorage {
	protected $instance;
	protected $connection;
	private $parsers;
	protected $affectedRowsOnly = false;
	
	protected $dir;
	protected $forceDir = false;

	function __construct(){
		
	}
	
	public function setParser($p){
		$this->parsers = $p;
	}
	
	static function createTable($CIA){
		$Ad = new Adapter(-1, PHYNX_MAIN_STORAGE);
		$DB = $Ad->getConnection();
		$DB->createTable($CIA);
	}
	
	public function setDBFolder($dir, $forceDir = false){
		$this->dir = $dir;
		$this->forceDir = $forceDir;
	}
	
	static function checkForTable($name){
		$Ad = new Adapter(-1, PHYNX_MAIN_STORAGE);
		$DB = $Ad->getConnection();
		return $DB->checkForTable("Datei");
	}
	
	function checkMyTable($CIA){
		$Ad = new Adapter(-1, PHYNX_MAIN_STORAGE);
		$DB = $Ad->getConnection();
		return $DB->checkMyTable($CIA);
	}
	
	public function setGetAffectedRowsOnly($bool){
		$this->affectedRowsOnly = $bool;
	}
	
	function loadSingle2($table, $id) {
		return $this->getFileClass($id, is_dir($id) ? 1 : 0)->getA();
	}
	
	private function getFileClass($file, $isDir){
		$file = str_replace(DIRECTORY_SEPARATOR, "/", $file);
		$F = new File($file);
		if(!file_exists($file)) $A = null;
		else {
			$A = $F->newAttributes();

			$ex = explode("/", $file);
			
			$A->FileDir = dirname(realpath($file));
			$A->FileName = $ex[count($ex) - 1];
			$A->FileIsDir = $isDir;
			$A->FileSize = filesize($file);
			if(is_readable($file) AND $isDir == "0" AND function_exists("mime_content_type")) $A->FileMimetype = mime_content_type($file);
			$A->FileIsWritable = is_writable($file);
			$A->FileIsReadable = is_readable($file);
			$A->FileCreationDate = filectime($file);
		}
		
		$F->setA($A);
		return $F;
	}
	
	public static function getFilesDir(){
		$path = realpath(Util::getRootPath())."/specifics/";
		
		if(!file_exists($path.".htaccess") AND is_writable($path))
			file_put_contents($path.".htaccess", "<IfModule mod_authz_core.c>
    Require all denied
</IfModule>

<IfModule !mod_authz_core.c>
    Order Allow,Deny
    Deny from all
</IfModule>");
		
		$CH = Util::getCloudHost();
		if($CH != null){
			Environment::load();
			$cloudUser = strtolower(Environment::$currentEnvironment->cloudUser());
			$dir = $CH->scientiaDir."/".($cloudUser != "" ? "$cloudUser/" : "")."specifics/";
			
			if(!file_exists($dir)){
				#mkdir($CH->scientiaDir."/".strtolower(Environment::$currentEnvironment->cloudUser())."/");
				mkdir($dir, 0777, true);
				chmod($dir, 0777);
			}
			
			return realpath($dir)."/";
		}
		
		return $path;
	}
	
	public static function getElementDir($class, $id){
		return FileStorage::getFilesDir().$class."ID".str_pad($id, 4, "0", STR_PAD_LEFT);
	}
	
	function loadMultipleV4(SelectStatement $statement){
		$collector = array();
		
		$dir = realpath($this->dir);
		$dir .= "/";
		
		if(strpos($dir,"specifics") === false AND !$this->forceDir) $dir = self::getFilesDir();#realpath("../specifics")."/";
		
		$dirs = array();
		$files = array();
		
		$dirIt = new DirectoryIterator($dir);
		foreach ($dirIt as $fileObj) {
			if($fileObj->isDot())
				continue;
			
			$file = $fileObj->getFilename();
			
			if(strpos(basename($file), "NewsletterID") === 0)
				continue;

			if(strpos(basename($file), "ProjektID") === 0)
				continue;

			if(strpos(basename($file), "WAdresseID") === 0)
				continue;

			if(strpos(basename($file), "VertragID") === 0)
				continue;

			if(strpos(basename($file), "FITCRMID") === 0)
				continue;

			if(strpos(basename($file), "GRLBMID") === 0)
				continue;

			if(strpos(basename($file), "LieferantID") === 0)
				continue;

			if(strpos(basename($file), "DBMailVorlageID") === 0)
				continue;

			if(strpos(basename($file), "VertragBelegEinmalID") === 0)
				continue;

			if(strpos(basename($file), "MailArchive") === 0)
				continue;

			if(strpos(basename($file), "MailTemp") === 0)
				continue;

			if(strpos(basename($file), "pPArbeitskraftID") === 0)
				continue;

			if(strpos(basename($file), "NewsletterEmbID") === 0)
				continue;

			if(strpos(basename($file), "EingangsbelegID") === 0)
				continue;

			if(strpos(basename($file), "ArtikelID") === 0)
				continue;

			if(strpos(basename($file), "PersonalID") === 0)
				continue;

			if(strpos(basename($file), "GRLBMPool") === 0)
				continue;

			if(strpos(basename($file), "Customizer") === 0 AND strpos(basename($file), ".class.php") !== false)
				continue;

			if(strpos(basename($file), ".lock") !== false)
				continue;

			if($fileObj->isDir())
				$dirs[] = $dir.$file;
			else 
				$files[] = $dir.$file;
		}
		
		#$fp = opendir($dir);
		#while(($file = readdir($fp)) !== false) {
			#if($file == ".") continue;
		#}
		#closedir($fp);
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			return count($dirs) + count($files);
		}
		
		sort($dirs);
		sort($files);
		
		$start = 0;
		$end = 0;
		
		if(count($statement->limit) > 0) list($start, $end) = explode(",", $statement->limit[0]);

		$c = 0;
		
		for($i = 0;$i < count($dirs); $i++){
			$c++;
			if($c < $start+1) continue;
			if($start + $end < $c AND $end != 0) break;
			
			$collector[] = $this->getFileClass($dirs[$i], 1);
		}
		
		for($i = 0;$i < count($files); $i++){
			$c++;
			if($c < $start+1) continue;
			if($start + $end < $c AND $end != 0) break;
			
			$collector[] = $this->getFileClass($files[$i], 0);
		}
		#echo "<pre>";
		#print_r($collector);
		#echo "</pre>";
		return $collector;
	}
	

	function deleteSingle($table, $keyName, $id){
		if(strpos(realpath($id), realpath(FileStorage::getFilesDir())) === false)
			Red::errorD("Die Datei kann wegen fehlender Berechtigung nicht gelöscht werden!");
		
		if(is_dir($id)) {
			$fp = opendir($id);
			$i = 0;
			while(($file = readdir($fp)) !== false) {
				if($file == ".") continue;
				if($file == "..") continue;
				
				$i++;
			}
			closedir($fp);
			if($i > 0) Red::errorD("Das Verzeichnis kann nicht gelöscht werden, es ist nicht leer!");
			
			rmdir($id);
		}
		else {
			if(!is_writable($id))
				Red::errorD("Die Datei kann wegen fehlender Berechtigung nicht gelöscht werden!");
			
			unlink($id);
		}
	}
	
	function makeNewLine2($table, $A) {
		
		if($A->FileName == "") return;
		$file = $A->FileDir."/".basename($A->FileName);
		if($A->FileIsDir == "1")
			mkdir($file);
		else {
			#if(file_exists($file)) return;
			
			$h = fopen($file, "w+");
			fwrite($h, stripslashes($A->FileContent));
			fclose($h);
		}
	}
}

?>
