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
class HTMLPopupGUI {
	private $object;
	private $parametersCreate = array();
	private $emptyCheckField;
	private $parsers = array();
	private $colsLeft = array();
	private $colsRight = array();
	
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
	
	public static function edit(HTMLGUIX $gui){
		
		$BC = new Button("Abbrechen", "stop");
		$BC->style("margin:10px;float:right;");
		$BC->onclick("\$j('#popupListEntries .lastSelected').removeClass('lastSelected'); \$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetailsm".$gui->object()->getClearClass()."').animate({'width':'400px'}, 200, 'swing'); });");
		
		$gui->addToEvent("onSave", "\$j('#popupListEntries .lastSelected').removeClass('lastSelected'); \$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetailsm".$gui->object()->getClearClass()."').animate({'width':'400px'}, 200, 'swing', function(){ ".OnEvent::reloadPopup("m".$gui->object()->getClearClass())." }); }); ");
		
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

		$cols = 3 + count($this->colsLeft) + count($this->colsRight);
		
		$TE = new HTMLTable($cols, "Einträge");
		$TE->setColWidth(1, 20);
		$TE->setColWidth($cols, 20);
		$TE->useForSelection(false);
		$TE->maxHeight(400);
		$TE->weight("light");
		
		$BE = new Button("Eintrag bearbeiten", "arrow_right", "iconic");
		
		$autoLoad = false;
		while($A = $this->object->getNextEntry()){
			$BD = new Button("Eintrag löschen", "trash_stroke", "iconic");
			$BD->doBefore("\$j('#popupEditEntry').fadeOut(400, function(){ \$j('#editDetails".$this->object->getClearClass()."').animate({'width':'400px'}, 200, 'swing', function(){ %AFTER }); });");
			$BD->onclick("deleteClass('".get_class($A)."','".$A->getID()."', function() { ".OnEvent::reloadPopup($this->object->getClearClass())." },'Eintrag wirklich löschen?');");

			$isEmpty = false;
			if($this->emptyCheckField != null AND $A->A($this->emptyCheckField) == ""){
				$autoLoad = $A->getID();
				$isEmpty = true;
			}
			
			if(!$isEmpty)
				$div = Util::invokeStaticMethod(get_class($this->object), $this->parsers["main"], array($A));#($A->A("TinkerforgeBrickletUID") != "" ? $A->A("TinkerforgeBrickletUID") : "Neuer Eintrag");
			else
				$div = "Neuer Eintrag";
			
			$row = array();
			$row[] = $BD;
			$row[] = $div;
			
			foreach($this->colsRight AS $col){
				$c = Util::invokeStaticMethod(get_class($this->object), $col[0], array($A));
				$row[] = $c;
			}
			
			$row[] = $BE;
			
			$TE->addRow($row);
			$action = "contentManager.selectRow(this); \$j('#editDetails".$this->object->getClearClass()."').animate({'width':'800px'}, 200, 'swing', function(){ ".OnEvent::frame("popupEditEntry", get_class($A), $A->getID(), "0", "function(){ \$j('#popupEditEntry').fadeIn(); }")." });";
			
			$TE->addCellEvent(2, "click", $action);
			$TE->addCellID(2, "popupEntryID".$A->getID());
			
			$TE->addCellEvent(count($row), "click", $action);
			
		}
		
		if($this->object->numLoaded() == 0){
			$TE->addRow("Keine Einträge");
			$TE->addRowColspan(1, 3);
		}
		
		return "$BA
			<div style=\"float:right;width:400px;height:500px;display:none;\" id=\"popupEditEntry\"></div>
			<div id=\"popupListEntries\" style=\"width:400px;height:440px;overflow:auto;\">$TE</div>
			<div style=\"clear:both;\"></div>
			".($autoLoad ? OnEvent::script("\$j('#popupEntryID".$autoLoad."').trigger(Touch.trigger);") : "");
		
	}
}
?>