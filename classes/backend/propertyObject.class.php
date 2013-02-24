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
class propertyObject extends PersistentObject {

	function __construct($id){
		parent::__construct($id);
	}
	
	/**
	 * @return HTMLGUI
	 */
	protected function getGUI(){
		$this->loadMeOrEmpty();
		
		$bps = $this->getMyBPSData();
		$c = $this->getClearClass()."Class";
		$d = $this->getClearClass()."ClassID";
		
		$this->A->$c = $bps["className"];
		$this->A->$d = $bps["classID"];
		
		$gui = new HTMLGUI();
		
		$gui->setType($this->getClearClass()."Class","hidden");
		$gui->setType($this->getClearClass()."ClassID","hidden");
		
		$gui->setObject($this);
		$gui->setFormID("propertyForm");
		
		if(isset($bps["returnTo".$bps["className"]])) $gui->setJSEvent("onSave","function(){ contentManager.reloadFrameLeft(); contentManager.loadFrame('contentRight','".$bps["returnTo".$bps["className"]]."'); }");
		
		$gui->setStandardSaveButton($this);
	
		return $gui;
	}
	
	public function createRemote(){
		die("alert:'You have to overwrite the method createRemote!'");
	}
	
	public function deleteRemote(){
		$this->deleteMe();
	}
}
?>