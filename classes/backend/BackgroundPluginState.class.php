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
class BackgroundPluginState {
	
	private $state = array();
	private $actual = "";
	
	/**
	 * Checks for existance of ':' or ';' in the supplied value.
	 * If any is found, execution is interrupted.
	 * 
	 * @param String $string String to be checked
	 */
	private function nameAndDataCheck($string){
		if(strpos($string,":") OR strpos($string,";")) {
			echo "You may NOT use ':' OR ';' in any class names, properties or values for BackgroundPluginState-functions";
			exit();
		}
	}
	
	/**
	 * Registers, unregisters or sets actual class according to given parameter.
	 * If parameter starts with '_', only registration occurs without change of actual class.
	 * Class name and parameters are split by ';'.
	 * If first parameter is '-', the class is unregistered.
	 * Otherwise each parameter is split by ':' in property name and value.
	 * Those pairs are then saved via setACProperty.
	 * 
	 * @param $string(String) Paremeter to be parsed
	 */
	public function setByString($string){
		if(!strstr($string,"SysMessages;")) $_SESSION["messages"]->addMessage("BPS: setByString:$string","BPS");
		
		$isAdd = false;
		if($string{0} == "_") {
			$isAdd = true;
			$string = substr($string,1);
		}
		$e = explode(";",$string);
		if(!$isAdd) $this->registerClass($e[0]);
		else {
			if(!$this->isClassRegistered($e[0]))
				$this->registerClass($e[0]);
			$this->setActualClass($e[0]);
		}
		
		#$this->setActualClass($e[0]);
		if($e[1] == "-") {
			$this->unregisterClass($e[0]);
			return;
		}
		for($i = 1;$i < count($e);$i++){
			$e2 = explode(":",$e[$i]);
			$this->setACProperty($e2[0],$e2[1]);
		}
	}
	
	/**
	 * Registers the specified class.
	 * 
	 * @param $className(String) The name of the class to be registered
	 */
	public function registerClass($className){
		
		$this->nameAndDataCheck($className);
		
		if($className == "") {
			echo "Please specify a class name for use in BackgroundPluginState->registerClass(\$className)!";
			exit();
		}
		
		$this->state[$className] = array();
		
		$this->setActualClass($className);
		if($className != "SysMessages") $_SESSION["messages"]->addMessage("BPS: registeringClass:$className","BPS");
	}
	
	/**
	 * Unregisters specified class.
	 * 
	 * @param $className(String) The name of the class to be unregistered
	 */
	public function unregisterClass($className){
		
		$this->nameAndDataCheck($className);
		
		if($className == "") {
			echo "Please specify a class name for use in BackgroundPluginState->unregisterClass(\$className)!";
			exit();
		}
		
		if(isset($this->state[$className])) unset($this->state[$className]);
		if($className != "SysMessages") $_SESSION["messages"]->addMessage("BPS: unregisteringClass:$className","BPS");
	}
	
	/**
	 * Returns all properties of the currently selected class.
	 * 
	 * @return array() Properties or -1 if no properties are set
	 */
	public function getAllProperties(){
		if($this->actual == "") {
			echo "You need to set the active plugin with setActualClass(\$className)!";
			exit();
		}
		
		if(!isset($this->state[$this->actual])) $this->state[$this->actual] = -1;
		
		return $this->state[$this->actual];
	}
	
	/**
	 * Selects actual class.
	 * 
	 * @return Boolean true if class state is set
	 * 
	 * @param $className(String) The name of the class to be set
	 */
	public function setActualClass($className){
		$this->nameAndDataCheck($className);
		
		if($className == "") {
			echo "Please specify a class name for use in BackgroundPluginState->setActualClass(\$className)!";
			exit();
		}
		if(isset($this->state[$className])) {
			$this->actual = $className;
			return true;
		} else {
			$this->state[$className] = -1;
			$this->actual = $className;
			return false;
		}
		
	}
	
	/**
	 * Unsets actual class.
	 */
	public function unsetActualClass(){
		$this->actual = "";
	}
	
	/**
	 * Sets the value of the specified property for the current class.
	 * If property name starts with '_' the value is added to the current value of this property.
	 * If property name starts with '-' the specified value is removed from property value.
	 * Otherwise the property value is set to the given value.
	 * 
	 * @param $property(String) The name of the property to be modified
	 * @param $value(String) The value to be added, removed or set
	 */
	public function setACProperty($property, $value){
		$add = false;
		$sub = false;
		if($property{0} == "_") {
			$add = true;
			$property = substr($property,1);
			if($this->state[$this->actual] == -1)
				$this->state[$this->actual] = array();
			
			if(!isset($this->state[$this->actual][$property]))
				$this->state[$this->actual][$property] = "";
		}
		
		if($property{0} == "-") {
			$sub = true;
			$property = substr($property,1);
			
			if(!isset($this->state[$this->actual][$property]))
				$this->state[$this->actual][$property] = "";
		}
		
		$this->nameAndDataCheck($property);
		$this->nameAndDataCheck($value);
		
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->setACProperty(\$property, \$value)!";
			exit();
		}
		if($this->actual == "") {
			echo "You need to set the active plugin with setActualClass(\$className)!";
			exit();
		}
		#print_r($this->state);
		if($this->state[$this->actual] == -1) $this->state[$this->actual] = array();
		if(!$sub)
			if(!$add) 
				$this->state[$this->actual][$property] = $value;
			else 
				$this->state[$this->actual][$property] .= ($this->state[$this->actual][$property] == ""? ",," : "").$value.",,";
		else 
			$this->state[$this->actual][$property] = str_replace(",$value,","",$this->state[$this->actual][$property]);
			
		if($this->actual != "SysMessages") $_SESSION["messages"]->addMessage("BPS: $this->actual;$property:$value","BPS");
		
	}
	
	/**
	 * This function returns the value(s) of the supplied property of the currently selected class.
	 * 
	 * @return String The value(s) of the supplied property
	 * 
	 * @param $property(String) The name of the property
	 */
	public function getACProperty($property){
		$this->nameAndDataCheck($property);
		
		if($this->actual == "") {
			echo "You need to set the active plugin with setActualClass(\$className)!";
			exit();
		}
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->getACProperty(\$property)!";
			exit();
		}
		return $this->state[$this->actual][$property];
	}
	
	/**
	 * Returns the value(s) of the specified property of the specified class.
	 * 
	 * @return String Property value(s)
	 * 
	 * @param $className(String) Name of the class
	 * @param $property(String) Name of the property
	 */
	public function getProperty($className, $property, $defaultValue = null){
		$this->nameAndDataCheck($className);
		$this->nameAndDataCheck($property);
		
		if($className == "") {
			echo "You need to specify the class name you want data for in BackgroundPluginState->getProperty(\$className, \$property)!";
			exit();
		}
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->getProperty(\$className, \$property)!";
			exit();
		}
		if(!isset($this->state[$className])) return $defaultValue;
		if(!isset($this->state[$className][$property])) return $defaultValue;
		return $this->state[$className][$property];
	}
	
	/**
	 * Checks if specified class is already registered.
	 * 
	 * @return Boolean True if class is registered
	 * 
	 * @param $className(String) The class name to be checked
	 */
	public function isClassRegistered($className){
		if($className == "") {
			echo "You need to specify the class name you want to check for in BackgroundPluginState->isPropertySet(\$className, \$property)!";
			exit();
		}
		$this->nameAndDataCheck($className);
		
		return isset($this->state[$className]);
	}
	
	/**
	 * Checks if the specified property of the specified class name is set.
	 * 
	 * @return Boolean True if property is set
	 * 
	 * @param $className(String) Name of the class
	 * @param $property(String) Name of the property
	 */
	public function isPropertySet($className, $property){
		if($className == "") {
			echo "You need to specify the class name you want to check for in BackgroundPluginState->isPropertySet(\$className, \$property)!";
			exit();
		}
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->isPropertySet(\$property)!";
			exit();
		}
		$this->nameAndDataCheck($className);
		$this->nameAndDataCheck($property);
		return isset($this->state[$className][$property]);
	}
	
	/**
	 * Checks if the specified property of the currently selected class is set.
	 * 
	 * @return Boolean True if property is set
	 * 
	 * @param $property(String) Name of the porperty
	 */
	public function isACPropertySet($property){
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->isACPropertySet(\$property)!";
			exit();
		}
		$this->nameAndDataCheck($property);
		return isset($this->state[$this->actual][$property]);
	}
	
	/**
	 * Unsets the specified property of the currently selected class.
	 * 
	 * @param $property(String) The name of the property
	 */
	public function unsetACProperty($property){
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->isACPropertySet(\$property)!";
			exit();
		}
		$this->nameAndDataCheck($property);
		unset($this->state[$this->actual][$property]);
	}
	
	/**
	 * Sets the value of the property for the specified class.
	 * 
	 * @param $className(String) The name of the class
	 * @param $property(String) The name of the property
	 * @param $value(String) The value to be set
	 */
	public function setProperty($className, $property, $value){
		$this->nameAndDataCheck($className);
		$this->nameAndDataCheck($property);
		$this->nameAndDataCheck($value);
		
		if($className == "") {
			echo "You need to specify the class name you want to set data for in BackgroundPluginState->setProperty(\$className, \$property)!";
			exit();
		}
		if($property == "") {
			echo "Please specify a property for use in BackgroundPluginState->setProperty(\$className, \$property)!";
			exit();
		}

		if(isset($this->state[$className]) AND !is_array($this->state[$className])) unset($this->state[$className]);
		$this->state[$className][$property] = $value;

		if($className != "SysMessages") $_SESSION["messages"]->addMessage("BPS: setProperty:$className;$property:$value","BPS");
	}
}
?>