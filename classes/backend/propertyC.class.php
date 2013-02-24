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
class propertyC extends anyC {
	#public $viewOnly = false;
	
	protected $ownerClassName;
	protected $ownerClassID;
	#protected $compactLayout = false;
	protected $singleOwner = false;

	protected $targetClassName;
	protected $buttonLabel;
	protected $buttonIcon;
	protected $buttonOnclick;
	
	protected $showAttributes = null;

	/**
	 * @var HTMLGUIX
	 */
	protected $GUI;
	protected $name;

	protected $allowDelete = true;
	protected $allowEdit = false;
	protected $tableLabel;

	protected $singleEntry = false;

	private $displayMode;

	public function  getClearClass() {
		return str_replace("GUI", "", get_class($this));
	}

	public function setSingleOwner($className, $classID){
		$this->singleOwner = true;
		$this->setOwner($className, $classID);
	}

	public function __construct(){
		$this->GUI = new HTMLGUIX($this);

		$this->setCollectionOf(preg_replace("/^m/", "", str_replace("GUI", "", get_class($this))));
	}
	
	public function setOwner($className, $classID){
		$this->ownerClassID = $classID;
		$this->ownerClassName = $className;
		#$this->setTarget($className);
	}

	public function setName($name){
		$this->name = $name;
	}
	
	public function setTarget($className){
		$this->targetClassName = $className;
	}
	
	public function setValuesClass($valuesClass){
		$this->valuesClass = $valuesClass;
	}

	public function setButton($label, $icon = null, $onclick = null){
		$this->buttonLabel = $label;
		$this->buttonIcon = $icon;
		$this->buttonOnclick = $onclick;
	}

	public function setShownAttributes($sa){
		$this->showAttributes = $sa;
	}

	public function setOptions($showTrash = true, $showEdit = false){
		$this->allowDelete = $showTrash;
		$this->allowEdit = $showEdit;
	}
	
	/**
	 * @return HTMLGUIX
	 */
	public function getGUI(){
		return $this->GUI;
	}

	/**
	 * @return anyC
	 */
	public function getC(){
		if($this->C == null) $this->loadC();
		return $this->C;
	}

	public function displayMode($DM){
		$this->displayMode = $DM;
	}

	public function getHTML($id, $page){
		if(!$this->singleOwner){
			$this->addAssocV3($this->collectionOf."OwnerClass", "=", $this->ownerClassName);
			$this->addAssocV3($this->collectionOf."OwnerClassID", "=", $this->ownerClassID);
		} else
			$this->addAssocV3($this->collectionOf.$this->ownerClassName."ID", "=", $this->ownerClassID);

		$gui = $this->GUI;
		try {
			$this->lCV3($id);
		} catch(FieldDoesNotExistException $e){
			return "<p>".$e->getErrorMessage()."</p>";
		}

		if($this->showAttributes != null)
			$gui->attributes($this->showAttributes);

		$gui->name($this->name);
		$gui->object($this);

		if($this->displayMode == null)
			if($this->ownerClassID > 0)
				$gui->addToEvent("onDelete","contentManager.reloadFrame('contentLeft');");
			else
				$gui->addToEvent("onDelete","contentManager.rmePCR('m$this->collectionOf', '-1', 'getContent', ['$this->ownerClassName', '$this->ownerClassID'], '$(\'propertym$this->collectionOf\').update(transport.responseText);');");
		else
			$gui->displayMode($this->displayMode);

		if($this->buttonLabel instanceof Button)
			$B = $this->buttonLabel;
		else {
			$B = new Button($this->buttonLabel, $this->buttonIcon == null ? "new" : $this->buttonIcon);
			if($this->buttonOnclick == null)
				$B->select(false, "m".$this->targetClassName, $this->ownerClassName, $this->ownerClassID, "add".$this->targetClassName);
			else
				$B->onclick($this->buttonOnclick);
		}

		if(!$this->singleEntry OR $this->numLoaded() == 0)
			$gui->addTopButton($B);

		if($this->allowEdit)
			$gui->activateFeature("editInPopup", $this, "{remember:true}");
		

		$gui->options($this->allowDelete, $this->allowEdit, false, false);

		return $gui->getBrowserHTML($id);
	}

	public function getContent($ownerClass, $ownerClassID){
		$this->setOwner($ownerClass, $ownerClassID);
		echo $this->getHTML(-1, 0);
	}
	
	public function __toString(){
		try {
			if($this->ownerClassID == -1) {
				$t = new HTMLTable(1);
				$t->addRow("Sie müssen den Datensatz erst speichern, bevor Sie ".($this->tableLabel != null ? $this-> tableLabel : $this->collectionOf)." eintragen können.");
				return "<div style=\"margin-top:30px;\">".$t->getHTML()."</div>";
			}
			else return "<div style=\"margin-top:30px;\" id=\"propertym$this->collectionOf\">".$this->getHTML(-1, 0)."</div>";
		} catch (Exception $e){
			print_r($e);
			die();
		}
	}
}
?>