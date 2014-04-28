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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class HTMLPopupGUI {
	private $object;
	private $parametersCreate = array();
	private $emptyCheckField;
	private $parsers = array();
	private $colsLeft = array();
	private $colsRight = array();
	private $showTrash = true;
	private $showNew = true;
	private $showEdit = true;
	
	public function __construct($object){
		$this->object = $object;
	}
	
	public function parametersCreate(array $parameters){
		$this->parametersCreate = $parameters;
	}
	
	public function colLeft($parser, $style = ""){
		$this->colsLeft[] = array($parser, $style);
	}
	
	public function colRight($parser, $style = ""){
		$this->colsRight[] = array($parser, $style);
		
	}
	
	public function options($showTrash = true, $showEdit = true, $showNew = true){
		$this->showTrash = $showTrash;
		$this->showEdit = $showEdit;
		$this->showNew = $showNew;
	}
	
	public static function edit(HTMLGUIX $gui, $parentClass = null){
		
		$BC = new Button("Abbrechen", "stop");
		$BC->style("margin:10px;float:right;");
		$BC->onclick("\$j('#popupListEntries .lastSelected').removeClass('lastSelected'); \$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetailsm".($parentClass == null ? $gui->object()->getClearClass() : $parentClass)."').animate({'width':'400px'}, 200, 'swing'); });");
		
		$gui->addToEvent("onSave", "\$j('#popupListEntries .lastSelected').removeClass('lastSelected'); \$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetailsm".($parentClass == null ? $gui->object()->getClearClass() : $parentClass)."').animate({'width':'400px'}, 200, 'swing', function(){ ".OnEvent::reloadPopup("m".($parentClass == null ? $gui->object()->getClearClass() : $parentClass))." }); }); ");
		
		#$gui->displayMode("popup");
		
		return $BC."<div style=\"clear:both;\"></div>".$gui->getEditHTML();
	}
	
	public function emptyCheckField($name){
		$this->emptyCheckField = $name;
	}
	
	public function parser($column, $method){
		$this->parsers[$column] = $method;
	}
	
	public function browser(){
		$BA = new Button("Eintrag\nhinzufügen", "new");
		$BA->doBefore("\$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetails".$this->object->getClearClass()."').animate({'width':'400px'}, 200, 'swing', function(){ %AFTER }); });");
		$BA->rmePCR($this->object->getClearClass(), "-1", "create", $this->parametersCreate, OnEvent::reloadPopup($this->object->getClearClass()));
		$BA->style("margin:10px;");
		if(!$this->showNew)
			$BA = "";
		
		$cols = ($this->showTrash ? 1 : 0) + ($this->showEdit ? 1 : 0) + 1 + count($this->colsLeft) + count($this->colsRight);
		
		$TE = new HTMLTable($cols, "Einträge");
		if($this->showTrash)
			$TE->setColWidth(1, 20);
		
		if($this->showEdit){
			$TE->setColWidth($cols, 20);
			$TE->useForSelection(false);
		}
		$TE->maxHeight(400);
		$TE->weight("light");
		
		$BE = new Button("Eintrag bearbeiten", "arrow_right", "iconic");
		
		$autoLoad = false;
		while($A = $this->object->getNextEntry()){
			$action = "contentManager.selectRow(this); \$j('#editDetails".$this->object->getClearClass()."').animate({'width':'800px'}, 200, 'swing', function(){ ".OnEvent::frame("popupEditEntry", get_class($A), $A->getID(), "0", "function(){ \$j('#popupEditEntry').fadeIn(); }")." });";
			
			$BD = new Button("Eintrag löschen", "trash_stroke", "iconic");
			$BD->doBefore("\$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetails".$this->object->getClearClass()."').animate({'width':'400px'}, 200, 'swing', function(){ %AFTER }); });");
			$BD->onclick("deleteClass('".get_class($A)."','".$A->getID()."', function() { ".OnEvent::reloadPopup($this->object->getClearClass())." },'Eintrag wirklich löschen?');");

			$isEmpty = false;
			if($this->emptyCheckField != null AND $A->A($this->emptyCheckField) == ""){
				$autoLoad = $action;
				$isEmpty = true;
			}
			
			if(!$isEmpty)
				$div = Util::invokeStaticMethod(get_class($this->object), $this->parsers["main"], array($A));#($A->A("TinkerforgeBrickletUID") != "" ? $A->A("TinkerforgeBrickletUID") : "Neuer Eintrag");
			else
				$div = "Neuer Eintrag";
			
			$row = array();
			
			if($this->showTrash)
				$row[] = $BD;
			
			
			$row[] = $div;
			
			foreach($this->colsRight AS $col)
				$row[] = Util::invokeStaticMethod(get_class($this->object), $col[0], array($A));
			
			if($this->showEdit)
				$row[] = $BE;
			
			$TE->addRow($row);
			
			if($this->showTrash)
				$TE->addCellStyle(1, "vertical-align:top;");
			
			#$TE->addCellEvent(2, "click", $action);
			
			if($this->showEdit){
				$TE->addCellID(count($row), "popupEntryID".$A->getID());
				$TE->addCellStyle(count($row), "vertical-align:top;");
				$TE->addCellEvent(count($row), "click", $action);
			}
		}
		
		if($this->object->numLoaded() == 0){
			$TE->addRow("Keine Einträge");
			$TE->addRowColspan(1, $cols);
		}
		
		return "$BA
			<div style=\"float:right;width:400px;height:500px;display:none;background-color:#f4f4f4;\" id=\"popupEditEntry\"></div>
			<div id=\"popupListEntries\" style=\"width:400px;height:440px;overflow:auto;\">$TE</div>
			<div style=\"clear:both;\"></div>
			".($autoLoad ? OnEvent::script($autoLoad) : "");
		
	}
}
?>