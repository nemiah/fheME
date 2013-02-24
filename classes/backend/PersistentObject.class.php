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
class PersistentObject {
	protected $ID;
	protected $A = null;
	protected $AA = null;
	protected $Adapter = null;
	protected $storage = PHYNX_MAIN_STORAGE;#"MySQL";
	protected $noDeleteHideOnly = false;

	protected $languageClass;
	protected $texts;

	protected $myAdapterClass;
	protected $echoIDOnNew = false;
	protected $customizer;
	
	public $hasParsers = false;
	/*protected function usedParsers(){
		if($this->Adapter == null)
			return false;
		
		return true;
	}*/
	
	protected function getMyBPSData(){
		return BPS::getAllProperties(get_class($this));
	}

	protected function makeNewIfNew($promote = true){
		if($this->getID() != -1)
			return $this->getID();
		
		$this->loadMeOrEmpty();
		$id = $this->newMe();
		$this->forceReload();
		
		if($promote)
			echo OnEvent::script("contentManager.lastLoaded('left', ".$this->getID().");");
		
		return $id;
	}
	
	public function isNoDelete(){
		return $this->noDeleteHideOnly;
	}

	function __construct($ID){
		$this->ID = $ID;
	}
	
	function getA(){
		return $this->A;
	}

	function changeA($name, $value){
		if($this->A == null) $this->loadMe();
		$this->A->$name = $value;
	}

	function getID(){ return $this->ID; }

	public function isCloneable(){
		return PMReflector::implementsInterface(get_class($this),"iCloneable");
	}

	function loadAdapter(){
		if($this->Adapter != null) return;
		/*$adapterToLoad = get_class($this);

	    if(strstr($adapterToLoad,"Adapter") OR strstr($adapterToLoad,"GUI"))
           		$adapterToLoad = get_parent_class($adapterToLoad);*/

	    $n = $this->myAdapterClass;
		if($this->myAdapterClass != null) $this->Adapter = new $n($this->ID, $this->storage);
		else $this->Adapter = new Adapter($this->ID, $this->storage);
	}

	function setParser($a,$f){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addParser($a,$f);
		$this->hasParsers = true;
	}

	function resetParsers(){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->resetParsers();
		$this->hasParsers = false;
	}

	function getClearClass(){
		if(isset($this->table) AND $this->table != null) return $this->table;
		$n = get_class($this);
		if(strstr($n,"GUI")) $n = get_parent_class($this);
		if($n == "PersistentObject") $n = str_replace("GUI","",get_class($this));
		elseif(strstr($n,"GUI")) $n = get_parent_class(get_parent_class($this));
		return $n;
	}

	/*function loadMe(){
	    $this->loadAdapter();
		if($this->A == null) $this->A = $this->Adapter->loadSingle($this->getClearClass(get_class($this)));
	}

	public function loadMeT(){
	    $this->loadAdapter();
		if($this->A == null) {
			$this->A = $this->Adapter->loadSingleT($this->getClearClass(get_class($this)));
			$n = $this->getClearClass(get_class($this))."ID";
			if(isset($this->A->$n)) $this->ID = $this->A->$n;
			unset($this->A->$n);
		}
	}*/

	function deleteMe() {
		mUserdata::checkRestrictionOrDie("cantDelete".str_replace("GUI","",get_class($this)));

		if(Session::isPluginLoaded("mArchiv"))
			Archiv::archive($this);
		
	    $this->loadAdapter();
		if(!$this->noDeleteHideOnly) $this->Adapter->deleteSingle($this->getClearClass(get_class($this)));
		else {
			$this->loadMe();
			$this->A->isDeleted = 1;
			$this->saveMe();
		}
	}

	function loadTranslation($forClass = null){
		if($forClass == null) $forClass = $this->getClearClass();
		if($this->languageClass == null){
			try {
				$n = $forClass."_".$_SESSION["S"]->getUserLanguage();
				$this->languageClass = new $n();
			} catch(ClassNotFoundException $e){
				try {
					$n = $forClass."_de_DE";
					$this->languageClass = new $n();
				} catch(ClassNotFoundException $e){
					return null;
				}
			}
		}

		$this->texts = $this->languageClass->getText();

		return $this->languageClass;
	}

	function getGUIClass(){
		$n = get_class($this)."GUI";
		$this->loadMe();
		$g = new $n($this->ID);
		$g->setA($this->getA());
		return $g;
	}

	public function getXML(){
		$this->loadMe();
		$XML = new XML();
		$XML->setObject($this);
		$XML->setXMLHeader();
		return $XML->getXML();
	}

	public function A($attributeName){
		if($this->A == null) $this->loadMe();

		if(!isset($this->A->$attributeName)) return null;
		return $this->A->$attributeName;
	}

	public function AA($attributeName, $value = null){
		if($value != null){
			if($this->AA == null)
				$this->AA = new Attributes();
			
			$this->AA->$attributeName = $value;
		}
		
		if(!isset($this->AA->$attributeName)) return null;
		return $this->AA->$attributeName;
	}

	public function setEchoIDOnNew($bool){
		$this->echoIDOnNew = $bool;
	}
	
	public function customize(){
		if(defined("PHYNX_FORBID_CUSTOMIZERS"))
			return;
		
		try {
			$active = mUserdata::getGlobalSettingValue("activeCustomizer");

			if($active == null) return;

			$this->customizer = new $active();
			$this->customizer->customizeClass($this);
		} catch (ClassNotFoundException $e){

		} catch (TableDoesNotExistException $e){

		} catch (TableDoesNotExistException $e){

		}
	}
	
	public function loadMe(){
	    $this->loadAdapter();
		if($this->A == null) {
			$this->A = $this->Adapter->loadSingle2($this->getClearClass(get_class($this)));
			$n = $this->getClearClass()."ID";
			if(isset($this->A->$n)) $this->ID = $this->A->$n;
			unset($this->A->$n);
		}

		Aspect::joinPoint("after", $this, get_class($this)."::loadMe", $this->A);
		
		return $this->A != null;
	}
	
	function loadMeOrEmpty(){
	    $this->loadAdapter();
		//if($this->A == null) {
			if($this->ID != -1) $this->loadMe();
			if($this->A == null) $this->A = $this->newAttributes();
			$n = $this->getClearClass(get_class($this))."ID";
			unset($this->A->$n);
		//}
	}
	
	function loadMeOrEmptyT(){
	    $this->loadAdapter();
	    
		if($this->ID != -1) $this->loadMeT();
		if($this->A == null) $this->A = $this->newAttributes();
		$n = $this->getClearClass(get_class($this))."ID";
		unset($this->A->$n);
	}
	
	public function setA($A){
		if($this->A == null) {
			$this->A = $A;
			if($this->ID == null) {
				$n = $this->getClearClass(get_class($this))."ID";
				$this->ID = $A->$n;
				unset($A->$n);
			}
			return true;
		} else return false;
	}
	
	function newAttributes(){
		$this->loadAdapter();
		$A = $this->Adapter->getTableColumns($this->getClearClass(get_class($this)));
		$n = $this->getClearClass(get_class($this))."ID";
		$A->$n = -1;
		
		if($this->customizer != null)
			$this->customizer->customizeNewAttributes($this->getClearClass(get_class($this)), $A);
		
		return $A;
	}
	
	function newMe($checkUserData = true, $output = false){
		if($checkUserData) mUserdata::checkRestrictionOrDie("cantCreate".str_replace("GUI","",get_class($this)));

	    $this->loadAdapter();
	    if($this->A == null) $this->loadMe();

        $this->ID = $this->Adapter->makeNewLine2($this->getClearClass(get_class($this)), $this->A);

        if($output OR $this->echoIDOnNew){
	        if($this->echoIDOnNew) {
				echo $this->ID;
			} else
				Red::messageCreated();
		}
		
		Aspect::joinPoint("after", $this, get_class($this)."::newMe", $this->A);
		
        return $this->ID;
	}
	
	function forceReload(){
		$this->A = null;
		$this->loadMe();
	    #$this->loadAdapter();
		#$this->A = $this->Adapter->loadSingle2($this->getClearClass(get_class($this)));
	}
	
	function saveMe($checkUserData = true, $output = false){
		Aspect::joinPoint("before", $this, get_class($this)."::saveMe", $this->A);
		
		if($checkUserData) mUserdata::checkRestrictionOrDie("cantEdit".str_replace("GUI","",get_class($this)));

		$this->loadAdapter();
		$this->Adapter->saveSingle2($this->getClearClass(get_class($this)), clone $this->A);
	    if($output) Red::messageSaved();
	}

	protected function getJSON(){
		$this->loadMe();

		return json_encode($this->A);
	}
}


?>
