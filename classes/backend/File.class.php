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

class File extends PersistentObject {
	public function __construct($ID){
		$this->storage = "File";
		parent::__construct($ID);
	}
	
	public function newAttributes(){
		$A = new stdClass();
		$A->FileDir = "";
		$A->FileName = "";
		$A->FileSize = 0;
		$A->FileMimetype = "";
		$A->FileIsDir = "";
		$A->FileIsWritable = false;
		$A->FileIsReadable = false;
		$A->FileCreationDate = 0;
		
		return $A;
	}
	
	function getRelPath(){
		$Path = $this->A->FileDir."/".$this->A->FileName;
		$pAF = Util::getRootPath();#str_replace("interface","",dirname($_SERVER["PHP_SELF"]));
		return str_replace($pAF,"./",substr($Path, strpos($Path,$pAF)));
	}
	
	public function download(){
		$this->loadMe();
		if(strpos($this->ID, "specifics") === false) return;
		
		if(strpos(strtolower($this->ID), ".pdf") !== false) 
			header("Content-Type: application/pdf");
		
		if(strpos(strtolower($this->ID), ".jpg") !== false) 
			header("Content-Type: image/jpg");
		
		if(strpos(strtolower($this->ID), ".png") !== false) 
			header("Content-Type: image/png");
		
		if(strpos(strtolower($this->ID), ".gif") !== false) 
			header("Content-Type: image/gif");
		
		header("Content-Disposition: attachment; filename=\"".basename($this->ID)."\"");
		
		readfile($this->ID);
	}

	protected function moveToDir($toDir){
		$newName = str_replace(DIRECTORY_SEPARATOR, "/", realpath($toDir))."/".basename($this->getID());

		if(file_exists($newName))
			throw new Exception("Eine Datei mit diesem Namen existiert bereits!");
		
		if(rename($this->getID(), $newName)){
			Datei::updatePath($this->getID(), $newName);
			return $newName;
		}

		return false;
	}

	protected function rename($newName){
		$newName = dirname($this->getID())."/".basename($newName);

		if(file_exists($newName))
			throw new Exception("Eine Datei mit diesem Namen existiert bereits!");
		
		if(rename($this->getID(), $newName)){
			Datei::updatePath($this->getID(), $newName);
			return $newName;
		}

		return false;
	}

	public function makeUpload($A){
		if($A->FileIsDir == "1")
			return;

		$maxSize = Util::getMaxUpload();

		if(!isset($_FILES['qqfile'])){ //XHR upload for good browsers
			if($_SERVER["CONTENT_LENGTH"] > $maxSize)
				die("{\"error\":\"Die angegebene Datei ist zu groß\"}");

			$A->FileContent = addslashes(file_get_contents("php://input"));
			$A->FileName = $_GET['qqfile'];
			$A->FileDir = preg_replace("/^([A-Z])%/", "\\1:", $_GET["path"]);
			$A->FileSize = (int) $_SERVER["CONTENT_LENGTH"];
		} else { //iframe upload for IE8
			if($_FILES['qqfile']['size'] > $maxSize)
				die("{\"error\":\"Die angegebene Datei ist zu groß\"}");


			$A->FileContent = addslashes(file_get_contents($_FILES['qqfile']['tmp_name']));
			$A->FileName = $_FILES['qqfile']['name'];
			$A->FileDir = preg_replace("/^([A-Z])%/", "\\1:", $_GET["path"]);
			$A->FileSize = $_FILES['qqfile']['size'];
		}

		echo "{\"success\":true}";
	}

	public function newMe($checkUserData = true, $output = false) {
		parent::newMe($checkUserData, false);
	}
}
?>