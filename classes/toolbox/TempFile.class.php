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
	
	function parse($newLine, $textSep, $sep, $codepage){
		
		#echo "Speicher start 1: ".Util::formatByte(memory_get_usage(true),2)."<br />";
		
		/*$win = false;
		if($newLine == "Windows") {
			$newLine = "\r\n";
			$win = true;
		}
		if($newLine == "Unix")
			$newLine = "\n";*/
		
		ini_set("auto_detect_line_endings", true);
		
		$makeUTF = false;
		if($codepage != "UTF-8")
			$makeUTF = true;

		$handle = fopen($this->Attributes->filename, "r");
		#$bugger = "";

		#while (!feof($handle))
		 #   $bugger .= fgets($handle, 4096);
		    
		#fclose ($handle);
		#echo "Speicher start 3: ".Util::formatByte(memory_get_usage(true),2)."<br />";
		#$bugger = trim($bugger);
		
		/*if(strpos($bugger, $newLine) === false AND $win){
			echo "Keine Windows-Zeilenumbrüche gefunden, verwende Unix!";
			$newLine = "\n";
			$win = false;
		}*/
		   
		$line = array();
		#$row = 1;
		
		while(($data = fgetcsv($handle, 0, $sep, $textSep)) !== FALSE) {
			
			if($makeUTF)
				foreach($data AS $k => $v)
					$data[$k] = utf8_encode ($v);
					
			$line[] = $data;
		}
		fclose($handle);
		
		return $line;
		/*$line = array();
		$line[0] = array();
		$line[0][0] = "";
		$mode = "outside";
		for($i=0;$i < strlen($bugger);$i++){
			#if($i % 10000 == 0) echo "Speicher $i: ".Util::formatByte(memory_get_usage(true),2)."<br />";
			
			if($i < strlen($bugger)-1)
				$winLine = $bugger[$i].$bugger[$i+1];
			else $winLine = "";
			
			if($mode == "outside" AND $bugger[$i] == $textSep){
				$mode = "inside";
				continue;
			}
			if($mode == "outside" AND ($bugger[$i] == $newLine OR $winLine == $newLine)){
				$line[] = array();
				$line[count($line) - 1][0] = "";
				continue;
			}
			if($mode == "outside" AND $bugger[$i] == $sep){
				$line[count($line) - 1][] = "";
				continue;
			}
			if($mode == "inside" AND $bugger[$i] == $textSep AND $bugger[$i-1] != "\\"){
				$mode = "outside";
				continue;
			}
			if($mode == "inside"){
				$current = count($line) - 1;
				$line[$current][count($line[$current]) - 1] .= $makeUTF ? utf8_encode($bugger[$i]) : $bugger[$i];
			}
		}
		#echo "Speicher ende: ".Util::formatByte(memory_get_usage(true),2)."<br />";
		return $line;*/
	}

	public function makeUpload($A, $quiet = false){
		$maxSize = Util::getMaxUpload();

		if(!isset($_FILES['qqfile'])){ //XHR upload for good browsers
			if($_SERVER["CONTENT_LENGTH"] > $maxSize)
				die("{\"error\":\"Die angegebene Datei ist zu groß\"}");

			$input = fopen("php://input", "r");
			$newFilename = Util::getTempFilename($_GET['qqfile'],"tmp");
			$output = fopen($newFilename, "w");

			$realSize = stream_copy_to_stream($input, $output);

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