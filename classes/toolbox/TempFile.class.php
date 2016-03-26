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

class TempFile {
	
	protected $Attributes;
	protected $ID;
	
	function getID(){
		return $this->ID;
	}
	
	function setID($ID){
		$this->ID = $ID;
	}
	
	function setA($A){
		$this->Attributes = $A;
	}
	
	function newAttributes(){
		$A = new stdClass();
		$A->filename = "";
		$A->filetype = "";
		$A->filesize = 0;
		$A->originalFilename = "";
		
		return $A;
	}
	
	function loadMe(){
		$this->Attributes = $this->newAttributes();
	}
	
	function getA(){
		return $this->Attributes;
	}

	function A($attributeName){
		return $this->Attributes->$attributeName;
	}

	function AA(){
	}
	
	function saveMe(){
		#echo $this->Attributes->data;
	}
	
	function deleteMe(){
		if(file_exists($this->Attributes->filename))
			unlink($this->Attributes->filename);
		
		$_SESSION["TempFiles"]->remove($this->ID);
	}
	
	function __construct($id = "-1"){
		if($id != "-1"){
			$this->Attributes = $_SESSION["TempFiles"]->get($id)->getA();
			$this->ID = $id;
		}
	}
	/*
	public static function getTempFilename(){
		return tempnam("/tmp/","TempFile_".rand()."_");
	}*/
	
	private $handle;
	function parse($null, $textSep, $sep, $codepage, $startLine = null, $endLine = null){
		ini_set("auto_detect_line_endings", true);
		
		$makeUTF = false;
		if($codepage != "UTF-8")
			$makeUTF = true;

		if(!$this->handle)
			$this->handle = fopen($this->Attributes->filename, "r");
		   
		$line = array();
		
		$i = 0;
		while($data = fgetcsv($this->handle, 0, $sep, $textSep)) {
			if($i < $startLine){
				$i++;
				continue;
			}
			
			if($i >= $endLine)
				break;
			
			if($makeUTF)
				foreach($data AS $k => $v)
					$data[$k] = utf8_encode($v);
					
			$line[] = $data;
			
			$i++;
		}
		fclose($this->handle);
		
		return $line;
	}
	
	public function getLine($textSep, $sep, $codepage){
		ini_set("auto_detect_line_endings", true);
		
		$makeUTF = false;
		if($codepage != "UTF-8")
			$makeUTF = true;

		if(!$this->handle)
			$this->handle = fopen($this->Attributes->filename, "r");
		   
		$line = array();
		
		$data = fgetcsv($this->handle, 0, $sep, $textSep);
		
		if($data === false)
			return false;
		
		if($makeUTF)
			foreach($data AS $k => $v)
				$data[$k] = utf8_encode($v);
		
		return $data;
		
		#while(() !== FALSE) {
			
					
		#	$line[] = $data;
		#}
		#fclose($this->handle);
		
		return $line;
	}

	public function makeUpload($A, $quiet = false){
		$maxSize = Util::getMaxUpload();

		if(!isset($_FILES['qqfile'])){ //XHR upload for good browsers
			if($_SERVER["CONTENT_LENGTH"] > $maxSize)
				die("{\"error\":\"Die angegebene Datei ist zu groß\"}");

			$input = fopen("php://input", "r");
			$newFilename = Util::getTempFilename($_GET['qqfile'],"tmp");
			$output = fopen($newFilename, "w");

			stream_copy_to_stream($input, $output);

			fclose($input);
			fclose($output);

			$A->filesize = (int) $_SERVER["CONTENT_LENGTH"];
			$A->filetype = "";
			$A->originalFilename = $_GET['qqfile'];
		} else { //iframe upload for IE8
			if($_FILES['qqfile']['size'] > $maxSize)
				die("{\"error\":\"Die angegebene Datei ist zu groß\"}");

			$newFilename = Util::getTempFilename($_FILES['qqfile']['name'],"tmp");
			move_uploaded_file($_FILES['qqfile']['tmp_name'], $newFilename);

			#if($_FILES['datei']['size'] > 0 AND $fi != "") $A->$fi = $_FILES['datei']['type'].":::".$_FILES['datei']['size'].":::".base64_encode($content);
			#$A->filename = $newFilename;
			$A->filesize = $_FILES['qqfile']['size'];
			$A->filetype = $_FILES['qqfile']['type'];
			$A->originalFilename = $_FILES['qqfile']['name'];
		}
		$A->filename = $newFilename;

		if(!isset($_SESSION["TempFiles"])) $_SESSION["TempFiles"] = new ArrayCollection();
		$_SESSION["TempFiles"]->add($this);

		if(!$quiet)
			echo "{\"success\":true}";
		return true;
	}

	public function newMe(){
		
	}
}
?>