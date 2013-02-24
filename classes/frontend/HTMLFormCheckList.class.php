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
class HTMLFormCheckList extends HTMLForm {
	function __construct($formID, $fields, $title = null) {
		parent::__construct($formID, $fields, $title);
		
		foreach($this->fields AS $field)
			$this->setType($field, "checkbox");
	}
	
	function __toString() {
		
		$this->getTable()->setColClass(1, "backgroundColor0");
		$this->getTable()->setColClass(2, "backgroundColor0");
		$this->getTable()->setColOrder(array(2,1));
		$this->getTable()->setColWidth(1, 20);
		
		$this->printColon(false);
		
		return parent::__toString();
	}
	
	public function setSaveCheckListUD($targetClass, $identifier = "", $checkedByDefault = true, $onSuccessFunction = null){
		$this->saveMode = "custom";
		
		if($onSuccessFunction != null AND strpos($onSuccessFunction, "function(") !== 0)
			$onSuccessFunction = "function(transport){ $onSuccessFunction }";
		
		$value = mUserdata::getUDValueS("$identifier$targetClass", "");
		
		if($checkedByDefault AND !$value)
			foreach($this->fields AS $field)
				$this->setValue ($field, "1");
		
		if($value != ""){
			$value = explode(",", $value);
			foreach($value AS $field)
				$this->setValue ($field, "1");
		}
		
		$B = new Button("Speichern", "./images/i2/save.gif", "icon");
		$B->onclick("var submitValues = ''; \$j.each(\$j('#$this->id').serializeArray(), function(k, v) { submitValues += (submitValues != '' ? ',' : '')+v.name; }); contentManager.rmePCR('mUserdata', '-1', 'setUserdata', ['$identifier$targetClass', submitValues]".($onSuccessFunction != null ? ", $onSuccessFunction" : "").");");
		$B->style("float:right;margin-top:-25px;margin-right:3px;");
		
		$this->saveButtonCustom = $B;
		
		$this->onSubmit = "return false;";
	}
}
?>