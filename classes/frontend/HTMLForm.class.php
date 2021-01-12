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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class HTMLForm {
	protected $id;
	protected $fields;
	protected $types;
	private $labels;
	private $table;
	private $options;
	private $values;
	private $action;
	private $method;
	private $enctype;
	private $appendJs = "";
	private $descriptionField = array();
	private $descriptionFieldReplace1 = array();

	private $inputStyle = array();

	protected $saveMode;
	protected $saveButtonLabel;
	protected $saveButtonBGIcon;
	protected $saveButtonSubmit;
	protected $saveButtonOnclick;
	protected $saveButtonConfirm;
	protected $saveButtonType = "button";
	protected $saveClass;
	protected $saveAction;
	protected $saveButtonCustom;

	protected $abortButton;
	
	protected $onSubmit = "return false;";
	protected $onChange = array();
	protected $onBlur = array();
	protected $onKeyup = array();
	protected $onFocus = array();
	protected $useRecentlyChanged = false;
			
	private $spaces;
	private $spaceLines;

	private $endLV;

	private $translationClass;

	private $buttons;

	private $style;

	private $inputLineSyles = array();

	private $cols = 2;
	private $title;

	private $formTag = true;

	private $editable = true;
	
	private $autocomplete = array();
	private $printColon = true;
	private $callbacks = "";
	private $placeholders = array();
	
	public function printColon($b){
		$this->printColon = $b;
	}
	
	public function addJSEvent($fieldName, $event, $function){
		switch($event){
			case "onChange":
			case "onchange":
				if(!isset($this->onChange[$fieldName]))
					$this->onChange[$fieldName] = "";
				
				$this->onChange[$fieldName] .= $function;
			break;
			case "onBlur":
			case "onblur":
				if(!isset($this->onBlur[$fieldName]))
					$this->onBlur[$fieldName] = "";
				
				$this->onBlur[$fieldName] .= $function;
			break;
			case "onFocus":
			case "onfocus":
				if(!isset($this->onFocus[$fieldName]))
					$this->onFocus[$fieldName] = "";
				
				$this->onFocus[$fieldName] .= $function;
			break;
			case "onKeyup":
			case "onkeyup":
				if(!isset($this->onKeyup[$fieldName]))
					$this->onKeyup[$fieldName] = "";
				
				$this->onKeyup[$fieldName] .= $function;
			break;
			case "onEnter":
			case "onenter":
				if(!isset($this->onKeyup[$fieldName]))
					$this->onKeyup[$fieldName] = "";
				
				$this->onKeyup[$fieldName] .= "if(event.keyCode == 13) { ".$function." }";;
			break;
		}
	}

	public function setAutoComplete($fieldName, $targetClass, $onSelectionFunction = "", $thirdParameter = null){
		$this->autocomplete[$fieldName] = array($targetClass, $onSelectionFunction, $thirdParameter);
	}
	
	public function setAction($action){
		$this->action = $action;
	}

	public function setMethod($method){
		$this->method = $method;
	}
	
	public function maxHeight($height){
		$this->table->maxHeight($height);
	}

	public function style($style){
		$this->style = $style;
	}

	public function cols($cols, $widths = null){
		if($cols != 2 AND $cols != 4 AND $cols != 1) return;

		$widths = Aspect::joinPoint("changeWidths", $this, __METHOD__, null, $widths);

		$this->table = new HTMLTable($cols, $this->title);
		$this->cols = $cols;

		if($cols == 4){
			if($widths == null) $widths = array(700, 132, 218);
			if(is_numeric($widths[0]))
				$widths[0] .= "px";
			
			$this->table->setTableStyle("width:$widths[0];max-width:$widths[0];margin-left:10px;");

			$this->table->addColStyle(1, "width:$widths[1]px;");
			$this->table->addColStyle(2, "width:$widths[2]px;");
			$this->table->addColStyle(3, "width:$widths[1]px;");
			$this->table->addColStyle(4, "width:$widths[2]px;");
			
			$this->table->setColClass(1, "backgroundColor3");
			$this->table->setColClass(2, "backgroundColor2");
			$this->table->setColClass(3, "backgroundColor3");
			$this->table->setColClass(4, "backgroundColor2");
		}
	}

	public function hideIf($fieldName, $operator, $value, $event, $fieldsToHide){
		switch ($operator){
			case "=":
				if($this->values[$fieldName] == $value){
					foreach($fieldsToHide AS $v)
						$this->inputLineStyle($v, "display:none;");

				}
				$operator = "==";
			break;
			case "!=":
				if($this->values[$fieldName] != $value){
					foreach($fieldsToHide AS $v)
						$this->inputLineStyle($v, "display:none;");

				}
			break;
		}

		if($this->types[$fieldName] == "checkbox")
			$this->addJSEvent($fieldName, $event, "if(".($value == "1" ? "" : "!")."this.checked) contentManager.toggleFormFields('hide', ['".implode("','", $fieldsToHide)."'], '$this->id'); else contentManager.toggleFormFields('show', ['".implode("','", $fieldsToHide)."'], '$this->id');");

		if($this->types[$fieldName] == "select"){
			$this->addJSEvent($fieldName, $event, "if(\$j(this).val() $operator '$value') contentManager.toggleFormFields('hide', ['".implode("','", $fieldsToHide)."'], '$this->id'); else contentManager.toggleFormFields('show', ['".implode("','", $fieldsToHide)."'], '$this->id');");
		
			$this->appendJs .= "\$j('#$this->id [name=$fieldName]').trigger('change');";
		}
	}

	public function inputLineStyle($fieldName, $style){
		$this->inputLineSyles[$fieldName] = $style;
	}

	public function setInputStyle($fieldName, $style){
		$this->inputStyle[$fieldName] = $style;
	}
	
	public function setFields(array $fields){
		$this->fields = $fields;
	}

	public function setPlaceholder($fieldName, $style){
		$this->placeholders[$fieldName] = $style;
	}

	public function __construct($formID, $fields, $title = null, $virtualFields = array()){
		$this->id = $formID;

		if(is_array($fields))
			$this->fields = $fields;
		if($fields instanceof PersistentObject){
			$fields->loadMeOrEmpty();
			$this->fields = PMReflector::getAttributesArrayAnyObject($fields->getA());
		}

		$title = T::_($title);
		
		$this->virtualFields = $virtualFields;
		$this->types = array();
		$this->labels = array();
		$this->options = array();
		$this->values = array();
		$this->endLV = array();
		$this->spaces = array();
		$this->spaceLines = array();
		$this->table = new HTMLTable(2, $title);
		$this->table->setColClass(1, "backgroundColor3");
		$this->table->setColClass(2, "backgroundColor2");
		$this->title = $title;
		$this->saveMode = null;
		#$this->onSubmit = null;
		$this->buttons = array();
	}
	
	public function useRecentlyChanged(){
		$this->useRecentlyChanged = true;
	}

	public function hasFormTag($bool){
		$this->formTag = $bool;
	}

	public function addFieldButton($fieldName, $B){
		$this->buttons[$fieldName] = $B;
	}

	public function addSaveDefaultButton($fieldName){
		$B = new Button("als Standard-Wert speichern", "./images/i2/save.gif");
		$B->rme("mUserdata","","setUserdata",array("'DefaultValue$this->id$fieldName'","$('$this->id').$fieldName.value"),"checkResponse(transport);");
		$B->type("icon");
		$B->style("float:right;");
		if(!isset($this->values[$fieldName])) {
			$U = new mUserdata();
			$this->values[$fieldName] = $U->getUDValue("DefaultValue$this->id$fieldName", "");
		}
		$this->buttons[$fieldName] = $B;
	}

	public function getAllFields(){
		return $this->fields;
	}

	public function translate(iTranslation $translationClass){
		$this->translationClass = $translationClass;
		if($translationClass == null) return;

		$labels = $this->translationClass->getLabels();
		#$labelDescriptions = $this->translationClass->getLabelDescriptions();
		#$fieldDescriptions = $this->translationClass->getFieldDescriptions();
		#$this->texts = $this->languageClass->getText();

		$this->table->setCaption($this->translationClass->getEditCaption());
		$this->saveButtonLabel = $this->translationClass->getSaveButtonLabel();

		if($labels != null)
			foreach($labels AS $k => $v)
				$this->setLabel($k, $v);

		/*if($labelDescriptions != null)
			foreach($labelDescriptions AS $k => $v)
				$gui->setLabelDescription($k, $v);

		if($fieldDescriptions != null)
			foreach($fieldDescriptions AS $k => $v)
				$gui->setFieldDescription($k, $v);*/
	}

	public function setSaveMultiCMS($saveButtonLabel, $saveButtonBGIcon, $class, $action = "", $onSuccessFuntion = "", $checkIfValid = false, $onErrorFunction = ""){
		$this->saveMode = "multiCMS";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;
		$this->saveClass = $class;
		if($action != "")
			$this->saveAction = $action;
		else
			$this->saveAction = $this->id;
		$this->saveButtonSubmit = ($checkIfValid ? "if($('#$this->id').valid()) " : "")."multiCMS.formHandler('$this->id', ".($onSuccessFuntion != "" ? "$onSuccessFuntion" : "function(){}")."".($onErrorFunction != "" ? ", $onErrorFunction" : "").");";
		$this->onSubmit = "return false;";
	}

	public function setSaveRMEP($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;
		$this->saveButtonSubmit = "rmeP('$targetClass', '$targetClassId', '$targetMethod', joinFormFieldsToString('$this->id'), '$onSuccessFunction');";
	}
	
	public function setSaveCustomerPage($saveButtonLabel, $saveButtonBGIcon = null, $checkIfValid = false, $onSuccessFunction = "function(){ }"){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;
		$this->saveButtonSubmit = ($checkIfValid ? "if($('#$this->id').valid()) " : "")."CustomerPage.rme('handleForm', $('#$this->id').serialize(), function(transport){ var ons = $onSuccessFunction; if(CustomerPage.checkResponse(transport)) ons(transport); });";
		
		$this->onSubmit = $this->saveButtonSubmit." return false;";
	}
	
	public function setAbortCustomerPage($label, $onclick, $icon = null){
		$this->abortButton = array($label, $onclick, $icon);
	}
	
	public function setSaveCustom($saveButtonLabel, $saveButtonBGIcon = null, $onClick = ""){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;
		$this->saveButtonSubmit = $onClick;
	}

	public function setSaveJSON($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction = null){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = T::_($saveButtonLabel);
		$this->saveButtonBGIcon = $saveButtonBGIcon;

		if($this->useRecentlyChanged){
			$RC = "\$j('#$this->id .recentlyChanged').removeClass('recentlyChanged');";
			
			if($onSuccessFunction != null AND strpos($onSuccessFunction, "function(") === 0)
				$onSuccessFunction = substr($onSuccessFunction, 0, -1).$RC."}";
			else
				$onSuccessFunction .= $RC;
		}
		
		if($onSuccessFunction != null AND strpos($onSuccessFunction, "function(") !== 0)
			$onSuccessFunction = "function(transport){ $onSuccessFunction }";
		
		$values = "encodeURIComponent(JSON.stringify(contentManager.formContent('$this->id')))";
		#$allFields = "JSON.stringify(\$j('#$this->id input, #$this->id select, #$this->id textarea').toArray())";
		$this->saveButtonSubmit = "contentManager.rmePCR('$targetClass', '$targetClassId', '$targetMethod', $values ".($onSuccessFunction != null ? ", $onSuccessFunction" : "").");";
		$this->onSubmit = $this->saveButtonSubmit."return false;";
	}
	
	public function setSaveRMEPCR($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction = "", $onFailureFunction = "function(){}"){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = T::_($saveButtonLabel);
		$this->saveButtonBGIcon = $saveButtonBGIcon;


		if($this->useRecentlyChanged){
			$RC = "\$j('#$this->id .recentlyChanged').removeClass('recentlyChanged');";
			
			if($onSuccessFunction != null AND strpos($onSuccessFunction, "function(") === 0)
				$onSuccessFunction = substr($onSuccessFunction, 0, -1).$RC."}";
			else
				$onSuccessFunction .= $RC;
		}
		
		if($onSuccessFunction != "" AND stripos($onSuccessFunction, "function(") === false)
			$onSuccessFunction = "function(transport){ $onSuccessFunction }";
		
		$values = "";
		foreach($this->fields AS $f){
			if(!isset($this->types[$f]) OR ($this->types[$f] != "checkbox" AND $this->types[$f] != "select-multiple"))
				$values .= ($values != "" ? ", " : "")."\$('$this->id').$f.value";
			elseif($this->types[$f] == "checkbox")
				$values .= ($values != "" ? ", " : "")."\$('$this->id').$f.checked ? '1' : '0'";
			elseif($this->types[$f] == "select-multiple")
				$values .= ($values != "" ? ", " : "")."\$j('#$this->id [name=$f]').val().join(';:;')";
		}
		
		foreach($this->virtualFields AS $f){
			if(!isset($this->types[$f]) OR $this->types[$f] != "checkbox")
				$values .= ($values != "" ? ", " : "")."\$('$this->id').$f.value";
			else
				$values .= ($values != "" ? ", " : "")."\$('$this->id').$f.checked ? '1' : '0'";
		}
		
		$this->saveButtonSubmit = "contentManager.rmePCR('$targetClass', '$targetClassId', '$targetMethod', [$values]".($onSuccessFunction != "" ? ", $onSuccessFunction" : ", function(){}").", '', true, $onFailureFunction);";
		$this->onSubmit = $this->saveButtonSubmit."return false;";
	}
	
	public function setSaveWindowRMEPCR($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction = null){
		$this->setSaveRMEPCR($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction);
		
		$this->saveButtonSubmit = str_replace("contentManager.rmePCR", "windowWithRme", $this->saveButtonSubmit);
		$this->onSubmit = str_replace("contentManager.rmePCR", "windowWithRme", $this->onSubmit);
	}

	public function setSaveContextMenu($class, $targetMethod, $onSuccessFunction = ""){
		$targetClass = str_replace("GUI", "", get_class($class));
		
		$this->setSaveRMEPCR("speichern", "./images/i2/save.gif", $targetClass, $class->getID(), $targetMethod, "function(){ phynxContextMenu.stop(); $onSuccessFunction }");
	}

	public function setSaveConfirmation($question){
		$this->saveButtonConfirm = $question;
	}

	public function setType($fieldName, $type, $value = null, $options = null){
		$this->types[$fieldName] = $type;
		$this->options[$fieldName] = $options;
		if($value != null) $this->values[$fieldName] = $value;
	}

	public function setValue($fieldName, $value){
		$this->values[$fieldName] = $value;
	}

	public function setValuesJSON($json){
		$json = trim($json);
		if($json == "[]" OR $json == "")
			return;
		
		$data = json_decode($json);
		$fields = $this->getAllFields();
		
		foreach($data AS $field){
			if(!in_array($field->name, $fields))
				continue;
			
			$this->setValue($field->name, $field->value);
		}
	}
	
	public function setValues(PersistentObject $PO){
		$fields = $this->getAllFields();
		foreach($fields AS $k => $v)
			$this->setValue($v, $PO->A($v));
	}

	public function setLabel($fieldName, $label){
		$this->labels[$fieldName] = $label;
	}

	/*public function setSaveUpload($className, $label, $onSuccessFunction = 'function(){}'){
		$this->action = "./interface/set.php";
		$this->method = "post";
		$this->enctype = "multipart/form-data";

		$this->fields[] = "id";
		$this->fields[] = "class";
		$this->fields[] = "saveToAttribute";

		$this->setValue("class", "TempFile");

		$this->setType("class", "hidden");
		$this->setType("id", "hidden");
		$this->setType("saveToAttribute", "hidden");

		$this->saveMode = "class";
		$this->saveButtonLabel = $label;
		$this->saveButtonBGIcon = "./images/i2/save.gif";
		$this->saveClass = "";
		$this->saveAction = "";
		#$this->saveButtonSubmit = "";
		$this->saveButtonType = "submit";
		$this->onSubmit = "return AIM.submit($('$this->id'), {'onComplete' : $onSuccessFunction});";
	}*/

	public function setSaveBericht($berichtClass){
		$this->saveMode = "Bericht";
		$this->saveButtonLabel = T::_("Einstellungen speichern");
		$this->saveButtonBGIcon = "";
		$this->saveClass = "";
		$this->saveAction = "";
		$this->saveButtonOnclick = "saveBericht('".get_class($berichtClass)."', '$this->id');";
	}
	public function setSaveBericht2($berichtClass){
		$this->saveMode = "Bericht";
		$this->saveButtonLabel = "Einstellungen speichern";
		$this->saveButtonBGIcon = "";
		$this->saveClass = "";
		$this->saveAction = "";
		$this->saveButtonOnclick = "Bericht.save('".get_class($berichtClass)."', '$this->id');";
	}

	public function isEditable($editable){
		$this->editable = $editable;
	}

	public function setSaveClass($className, $classID, $onSuccessFunction = '', $label = ''){
		$this->saveMode = "class";
		$this->saveButtonLabel = T::_("%1 speichern", $label);
		$this->saveButtonBGIcon = "./images/i2/save.gif";
		$this->saveClass = "";
		$this->saveAction = "";
		$this->saveButtonSubmit = "saveClass('$className', '$classID', $onSuccessFunction, '$this->id', function(id) { /*CALLBACKS*/ })";
	}

	public function insertSpaceAbove($fieldName, $label = ""){
		$this->spaces[$fieldName] = $label;
	}

	public function getSpaces(){
		return $this->spaces;
	}

	public function getTypes(){
		return $this->types;
	}
	
	public function getOptions(){
		return $this->options;
	}
	
	public function insertLineAbove($fieldName, $label = ""){
		$this->spaceLines[$fieldName] = $label;
	}


	public function insertField($where, $fieldName, $insertedFieldName){
		if($where == "after")
			$add = 1;

		if($where == "before")
			$add = 0;

		$resetKeys = array();
		foreach($this->fields AS $v)
			$resetKeys[] = $v;
		
		$this->fields = $resetKeys;
		
		$first = array_splice($this->fields, 0, array_search($fieldName, $this->fields) + $add);
		$last = array_splice($this->fields, array_search($fieldName, $this->fields));

		$this->fields = array_merge($first, array($insertedFieldName), $last);
		
	}
	
	public function setDescriptionField($fieldName, $description, $replace1 = null){
		$this->descriptionField[$fieldName] = $description;
		$this->descriptionFieldReplace1[$fieldName] = $replace1;
	}


	/**
	 * @return HTMLTable
	 */
	public function getTable(){
		return $this->table;
	}

	public function addTableEndLV($label, $value){
		$this->endLV[] = array($label, $value);
	}

	private function getInput($v){
		if(!isset($this->types[$v]) OR $this->types[$v] != "parser"){
			
			if(isset($this->types[$v]) AND ($this->types[$v] == "tinyMCE" OR $this->types[$v] == "TextEditor" OR $this->types[$v] == "nicEdit")){
				$options = array($this->id, $v);
				if(isset($this->options[$v]))
					foreach($this->options[$v] AS $ov)
						$options[] = $ov;
				
				$this->options[$v] = $options;
			}

			$Input = new HTMLInput(
				$v,
				isset($this->types[$v]) ? $this->types[$v] : "text",
				isset($this->values[$v]) ? $this->values[$v] : null,
				isset($this->options[$v]) ? $this->options[$v] : null);

			if(isset($this->onChange[$v]))
				$Input->onchange($this->onChange[$v]);

			if(isset($this->onBlur[$v]))
				$Input->onblur($this->onBlur[$v]);

			if(isset($this->onFocus[$v]))
				$Input->onfocus($this->onFocus[$v]);

			if(isset($this->onKeyup[$v]))
				$Input->onkeyup($this->onKeyup[$v]);

			if(isset($this->inputStyle[$v]))
				$Input->style($this->inputStyle[$v]);

			if(isset($this->autocomplete[$v]))
				$Input->autocomplete($this->autocomplete[$v][0], $this->autocomplete[$v][1], false, $this->autocomplete[$v][2]);
			
			if(isset($this->placeholders[$v]))
				$Input->placeholder($this->placeholders[$v]);
			
			$Input->isDisplayMode(!$this->editable);
		} else {
			if($this->options[$v][0] instanceof Closure){
				$f = $this->options[$v][0];
				$Input = $f(isset($this->values[$v]) ? $this->values[$v] : null, "", isset($this->options[$v][1]) ? $this->options[$v][1] : null, (isset($this->options[$v][2]) ? $this->options[$v][2] : null));
			} else {
				$method = explode("::", $this->options[$v][0]);
				$Input = Util::invokeStaticMethod($method[0], $method[1], array(isset($this->values[$v]) ? $this->values[$v] : null, "", isset($this->options[$v][1]) ? $this->options[$v][1] : null, (isset($this->options[$v][2]) ? $this->options[$v][2] : null)));
			}
		}

		return $Input;
	}

	public function getLabels(){
		return $this->labels;
	}
	
	private function getCustomButton($v, $Input){
		$B = "";
		if(!isset($this->types[$v]) OR $this->types[$v] != "parser"){
			if(isset($this->buttons[$v])) {
				$B = $this->buttons[$v];
				if(
					(!isset($this->types[$v]) 
						OR $this->types[$v] == "text" 
						OR $this->types[$v] == "select" 
						OR $this->types[$v] == "readonly") 
					AND (isset($this->inputStyle[$v]) AND strpos($this->inputStyle[$v], "width") === false))
					$Input->style("width:87%;");
			}
		}

		return $B;
	}

	private $saveButton;
	public function saveButton(){
		switch($this->saveMode){
			case "class":
				if($this->saveButton != null)
					return $this->saveButton;
				
				$S = new HTMLInput("currentSaveButton", $this->saveButtonType, $this->saveButtonLabel);
				if($this->saveButtonBGIcon != "")
					$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");

				if($this->saveButtonSubmit != null)
					$S->onclick(str_replace("/*CALLBACKS*/", $this->callbacks, $this->saveButtonSubmit));
				
				$this->saveButton = $S;
				return $S;
			break;
		}
	}
	
	public function  __toString() {
		$hiddenFields = "";

		if($this->cols == 2 OR $this->cols == 1)
			foreach($this->fields as $k => $v){
				if(isset($this->spaceLines[$v])){
					$this->table->addRow(array("<hr />"));
					#$this->table->addRowStyle("font-weight:bold;");
					#$this->table->addCellStyle(1, "padding-top:20px;");
					$this->table->addRowColspan(1, 2);
					$this->table->addRowClass("FormSeparatorWithoutLabel");

				}
				if(isset($this->spaces[$v]) AND $this->spaces[$v] != ""){
					$this->table->addRow(array(T::_($this->spaces[$v])));
					#$this->table->addRowStyle("font-weight:bold;");
					#$this->table->addCellStyle(1, "padding-top:20px;");
					$this->table->addRowColspan(1, 2);
					$this->table->addRowClass("FormSeparatorWithLabel");

				}
				if(isset($this->spaces[$v]) AND $this->spaces[$v] == ""){
					$this->table->addRow(array(""));
					$this->table->addRowClass("backgroundColor0");
					$this->table->addRowColspan(1, 2);
					$this->table->addRowClass("FormSeparatorWithoutLabel");
				}

				$Input = $this->getInput($v);
				$B = $this->getCustomButton($v, $Input);
				
				if(is_object($Input) AND $Input instanceof HTMLInput){
					$InputS = $Input->__toString();
				
					$this->callbacks .= $Input->getCallback();
				} else
					$InputS = $Input;
				
				
				if(!isset($this->types[$v]) OR $this->types[$v] != "hidden"){

					if($this->cols == 2) $this->table->addLV(
						T::_(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).($this->printColon ? ":" : ""),
						$B.$InputS.(isset($this->descriptionField[$v]) ? "<br><small style=\"color:grey;\">".T::_($this->descriptionField[$v], isset($this->descriptionFieldReplace1[$v]) ? $this->descriptionFieldReplace1[$v] : null)."</small>" : ""));

					if($this->cols == 1) {
						
						if(isset($this->labels[$v]) AND $this->labels[$v] != ""){
							$this->table->addRow(
							"<label ".($this->cols == 1 ? "style=\"width:100%;\"" : "").">".$this->labels[$v].":</label>");
							$this->table->addRowClass("backgroundColor3");
						}
						
						if(!isset($this->labels[$v])){
							$this->table->addRow(
							"<label ".($this->cols == 1 ? "style=\"width:100%;\"" : "").">".ucfirst($v).":</label>");
							$this->table->addRowClass("backgroundColor3");
						}
						
						
						$this->table->addRow(
						$B.$InputS.(isset($this->descriptionField[$v]) ? "<br><small style=\"color:grey;\">".$this->descriptionField[$v]."</small>" : ""));
						$this->table->addRowClass("backgroundColor0");
					}

					if(isset($this->inputLineSyles[$v]))
						$this->table->addRowStyle($this->inputLineSyles[$v]);
				}
				else $hiddenFields .= $InputS;
			}

		if($this->cols == 4){
			$row = array();
			foreach($this->fields as $k => $v){
				$Input = $this->getInput($v);

				if(isset($this->types[$v]) AND $this->types[$v] == "hidden") {
					$hiddenFields .= $Input;
					continue;
				}
				
				if(isset($this->spaces[$v])){
					if($this->spaces[$v] == ""){
						if(count($row) == 0) {
							$this->table->addRow(array("", "", "", ""));
							$this->table->addRowClass("backgroundColor0");
						}
						if(count($row) == 2) {
							$row[] = "";
							$row[] = "";
							$this->table->addRow($row);
							$row = array();
							
							$this->table->addRow(array("", "", "", ""));
							$this->table->addRowClass("backgroundColor0");
						}
					} else {
						if(count($row) == 0) {
							$this->table->addRow(array($this->spaces[$v], "", "", ""));
							$this->table->addRowClass("backgroundColor0");
						}
						if(count($row) == 2) {
							$row[] = "";
							$row[] = "";
							$this->table->addRow($row);
							$row = array();
							
							$this->table->addRow(array($this->spaces[$v], "", "", ""));
							$this->table->addRowClass("backgroundColor0");
						}
					}

					#$this->table->addRow($row);
					
					#$row = array();
				}

				$B = $this->getCustomButton($v, $Input);

				$row[] = "<label>".T::_(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).":</label>";
				$row[] = $B.$Input.(isset($this->descriptionField[$v]) ? "<br /><small style=\"color:grey;\">".$this->descriptionField[$v]."</small>" : "");
				/*if(!isset($this->types[$v]) OR $this->types[$v] != "hidden"){
					$this->table->addLV(
						(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).":",
						$B.$Input);

					if(isset($this->inputLineSyles[$v]))
						$this->table->addRowStyle($this->inputLineSyles[$v]);
				}
				else $hiddenFields .= $Input;*/


				if(count($row) == 4){
					$this->table->addRow($row);
					$row = array();
				}
			}

			if(count($row) == 2){
				$row[] = "";
				$row[] = "";
				$this->table->addRow($row);
			}
		}

		foreach($this->endLV AS $k => $v)
			$this->table->addLV($v[0],$v[1]);
		
		if($this->saveMode != null)
			switch($this->saveMode){
				case "custom":
					$this->table->addRow(array($this->saveButtonCustom));
					$this->table->addRowColspan(1, $this->cols);
				break;
			
				case "class":

					$S = $this->saveButton();
					
					$this->table->addRow(array($S));
					$this->table->addRowColspan(1, $this->cols);
				break;

				case "multiCMS":
					$S = new HTMLInput("submitForm", "submit", $this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");

					if($this->saveButtonSubmit != null)
						$S->onclick(str_replace("/*CALLBACKS*/", $this->callbacks, $this->saveButtonSubmit));
					
					$action = new HTMLInput("action", "hidden", $this->saveAction);
					$handler = new HTMLInput("HandlerName", "hidden", $this->saveClass);
					$return = new HTMLInput("returnP", "hidden", "/".$_GET["permalink"]);

					if($this->cols > 1) $this->table->addRow(array("",$S.$action.$handler.$return));
					else $this->table->addRow(array($S.$action.$handler.$return));
				break;

				case "rmeP":
					$S = new HTMLInput("submitForm", "button", $this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");

					$S->onclick(($this->saveButtonConfirm != null ? "if(confirm('".$this->saveButtonConfirm."')) " : "").$this->saveButtonSubmit);
					$S->setClass("submitFormButton borderColor1");
					
					$C = "";
					if($this->abortButton != null){
						$C = new HTMLInput("abortForm", "button", $this->abortButton[0]);
						if($this->abortButton[2] != null)
							$C->style("background-image:url({$this->abortButton[2]});background-position:98% 50%;background-repeat:no-repeat;");

						$C->onclick($this->abortButton[1]);
							
						$C->setClass("abortFormButton borderColor1");
					}
					
					$this->table->addRow(array($S.$C));
					$this->table->addRowColspan(1, 2);
				break;

				case "Bericht":
					$S = new HTMLInput("submitForm", "button", $this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");
					$S->onclick($this->saveButtonOnclick);

					$this->table->addRow(array($S));
					$this->table->addRowColspan(1, 2);
				break;
			}


		$html = "";

		if($this->formTag) $html .= "
	<form
		id=\"$this->id\"
		".($this->action != null ? "action=\"$this->action\"" : "")."
		".($this->method != null ? "method=\"$this->method\"" : "")."
		".($this->enctype != null ? "enctype=\"$this->enctype\"" : "")."
		".($this->onSubmit != null ? "onsubmit=\"$this->onSubmit\"" : "")."
		".($this->style != null ? "style=\"$this->style\"" : "").">";

		$html .= $this->table;


		$html .= $hiddenFields;

		if($this->formTag) $html .= "
	</form>";

		return $html.($this->appendJs != "" ? OnEvent::script($this->appendJs) : "").($this->useRecentlyChanged ? GUIFactory::editFormOnchangeTest($this->id) : "");
	}

	public function getHTML(){
		return $this->__toString();
	}
}
?>