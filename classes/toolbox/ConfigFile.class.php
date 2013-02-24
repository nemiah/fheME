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

class ConfigFile {
	private $fileName;
	
	protected $default = "";
	
	private $objects = array();
	
	function __construct($fileName){
		$this->fileName = $fileName;
	}
	
	public function addObject($object){
		$this->objects[] = $object;
	}
	
	public function addLines($string){
		$this->default = $string;
	}
	
	protected function getString($class){
		if($this->default != "") return "";
		return "Please overwrite the Method getString!<br />";
	}
	
	function write(){
		
		$F = new File($this->fileName);
		$F->loadMe();
		if(!$F->getA()->FileIsWritable) return false;
		
		$fp = fopen($this->fileName, 'w');
		fwrite($fp, $this->getContent());
		fclose($fp);
		
		return true;
	}
	
	function getContent(){
		$s = "";
		foreach($this->objects as $k => $v)
			$s .= $this->getString($v);
			
		return $this->default.$s;
	}
	
	function __toString(){
		$c = $this->getContent();
		$t = split("\n",$c);
		foreach($t as $k => $v)
			$t[$k] = str_pad(($k + 1),5, " ", STR_PAD_LEFT).": ";
		
		return Util::getBasicHTML("<pre class=\"backgroundColor2\" style=\"font-size:9px;float:left;\">".implode("\n",$t)."</pre><pre class=\"backgroundColor0\" style=\"font-size:9px;margin-left:40px;\">".$c."</pre>","Preview");
	}
}
?>