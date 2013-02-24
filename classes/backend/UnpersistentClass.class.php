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
class UnpersistentClass {
	private $collectionOf;
	protected $languageClass = null;
	protected $texts = null;

	protected $customizer;

	/**
	 * If active, customizes this class.
	 *
	 * If updated, please also update Collection::customizer and Environment::customizer
	 */
	public function customize(){
		try {
			$active = mUserdata::getGlobalSettingValue("activeCustomizer");
			if($active == null) return;

			$this->customizer = new $active();
			$this->customizer->customizeClass($this);
		} catch (Exception $e){	}
	}

	function getClearClass(){
		return str_replace("GUI","",get_class($this));
	}
	
	protected function getMyBPSData(){
		$_SESSION["BPS"]->setActualClass(get_class($this));
		return $_SESSION["BPS"]->getAllProperties();
	}
	
	function __construct() {
		$this->collectionOf = "Nix";
	}

	public function checkIfMyTableExists(){
		return false;
	}
	
	public function checkIfMyDBFileExists(){
		return false;
	}
	
	function getCollectionOf(){
		return $this->collectionOf;
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
}
?>
