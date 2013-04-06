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
class FileBrowser {
	private $dirs = array();
	private $excludeExtensions = array();
	private $onlyExtensions = array();
	private $isRecursive = false;
	private $implementedInterfaces = array();
	
	private $parameter = "nil";
	private $foundFiles = array();
	
	public function addDir($dir){
		if($dir{strlen($dir) - 1} == "/") {
			$dir{strlen($dir) - 1} = " ";
			$dir = trim($dir);
		}
		
		$this->dirs[] = $dir;
	}
	
	public function addExcludedExtension($ext){
		if($ext{0} != ".") $ext = ".".$ext;
		$this->excludeExtensions[] = strtolower(trim($ext));
	}
	
	public function addOnlyExtension($ext){
		if($ext{0} != ".") $ext = ".".$ext;
		$this->onlyExtensions[] = $ext;
	}
	
	public function setRecursiveSearch($b){
		$this->isRecursive = $b;
	}
	
	public function addImplementedInterface($i, $extension){
		$this->implementedInterfaces[] = $i;
		$this->addOnlyExtension($extension);
	}
	
	private function searchFolder($dir){
		$fp = opendir($dir);
		if(!$fp)
			return;
		while(($file = readdir($fp)) !== false) {
			if(is_dir("$dir/$file") AND !$this->isRecursive) continue;
			elseif($this->isRecursive AND is_dir("$dir/$file") AND $file{0} != ".")
				$this->searchFolder("$dir/$file");

			$c = false;
			for($i = 0; $i < count($this->excludeExtensions);$i++)
				if(strtolower(substr($file,strlen($this->excludeExtensions[$i]) * -1)) == $this->excludeExtensions[$i]) $c = true;
			if($c) continue;
			
			$c = false;
			for($i = 0; $i < count($this->onlyExtensions);$i++)
				if(strtolower(substr($file,strlen($this->onlyExtensions[$i]) * -1)) != $this->onlyExtensions[$i]) $c = true;
			if($c) continue;
			
			$c = false;
			for($i = 0; $i < count($this->implementedInterfaces);$i++)
				if(!PMReflector::implementsInterface(str_replace($this->onlyExtensions,"",$file),$this->implementedInterfaces[$i])) $c = true;
			if($c) continue;
			
			$this->foundFiles[] = $file;
		}
		
	}
	
	public function getAsArray(){
		for($i = 0; $i < count($this->dirs); $i++)
			$this->searchFolder($this->dirs[$i]);
		
		return $this->foundFiles;
	}
	
	public function setDefaultConstructorParameter($parameter){
		$this->parameter = $parameter;
	}
	
	public function getAsLabeledArray($interface, $extension, $sorted = false){
		$this->addImplementedInterface($interface, $extension);
		$this->getAsArray();
		
		$labeled = array();
		foreach($this->foundFiles as $key => $value){
			$class = str_replace($this->onlyExtensions,"",$value);
			try {
				if($this->parameter != "nil") $class = new $class($this->parameter);
				else $class = new $class();

				if($class->getLabel() == null) continue;
				$labeled[$class->getLabel()] = get_class($class);
			} catch(ClassNotFoundException $e){
				continue;
			}
		}
		if($sorted) ksort($labeled);
		return $labeled;
	}
	
	public function getAsOptionsArray($interface, $extension){
		return array_flip($this->getAsLabeledArray($interface, $extension, true));
	}
}
 ?>