<?php
/*
 *  This file is part of phynx.

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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class HTMLTidy {
	protected $content;
	private $makeUTF8 = false;
	private $done = false;
	private $cleanedFile;
	private $errorsFile;

	function __construct($uri = null){
		if($uri != null)
			$this->content = file_get_contents($uri);
	}

	function setContent($text){
		$this->content = $text;
	}

	function makeUTF8(){
		$this->makeUTF8 = true;
	}

	function cleanUp(){

	}

	function tidy(){
		if($this->done) return;
		
		if($this->makeUTF8)
			$this->content = utf8_encode($this->content);

		$this->cleanUp();

		$temp = Util::getTempFilename(session_id(), "html");
		$this->cleanedFile = Util::getTempFilename(session_id(), "xhtml");
		$this->errorsFile = Util::getTempFilename(session_id()."_errors", "txt");
		file_put_contents($temp, $this->content);

		$SC = new SystemCommand();
		if(!Util::isWindowsHost())
			$SC->setCommand("tidy -asxhtml -numeric < $temp > $this->cleanedFile 2> $this->errorsFile");
		else
			$SC->setCommand("c:/tidy.exe -asxhtml -numeric < $temp > $this->cleanedFile");
		$SC->execute();
		#echo htmlentities(file_get_contents($this->errorsFile));
		$this->done = true;
	}

	function getCleaned(){
		$this->tidy();
		
		return file_get_contents($this->cleanedFile);
	}

	function removeTag($tag){
		while(stripos($this->content,"<$tag") > 0){
			$pos1 = stripos($this->content, "<$tag");
			$pos2 = stripos($this->content, "</$tag>", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen("</$tag>");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}

		while(stripos($this->content,"<$tag") > 0){
			$pos1 = stripos($this->content,"<$tag");
			$pos2 = stripos($this->content,">", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen(">");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}

		while(stripos($this->content,"<$tag") > 0){
			$pos1 = stripos($this->content,"<$tag");
			$pos2 = stripos($this->content,"/>", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen("/>");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}
	}

	function removeComments(){
		while(stripos($this->content,"<!--") > 0){
			$pos1 = stripos($this->content, "<!--");
			$pos2 = stripos($this->content, "-->", $pos1);
			if($pos2 === false) break;
			$len = $pos2 - $pos1 + strlen("-->");

			$x = substr($this->content, $pos1, $len);
			$this->content = str_replace($x, '', $this->content);
		}
	}
}
?>