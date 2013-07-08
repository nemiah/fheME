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
class mUserdata extends anyC {
	function __construct() {
		$this->setCollectionOf("Userdata");
	}
	
	public function loadDataOfUser($UserID = 0){
		if($UserID == 0) $UserID = $_SESSION["S"]->getCurrentUser()->getID();
		$this->addAssocV3("UserID","=",$UserID);
		$this->lCV3();
	}
	
	public static function getRelabels($forPlugin){
		$UD = new mUserdata();
		$UD->addAssocV3("typ","=","relab");
		$UD->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		$UD->addAssocV3("name","LIKE","relabel".$forPlugin.":%");
	
		$labels = array();
				
		try	{
			$UD->getNextEntry();
		}
		catch (StorageException $e){
			return $labels;
		}
		
		$UD->resetPointer();
		
		while(($sUD = $UD->getNextEntry())){
			$A = $sUD->getA();
			$s = split(":",$A->name);
			$labels[$s[1]] = $A->wert;
		}
		return $labels;
	}
	
	public static function getHiddenPlugins(){
		$Cache = SpeedCache::getCache("getHiddenPlugins");
		if($Cache !== null)
			return $Cache;

		$UD = new mUserdata();
		$UD->addAssocV3("typ","=","pHide");
		$UD->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
	
		$labels = array();
		try {
			while(($sUD = $UD->getNextEntry())){
				$A = $sUD->getA();
				$labels[$A->wert] = 1;
			}

		} catch (StorageException $e){
			return true;
		}
		SpeedCache::setCache("getHiddenPlugins", $labels);

		return $labels;
	}
	
	public static function getHides($forPlugin){
		$labels = array();
		
		$UD = new mUserdata();
		$UD->addAssocV3("typ","=","hideF");
		$UD->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		$UD->addAssocV3("name","LIKE","hideField".$forPlugin.":%");
			
		try	{
			$UD->getNextEntry();
		}
		catch (StorageException $e){
			return $labels;
		}
		
		$UD->resetPointer();
		
		while(($sUD = $UD->getNextEntry())){
			$A = $sUD->getA();
			$s = split(":",$A->name);
			$labels[$s[1]] = $A->wert;
		}
		return $labels;
	}
	
	public static function getPluginSpecificData($forPlugin){
		if(Session::currentUser() === null)
			return array();
		
		$UD = new mUserdata();
		$UD->addAssocV3("typ","=","pSpec");
		$UD->addAssocV3("wert","=","$forPlugin", "AND");
		$UD->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
	
		$labels = array();
		
		while(($sUD = $UD->getNextEntry())){
			$A = $sUD->getA();
			$labels[$A->name] = $A->wert;
		}
		return $labels;
	}
	
	/**
	 * You can get Userdata with this function.
	 * Returns null if name does not exist
	 */
	public function getUserdata($name, $UserID = 0){

		if($this->collector == null) 
			$this->loadDataOfUser($UserID);
		
		$r = null;
		
		while(($t = $this->getNextEntry()))
			if($t->A("name") == $name)
				$r = $t;
		
		$this->resetPointer();
		return $r;
	}
	
	public function getAsObject($typ){
		$this->addAssocV3("typ","=",$typ);
		$this->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		$c = new stdClass();
		
		while(($t = $this->getNextEntry())){
			$n = $t->A("name");
			$c->$n = $t->A("wert");
		}
			
		return $c;
	}
	
	public function getAsArray($typ){
		
		$this->addAssocV3("typ","=",$typ);
		$this->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		$r = array();
		
		while(($t = $this->getNextEntry()))
			$r[$t->A("name")] = $t->A("wert");
		
		return $r;
	}

	public static function getUDValueS($name, $default = null){
		$U = new mUserdata();
		return $U->getUDValue($name, $default);
	}

	public function getUDValue($name, $default = null){
		$this->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		$this->addAssocV3("name","=",$name);
		$this->lCV3();
		$UD = $this->getNextEntry();
		
		return $UD == null ? $default : $UD->getA()->wert;
	}
	
	public function getUDValueCached($name){
		$UD = $this->getUserdata($name);
		
		return $UD == null ? $UD : $UD->getA()->wert;
	}

	public static function getGlobalSettingValue($name, $defaultValue = null){
		$UD = new mUserdata();
		$UD->addAssocV3("UserID", "=", "-1");
		$UD->addAssocV3("name", "=", $name);

		$e = $UD->getNextEntry();
		if($e == null) return $defaultValue;
		
		return $e->getA()->wert;
		
	}

	public static function setUserdataS($name, $wert, $typ = "", $UserID = 0){
		$U = new mUserdata();
		return $U->setUserdata($name, $wert, $typ, $UserID);
	}

	public function setUserdata($name, $wert, $typ = "", $UserID = 0, $echoSaved = false){
		if($UserID  > 0 AND $_SESSION["S"]->isUserAdmin() == "0"){
			echo "Only an admin-user can change Userdata of other users!";
			exit();
		}
	
		if($typ == "uRest" AND $_SESSION["S"]->isUserAdmin() == "0"){
			echo "Only an admin-user can change restrictions!";
			exit();
		}
		
		if($UserID == 0)
			$UserID = $_SESSION["S"]->getCurrentUser()->getID();
			
		$UD = $this->getUserdata($name, $UserID);

		if($UD == null){
			$nUD = new Userdata(-1);
			$nUDA = $nUD->newAttributes();
			$nUDA->UserID = $UserID;
			$nUDA->name = $name;
			$nUDA->wert = $wert;
			$nUDA->typ = $typ;
			
			$nUD->setA($nUDA);
			$nUD->newMe();
			
			$this->collector[] = $nUD;
		} else 
			$UD->saveNewValue($wert);
		
		if($echoSaved)
			Red::messageSaved ();
	}
	
	public static function checkRestrictionOrDie($restriction){
		if($_SESSION["S"]->getCurrentUser() == null) return;#throw new Exception("No user authenticated with the system!");
		if($_SESSION["S"]->isUserAdmin()) return;
		
		$UD = new mUserdata();
		$UD->addAssocV3("wert","=",$restriction);
		$UD->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		$sUD = $UD->getNextEntry();
		if($sUD != null)
			Red::errorD("Diese Aktion ist nicht erlaubt!");
	}
	
	public static function isDisallowedTo($restriction){
		$UD = new mUserdata();
		$UD->addAssocV3("wert","=",$restriction);
		$UD->addAssocV3("UserID","=",$_SESSION["S"]->getCurrentUser()->getID());
		
		try {
			$sUD = $UD->getNextEntry();
		}
		catch (StorageException $e){
			return true;
		}
		
		if($sUD != null) return false;
		else return true;
	}
	
	public function delUserdata($name, $UserID = 0){
		$UD = $this->getUserdata($name, $UserID);
		
		if($UD == null) return false;
		else {
			$UD->deleteMe();
			#$_SESSION["UD"] = new mUserdata();
			return true;
		}
	}
}
?>