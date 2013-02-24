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
class XMLPlugin extends PluginV2 {
	private $file;
	private $name;
	private $folder = array();
	private $icon;
	private $menuName;
	private $javascript;
	private $adminOnly;
	private $collection;
	private $genericCollection;
	private $genericSingle;
	private $menuEntryTarget;
	
	private $doSomethingElse;
	
	private $collectionGUI = array();
	
	private $version;
	
	function __construct($file, $allowedPlugins = null){
		$this->file = $file;
		$this->parse($allowedPlugins);
	}
	
	private function parse($allowedPlugins){
		$content = file($this->file);
		$content = implode("", $content);
		$p = xml_parser_create();
		xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($p, $content, $vals, $index);
		if(xml_get_error_code($p)) echo xml_error_string(xml_get_error_code($p))." at line ".xml_get_current_line_number($p);
		xml_parser_free($p);

		if(isset($index["genericCollection"]) AND isset($vals[$index["genericCollection"][0]])) $this->genericCollection = $vals[$index["genericCollection"][0]]["value"] == "true";
		else $this->genericCollection = false;
		
		if(isset($index["genericSingle"]) AND isset($vals[$index["genericSingle"][0]])) $this->genericSingle = $vals[$index["genericSingle"][0]]["value"] == "true";
		else $this->genericSingle = false;
		
		if(isset($vals[$index["name"][0]])) $this->name = $vals[$index["name"][0]]["value"];
		
		if(isset($index["folder"]) AND isset($vals[$index["folder"][0]])) 
			foreach($index["folder"] AS $k => $v)
				$this->folder[] = $vals[$index["folder"][$k]]["value"];

		if(isset($vals[$index["icon"][0]]) AND isset($vals[$index["icon"][0]]["value"])) $this->icon = $vals[$index["icon"][0]]["value"];
		
		if(isset($index["menuName"]) AND isset($vals[$index["menuName"][0]]) AND isset($vals[$index["menuName"][0]]["value"]))
			$this->menuName = $vals[$index["menuName"][0]]["value"];
		
		if(isset($index["javascript"]) AND isset($vals[$index["javascript"][0]])) {
			$JS = array();
			foreach($index["javascript"] AS $k => $v)
				$JS[] = $vals[$index["javascript"][$k]]["value"];
			
			$this->javascript = $JS;#$vals[$index["javascript"][0]]["value"];
		}
		
		if(isset($index["adminOnly"]) AND isset($vals[$index["adminOnly"][0]])) 
			$this->adminOnly = $vals[$index["adminOnly"][0]]["value"];
		else
			$this->adminOnly = "false";
		if(isset($index["collection"]) AND isset($vals[$index["collection"][0]])) $this->collection = $vals[$index["collection"][0]]["value"];
		
		if(isset($index["doSomethingElse"]) AND isset($vals[$index["doSomethingElse"][0]])) $this->doSomethingElse = $vals[$index["doSomethingElse"][0]]["value"];
		if(isset($index["menuEntryTarget"]) AND isset($vals[$index["menuEntryTarget"][0]])) $this->menuEntryTarget = $vals[$index["menuEntryTarget"][0]]["value"];

		if(isset($index["version"]) AND isset($vals[$index["version"][0]]) AND isset($vals[$index["version"][0]]["value"]))
			$this->version = $vals[$index["version"][0]]["value"];

		
		if(count($allowedPlugins) > 0 AND !in_array($this->registerClassName(), $allowedPlugins))
			return;
		
		if(isset($index["registry"]) AND isset($vals[$index["registry"][0]]))
			foreach($index["registry"] AS $k => $v){
				$call = explode(";", $vals[$index["registry"][$k]]["value"]);
				#print_r($call);
				
				if(count($call) == 3)
					Registry::setCallback($call[0], $call[1], $call[2]);

				if(count($call) == 2)
					Registry::setCallback($call[0], $call[1]);
					#echo "callbacks:";
				#print_r(Registry::getCallbacks($call[0], $call[2]));
			}

		
		if(isset($index["collectionGUI"]))
			for($i = $index["collectionGUI"][0]; $i <= $index["collectionGUI"][count($index["collectionGUI"])-1]; $i++){
			if($vals[$i]["tag"] == "column"){
				if($vals[$i]["value"] == "") continue;
				$name = $vals[$i]["value"];
				
				if(!isset($this->collectionGUI["showAttributes"]))
					$this->collectionGUI["showAttributes"] = array();
				
				$this->collectionGUI["showAttributes"][] = $name;
				
				if(isset($vals[$i]["attributes"])){
					
					if(!isset($this->collectionGUI["rowStyle"]))
						$this->collectionGUI["rowStyle"] = array();
						
					if(!isset($this->collectionGUI["colWidth"]))
						$this->collectionGUI["colWidth"] = array();
					
					if(isset($vals[$i]["attributes"]["rowStyle"]))
						$this->collectionGUI["rowStyle"][$name] = $vals[$i]["attributes"]["rowStyle"];
						
					if(isset($vals[$i]["attributes"]["colWidth"]))
						$this->collectionGUI["colWidth"][$name] = $vals[$i]["attributes"]["colWidth"];
				}
					
			}
				
		}
	}
	
	function registerName() {
		if($this->name != null) return $this->name;
		else return parent::registerName();
	}
	
	function registerFolder() {
		if(count($this->folder) > 0) return $this->folder;
		else return parent::registerFolder();
	}
	
	function registerIcon(){
		if($this->icon != null) return $this->icon;
		else return parent::registerIcon();
	}
	
	function registerClassName() {
		#if(!$this->genericCollection) {
		if($this->collection == null) return "m".$this->name;
		else return $this->genericSingle ? "m".$this->name : $this->collection;
		#}
		#else return parent::registerClassName();
	}
	/*
	public function registerDependencies(){
		return "none";
	}
	
	public function doSomethingElse(){
	
	}
	*/
	function registerJavascriptFile(){
		if($this->javascript != null) return $this->javascript;
		else return parent::registerJavascriptFile();
	}
	/*
	function registerJavascriptFolder(){
		return "";
	}
	*/
	function registerMenuEntry(){
		if($this->menuName != null) return $this->menuName;
		else return "";
		#else return parent::registerMenuEntry();
	}
	
	function registerMenuEntryTarget(){
		if($this->menuEntryTarget != null) return $this->menuEntryTarget;
		else return parent::registerMenuEntryTarget();
	}
	
	function registerPluginIsAdminOnly(){
		if($this->adminOnly != null) return $this->adminOnly == "true";
		else return parent::registerPluginIsAdminOnly();
	}
	
	function registerUseGenericClasses(){
		return array($this->genericCollection,$this->genericSingle);
	}
	
	function doSomethingElse(){
		if($this->doSomethingElse != null) {
			$n = $this->registerClassName();
			$f = $this->doSomethingElse;

			if(method_exists($n, $f)){
				$method = new ReflectionMethod($n, $f);
				$method->invoke(null);
			} else {
				$method = new ReflectionMethod($n."GUI", $f);
				$method->invoke(null);
			}
		}
		else return parent::doSomethingElse();
	}
	
	function getCollectionGUI(){
		return $this->collectionGUI;
	}
	
	function registerVersion(){
		if($this->version != null) return $this->version;
		else return parent::registerVersion();
	}
}
?>