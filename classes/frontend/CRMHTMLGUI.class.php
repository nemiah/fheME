<?php
/**
 *  This file is part of lightCRM.

 *  lightCRM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  lightCRM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class CRMHTMLGUI extends HTMLGUIX {
	#private $types = array();
	#private $options = array();
	#protected $labels = array();
	private $forceNewRow = array();

	#private $topButtons = array();

	private $functionDelete = "deleteClass('%CLASSNAME','%CLASSID', function() { contentManager.reloadFrame('contentRight'); contentManager.emptyFrame('contentLeft'); /*ADD*/ },'Eintrag löschen?');";
	private $functionSave = "function(transport) { contentManager.setLeftFrame('%CLASSNAME', %CLASSID); contentManager.reloadFrame('contentLeft'); contentManager.updateLine('%CLASSNAMEForm', %CLASSID, 'm%CLASSNAME'); }";
	private $functionSaveNew = "function(transport) { contentManager.reloadFrame('contentRight'); contentManager.loadFrame('contentLeft', '%CLASSNAME', transport.responseText); }";
	private $functionAbort = "contentManager.restoreFrame('contentLeft','lastPage', true);";
	private $functionEdit = "contentManager.backupFrame('contentLeft','lastPage', true); contentManager.loadFrame('contentLeft','%CLASSNAME','%CLASSID','','%CLASSNAMEGUI;edit:ok');";

	public function addTopButton($labelOrButton, $image = ""){
		if($labelOrButton instanceof Button){
			$B = $labelOrButton;
			
			$B->type("LPBig");
			$B->style("margin-left:15px;");
			$B->className("backgroundColor0");
		} else {
			$B = new Button($labelOrButton, $image);
			$B->style("float:left;margin-left:10px;");
		}

		$this->topButtons[] = $B;

		return $B;
	}

	public function space($fieldName, $label = "", $forceNewRow = false, $replaceWith = null) {
		$this->forceNewRow[$fieldName] = $forceNewRow;
		$this->replaceWith[$fieldName] = $replaceWith;
		
		parent::space($fieldName, $label);
	}
	
	public function addToEvent($event, $function){
		switch($event){
			case "onDelete":
				$this->functionDelete = str_replace("/*ADD*/", $function, $this->functionDelete);
			break;
		}
	}

	public function getAbortButton(){
		$B = new Button("Abbrechen","stop");
		$B->onclick(str_replace(array("%CLASSNAME"),array($this->className),$this->functionAbort));
		$B->style("float:left;margin-left:10px;");

		return $B;
	}

	public function replaceEvent($event, $function){
		switch($event){
			case "onDelete":
				$this->functionDelete = $function;
			break;

			case "onSave":
				$this->functionSave = $function;
			break;
		}
	}

	#public function space($fieldName){
	#	$this->spaces[$fieldName] = "";
	#}

	/**
	 * @param string $fieldName
	 * @param string $type
	 * @param array|anyC $options
	 * 
	 * @param string $labelField UNUSED!
	 * @param string $zeroEntry UNUSED!
	 */
	public function type($fieldName, $type, $options = null, $labelField = null, $zeroEntry = null){
		$this->types[$fieldName] = $type;
		
		if(is_object($options) AND $options instanceof Collection){
			$opt = array();

			if($zeroEntry != null)
				$opt[0] = $zeroEntry;

			while($O = $options->getNextEntry())
				$opt[$O->getID()] = $O->A($labelField);

			$options = $opt;
		}
		
		if($type == "HTMLEditor")
			$options = array($this->object->getClearClass(), $this->object->getID(), $fieldName);

		if($options != null) $this->options[$fieldName] = $options;
	}

	public function getEditTableHTML($cols = 2){
		BPS::unsetProperty($this->className."GUI", "edit");

		if($this->attributes == null)
			$this->attributes = PMReflector::getAttributesArrayAnyObject($this->object->getA());

		if($this->name == null)
			$this->name = $this->className;

		$BA = $this->getAbortButton();
		if(isset($this->features["CRMEditAbove"]))
			$BA->style("float:left;margin-left:10px;margin-top:10px;");
		
		$Buttons = "";
		foreach($this->sideButtons AS $B){
			if(isset($this->features["CRMEditAbove"]))
				$B->style("float:left;margin-left:10px;margin-top:10px;");
			
			$Buttons .= $B;
		}
		
		$abort = "<div>$BA$Buttons</div><div style=\"clear:left;height:10px;\"></div>";

		
		$tab = new HTMLForm($this->className."Form", $this->attributes, $this->name." editieren:");

		if($cols != 2) $tab->cols($cols);

		foreach($this->labels AS $k => $v)
			$tab->setLabel($k, $v);

		foreach($this->types AS $k => $v)
			$tab->setType($k, $v, null, isset($this->options[$k]) ? $this->options[$k] : null);

		foreach($this->spaces AS $k => $v)
			$tab->insertSpaceAbove($k, $v);
		
		foreach($this->autocomplete AS $k => $a)
			$tab->setAutoComplete($k, $a[0], $a[1]);

		foreach($this->fieldButtons AS $k => $B)
			$tab->addFieldButton($k, $B);

		foreach($this->parsers AS $n => $l)
			$tab->setType($n, "parser", null, array($l, $this->object));
		
		foreach($this->inputStyles AS $k => $n)
			$tab->setInputStyle($k, $n);
		
		$tab->setValues($this->object);

		if($this->object->getID() == -1)
			$save = $this->functionSaveNew;
		else
			$save = $this->functionSave;

		$tab->setSaveClass($this->className, $this->object->getID(), str_replace(array("%CLASSNAME","%CLASSID"), array($this->className, $this->object->getID()), $save), $this->name);

		return $abort.$tab;
	}

	public function getEditHTML(){
		if(BPS::getProperty($this->className."GUI", "edit") == "ok")
			return $this->getEditTableHTML();

################################################################################
		if($this->name == null)
			$this->name = $this->className;

		if($this->attributes == null)
			$this->attributes = PMReflector::getAttributesArrayAnyObject($this->object->getA());


		$widths = Aspect::joinPoint("changeWidths", $this, __METHOD__);
		if($widths == null) $widths = array(700, 132, 218);

		$tab = new HTMLTable(2);

		$tab->setTableStyle("width:$widths[0]px;margin-left:10px;");
		$tab->setColWidth(1, "50%");
		$tab->setColWidth(2, "50%");

		$A = $this->object->getA();

		$TSub = new HTMLTable(2);
		$TSub->setColWidth(1, 120);
		$TSub->setColClass(1, "");
		$TSub->setColClass(2, "");
		
		$TC = clone $TSub;
		$row = array();
		foreach($this->attributes AS $k => $v){
			if(isset($this->types[$v]) AND $this->types[$v] == "hidden") continue;
			
			if(isset($this->parsers[$v]))
				$A->$v = $this->invokeParser($this->parsers[$v], $A->$v, $this->object);
			
			if(isset($this->types[$v]) AND $this->types[$v] == "select")
				if(isset($this->options[$v]) AND isset($this->options[$v][$A->$v]))
					$A->$v = $this->options[$v][$A->$v];
			
				
			if(isset($this->spaces[$v]) AND $this->spaces[$v] != ""){
				if($k > 0)
					$row[] = $TC;
				
				if($this->forceNewRow[$v]){
					$row[] = "";
					$tab->addRow($row);
					$tab->addRowClass("backgroundColor0");
					$tab->addRowStyle("vertical-align:top;");
					
					$row = array();
				}
					
				if(count($row) == 2){
					$tab->addRow($row);
					$tab->addRowClass("backgroundColor0");
					$tab->addRowStyle("vertical-align:top;");
					
					$row = array();
				}
				$TC = clone $TSub;
				if(trim($this->spaces[$v]) != ""){
					$TC->addRow(array($this->spaces[$v]));
					$TC->addRowClass("backgroundColor2");
					$TC->addRowColspan(1, 2);
					
				}
				
				if($this->replaceWith[$v] !== null){
					$TC = $this->replaceWith[$v];
				}
			}
			
			if($A->$v != ""){
				$B = "";
				
				if(isset($this->fieldButtons[$v])){
					$B = $this->fieldButtons[$v];
					$B->style("float:right;");
				}
				
				if($TC instanceof HTMLTable){
					$TC->addLV($this->labels($v).":", $B.nl2br($A->$v));
					$TC->addRowStyle("vertical-align:top;");
				}
			}
			/*
			$label = isset($this->labels[$v]) ? $this->labels[$v] : $v;

			$row[] = "<label>".($label != "" ? ucfirst($label).":" : "")."</label>";



			$row[] = nl2br($A->$v);*/

			/*if(count($row) == 4){
				$tab->addRow($row);
				$row = array();
			}*/
		}
		
		$row[] = $TC;

		if(count($row) == 1)
			$row[] = "";
		
		if(count($row) == 2){
			$tab->addRow($row);
			$tab->addRowClass("backgroundColor0");
			$tab->addRowStyle("vertical-align:top;");
		}

		$BE = new Button("Eintrag\nbearbeiten","edit");
		$BE->onclick(str_replace(array("%CLASSNAME","%CLASSID"), array($this->className, $this->object->getID()), $this->functionEdit));
		$BE->style("float:left;margin-left:10px;");

		$BD = new Button("Eintrag\nlöschen","trash");
		$BD->onclick(str_replace(array("%CLASSNAME","%CLASSID"), array($this->className, $this->object->getID()), $this->functionDelete));
		$BD->style("float:left;margin-left:10px;");

		if(!mUserdata::isDisallowedTo("cantDelete".$this->className))
			$BD = "";
		
		if(!mUserdata::isDisallowedTo("cantEdit".$this->className))
			$BE = "";
		
		$options = "<div style=\"width:$widths[0]px;\">".$BE.$BD.implode("", $this->topButtons)."</div><div style=\"clear:left;height:10px;width:$widths[0]px;\"></div>";

		$appended = "";
		if(count($this->appended) > 0)
			foreach($this->appended as $k => $v)
				$appended .= $v->getHTML();
		
		$prepended = "";
		if(count($this->prepended) > 0)
			foreach($this->prepended as $k => $v)
				$prepended .= $v->getHTML();


/*
		if(count($this->CRMGUIappendedElements) > 0)
			foreach($this->CRMGUIappendedElements as $k => $v)
				$appended .= $v->getHTML();*/

		return $options.$prepended.$tab.$appended;
	}
	
	/**
	 *  This Method activates several features. Possible values for HTMLGUIX are:
	 *
	 *  CRMEditAbove
	 *
	 *  ---
	 *
	 * @param string $feature The feature to activate
	 * @param PersistentObject Collection $class
	 * @param $par1
	 * @param $par2
	 * @param $par3
	 */
	 // <editor-fold defaultstate="collapsed" desc="activateFeature">
	function activateFeature($feature, $class, $par1 = null, $par2 = null, $par3 = null){
		switch($feature){
			case "CRMEditAbove":
				$this->features["CRMEditAbove"] = "";

				$this->functionAbort = "\$j('#subFrameEditm%CLASSNAME').hide(); \$j('#subFramem%CLASSNAME').show();/* new Effect.BlindUp('subFrameEditm%CLASSNAME', {duration: 0.5});*/";
				$this->functionSave = "function() { \$j('#subFrameEditm%CLASSNAME').hide(); \$j('#subFramem%CLASSNAME').show(); contentManager.updateLine('%CLASSNAMEForm', %CLASSID, 'm%CLASSNAME'); }";
				$this->functionSaveNew = "function() { contentManager.reloadFrame('contentLeft'); }";
				if($par1 == true)
					$this->functionSave = $this->functionSaveNew;
			break;
			/*case "reloadOnNew":
				if($class instanceof PersistentObject AND $class->getID() == -1)
					$this->functionSaveNew = "function(transport){ contentManager.reloadOnNew({responseText: '%CLASSID'}, '%CLASSNAME'); }";
					#$this->setJSEvent("onSave","function(transport){ contentManager.reloadOnNew(transport, '".$class->getClearClass()."'); }");
			break;*/
		}
	}
}
?>
