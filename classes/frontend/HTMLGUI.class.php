<?php
/**
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

class HTMLGUI implements icontextMenu {
	protected $attributes = null;
	private $dontShow = array();
	private $isNew;
	protected $name = "Noname";
	protected $labels = array();
	private $labelDescriptions = array();
	private $fieldDescriptions = array();
	
	protected $values = array();
	protected $options = array();
	private $optgroups = array();
	
	protected $types = array();
	private $events = array();
	private $style = array();
	private $inputStyle = array();
	private $saveButtonEvent;
	protected $singularClass = "none";
	protected $singularName = "none";
	protected $parsers = array();
	protected $parserParameters = array();
	private $dgParser = "";
	private $dgParserParameters = array();
	protected $showAttributes = array();
	#private $notEditable = array();
	
	protected $colStyles = array();
	private $colClasses = array();
	private $shownCols = array();
	
	private $addedRows = array();
	private $addedCols = array();
	
	private $selectionRow = "";
	protected $selectionFunctions = "";
	private $hiddenInputs = "";
	
	protected $onlyDisplayMode = false;
	protected $deleteInDisplayMode = false;
	protected $editInDisplayMode = false;
	protected $editInDisplayModeTarget = "";
	protected $multiEditMode = null;
	
	protected $displayGroupBy = null;
	protected $displayGroup = null;
	
	protected $showHiddenCategoriesWarning = false;
	protected $showFilteredCategoriesWarning = null;
	#private $newClassButtonOnsuccessFunction = "";
	#private $forwardType = "";
	#private $forwardMode = "";
	
	protected $multiPageMode = array();
	
	private $autoCompletion = array();
	
	private $multiInputMode = false;
	
	private $FormID = "AjaxForm";
	
	protected $quickSearchPlugin = "";
	private $editedID = -1;
	protected $insertSpaceBefore = array();
	
	protected $JSOnDelete;
	protected $JSOnNew;
	protected $JSOnEdit;
	protected $JSOnSave;
	
	private $labelCaption;
	private $labelSaveButton;
	
	private $className;
	private $classParentName;
	#private $infoDropDown = array();
	
	protected $languageClass;
	protected $texts;
	
	private $RowIDPrefix = "BrowserMain";
	
	protected $object;
	
	private $replaceSaveButton;
	
	protected $buttonsNextToFields = array();
	
	protected $appendedElements = array();
	protected $prependedElements = array();
	protected $tip = "";
	
	public function appendElement($element){
		$this->appendedElements[] = $element;
	}

	public function prependElement($element){
		$this->prependedElements[] = $element;
	}
	
	public function tip(){
		$targetClass = $this->object->getClearClass();
		
		$this->tip = HTMLGUIX::tipJS($targetClass);
	}

	public function insertAttribute($where, $fieldName, $insertedFieldName){
		if($where == "after")
			$add = 1;

		if($where == "before")
			$add = 0;

		$first = array_splice($this->showAttributes, 0, array_search($fieldName, $this->showAttributes) + $add);
		$last = array_splice($this->showAttributes, array_search($fieldName, $this->showAttributes));

		$this->showAttributes = array_merge($first, array($insertedFieldName), $last);
	}
	
	/**
	 *  This Method activates several features. Possible values are:
	 *  
	 *  reloadOnNew:
	 *  Instantly reloads a newly created entry
	 * 
	 *  Make sure to call $this->setEchoIDOnNew(true); in the constructor of
	 *  the related class. Works on instances of PersistentObject and Collection.
	 * 
	 *  no parameters are required
	 *  
	 *  ---
	 *  
	 *  
	 *  replaceSaveButton:
	 *  Replaces the default edit save/new-button with a user-defined button of class Button
	 *  
	 *  par1: Button
	 *  
	 *  ---
	 *  
	 *  
	 *  addSaveDefaultButton:
	 *  Creates a button next to an input element to save the current value which will then be loaded by default
	 *  
	 *  par1: The name of the input element
	 *  
	 *  ---
	 *  
	 *  
	 *  addCustomButton:
	 *  Adds a custom button next to an input element
	 *  
	 *  par1: The name of the input element
	 *  
	 *  par2: The button of class Button
	 *  
	 *  ---
	 *  
	 *  
	 *  addAnotherLanguageButton:
	 *  Creates a button next to an input element to add an alternate language
	 *  
	 *  par1: The name of the input element
	 *  
	 *  ---
	 *  
	 *  
	 *  Each par variable may be used differently with each feature
	 *  
	 * @param string $feature The feature to activate
	 * @param PersistentObject Collection $class 
	 * @param $par1 
	 * @param $par2
	 * @param $par3
	 */
	public function activateFeature($feature, $class, $par1 = null, $par2 = null, $par3 = null){
		switch($feature){
			
			case "reloadOnNew":
				if($class instanceof PersistentObject AND $class->getID() == -1)
					$this->setJSEvent("onSave","function(transport){ contentManager.reloadOnNew(transport, '".$class->getClearClass()."'); }");
				if($class instanceof Collection)
					$this->setJSEvent("onNew","function(transport){ contentManager.reloadFrameRight(); }");
				
			break;
			
			case "replaceSaveButton":
				$this->replaceSaveButton = $par1;
			break;
			
			case "addSaveDefaultButton":
				$B = new Button("als Standard-Wert speichern", "./images/i2/save.gif");
				$B->rme("mUserdata","","setUserdata",array("'DefaultValue".$class->getClearClass()."$par1'","$('$par1').value"),"checkResponse(transport);");
				$B->type("icon");
				$B->style("float:right;");
				$this->setInputStyle($par1,"width:90%;");
				$this->buttonsNextToFields[$par1] = $B;
			break;
			
			case "addCustomButton":
				$par2->style("float:right;");
				$par2->type("icon");
				$this->setInputStyle($par1,"width:90%;");
				$this->buttonsNextToFields[$par1] = $par2;
			break;
			
			case "addAnotherLanguageButton":
				$B = new Button("andere Sprachen", "./images/i2/sprache.png");
				if($class->getID() != -1) $B->rme("mMultiLanguage","","getPopupHTML",array("'".$class->getClearClass()."'","'".$class->getID()."'","'".$par1."'"),"Popup.create(\'".$class->getID()."\', \'altLang".$class->getClearClass()."\', \'alternative Sprachen\'); Popup.update(transport, \'".$class->getID()."\', \'altLang".$class->getClearClass()."\');");
				
				else $B->onclick("alert('Sie müssen den Artikel zuerst speichern, bevor Sie Übersetzungen eintragen können')");
				$B->type("icon");
				$B->style("float:right;");
				$this->setInputStyle($par1,"width:90%;");
				$this->buttonsNextToFields[$par1] = $B;
			break;
		}
	}
	
	function __construct(){
		$this->languageClass = $this->loadLanguageClass("HTML");
	}

	/**
	 * Use this method to set the Object you want to create a GUI for.
	 * 
	 * @param Collection PersistentObject $object
	 */
	public function setObject($object, $name = ""){
		if($object instanceof PersistentObject){
			$this->object = $object;
			$this->setAttributes($object->getA());
		}
		
		if($object instanceof Collection){
			$this->object = $object;
			$this->setAttributes($object->getCollector());
			$this->setCollectionOf($object->getCollectionOf(), $name);
		}
	}
	
	/**
	 * Finds, loads and returns the language class for the given class name
	 * 
	 * @param string $class
	 * @return unknown_type
	 */
	function loadLanguageClass($class){
		try {
			$n = $class."_".$_SESSION["S"]->getUserLanguage();
			$c = new $n();
		} catch(ClassNotFoundException $e){
			try {
				$n = $class."_de_DE";
				$c = new $n();
			} catch(ClassNotFoundException $e){
				return null;
			}
		}
		return $c;
	}
	
	function setRowIDPrefix($prefix){
		$this->RowIDPrefix = $prefix."Browser";
	}
	
	function setLabelSaveButton($l){
		$this->labelSaveButton = $l;
	}
	
	function setLabelCaption($l){
		$this->labelCaption = $l;
	}
	
	/**
	 * Sets the attributes which are used to fill the tables with
	 * 
	 * Usually called by the setObject-Method
	 * 
	 * @param Attributes $A
	 */
	function setAttributes($A) {
		$this->attributes = $A;
	}
	
	/**
	 * Sets a JavaScript function(){ } for the following events:
	 * 
	 * onDelete
	 * 
	 * onNew
	 * 
	 * onEdit
	 * 
	 * onSave
	 * 
	 * @param string $event
	 * @param string $function
	 */
	function setJSEvent($event, $function){
		switch($event){
			case "onDelete":
				$this->JSOnDelete = $function;
			break;
			case "onNew":
				$this->JSOnNew = $function;
			break;
			case "onEdit":
				$this->JSOnEdit = $function;
			break;
			case "onSave":
				$this->JSOnSave = $function;
			break;
		}
		
	}
	
	/**
	 * Inserts some space above the given attribute.
	 * If $label is set, it will be displayed above the attribute.
	 * If $tab is set to true, all attributes below will be hidden 
	 * and may be displayed again by clicking on the label.
	 * 
	 * @param string $attributeName
	 * @param string $label
	 * @param bool $tab
	 */
	function insertSpaceAbove($attributeName, $label = "", $tab = false){
		$this->insertSpaceBefore[$attributeName] = $label;
		$this->tabs[$attributeName] = $tab;
	}

	/**
	 * Sets the Name shown in the caption of the table
	 * 
	 * @param string $n
	 * @return void
	 */
	function setName($n){
		$this->name = $n;
	}
	
	function setIsNew($n){
		$this->isNew = $n;
	}
	
	/**
	 * The specified attribute is not shown in the table
	 * 
	 * @param string $a
	 */
	function hideAttribute($a){ $this->dontShow[$a] = 1; }
	
	function isQuickSearchable($plugin){
		$this->quickSearchPlugin = $plugin;
	}
	
	/**
	 * Changes the label displayed for each attribute.
	 * Per default the attribute name is shown.
	 * 
	 * @param string $attributeName
	 * @param string $label
	 */
	function setLabel($attributeName,$label) {
		$this->labels[$attributeName] = $label;
	}
	
	function setLabelDescription($attributeName,$description) {
		$this->labelDescriptions[$attributeName] = $description;
	}
	
	function setFieldDescription($attributeName,$description) {
		$this->fieldDescriptions[$attributeName] = $description;
	}
	
	/**
	 * If setType is used to display select, radio or checkboxes you may add the options here
	 * 
	 * @param string $attributeName
	 * @param string[] $values
	 * @param string[] $options
	 */
	function setOptions($attributeName, $values, $options = null) {
		$this->options[$attributeName] = $options;
		$this->values[$attributeName] = $values;
	}
	
	/**
	 * Here you can change the type of the input-field.
	 * Defaults to text but may be one of these:
	 * 
	 *  select
	 *  
	 *  radio
	 *  
	 *  hidden
	 *  
	 *  password
	 *  
	 *  textarea
	 *  
	 *  TextEditor
	 *  
	 *  TextEditor64: May be used for HTML as the contents are transmitted base64 encoded
	 * 
	 *  readonly
	 *  
	 *  HTMLEditor: Only works if plugin Wysiwyg is installed
	 *  
	 *  For the types select and radio you need to set the options with setOptions()
	 * 
	 * @param string $attributeName
	 * @param string $type
	 */
	function setType($attributeName, $type) {
		$this->types[$attributeName] = $type;
	}

	/**
	 * A JavaScript-event such as onclick, onkeyup etc. may be set here
	 * 
	 * @param string $attributeName
	 * @param string $eventName
	 * @param string $function
	 */
	function setInputJSEvent($attributeName,$eventName,$function) {
		$this->events[$attributeName][] = "$eventName=\"$function\"";
	}
	
	/**
	 * The id used for the <form>-tag when editing a database entry by calling getEditHTML()
	 * 
	 * @param string $newFormID
	 */
	function setFormID($newFormID) {
		$this->FormID = $newFormID;
	}
	
	/**
	 * The style-attribute of the tr-tag is set here
	 * E.g. setLineStyle("name","background-color:red;");
	 * 
	 * @param string $attributeName
	 * @param string $style
	 */
	function setLineStyle($attributeName,$style) {
		$this->style[$attributeName] = $style;
	}
	
	function setInputStyle($attributeName,$style) {
		$this->inputStyle[$attributeName] = $style;
	}
	#function setSaveButtonOnClick($e) { $this->saveButtonEvent = $e; }
	
	function setStandardSaveButton($class, $collectorClass = "", $additionalFunction = ""){
		$this->setSaveButtonValues(get_parent_class($class), $class->getID(), $collectorClass == "" ? $_SESSION["CurrentAppPlugins"]->isCollectionOf(get_parent_class($class)) : $collectorClass, $additionalFunction);
		$this->className = get_class($class);
		$this->classParentName = get_parent_class($class);
	}
	
	function setSaveButtonValues($parentClass, $ID, $CollectorClass, $additionalFunction = ""){
		$_SESSION["BPS"]->setActualClass("HTMLGUI");
		$bps = $_SESSION["BPS"]->getAllProperties();
		if($bps != -1 AND isset($bps["insertAsNew"])) {
			$ID = -1;
			$_SESSION["BPS"]->unsetACProperty("insertAsNew");
		}
		
		if($this->multiInputMode) {
			$c = $CollectorClass."GUI";
			$c = new $c();
			$singular = $c->getCollectionOf();
			$preFields = PMReflector::getAttributesArray($singular."Attributes");
			$fields = array();
			foreach($preFields as $k => $v)
				if(isset($this->types[$v]) AND $this->types[$v] == "hidden")
					unset($preFields[$k]);

			foreach($preFields as $k => $v)
				$fields[] = $v;
			
		}
		$this->editedID = $ID;
		$this->hiddenInputs .= "
		<input type=\"hidden\" name=\"".$parentClass."ID\" value=\"$ID\" />
		<input type=\"hidden\" name=\"CollectorClass\" value=\"$CollectorClass\" />";
		
		if($this->JSOnSave == null) $this->JSOnSave = "
				function() { 
					contentManager.updateLine('$this->FormID', '$ID');
					".($ID != -1  ? "" 
					: (!$this->multiInputMode ? "
					$('contentLeft').update('');" : "
					new Ajax.Request('./interface/loadFrame.php?p=$singular&id=-1', {onSuccess: function(transport){
						$('contentLeft').update(transport.responseText);
						inputs = document.getElementsByTagName('input');
						$('".$fields[0]."').focus();
					}});"))."
				}";
		
		$this->saveButtonEvent = "
			$additionalFunction saveClass(
				'".$parentClass."',
				'$ID',
				$this->JSOnSave,
				'$this->FormID');";
	}
	
	function setCollectionOf($c,$s = "") {
		$this->singularClass = $c; $this->singularName = ($s != "" ? $s : $c);
	}
	
	function setMultiPageMode($entries, $page, $entriesPerPage, $targetFrame, $targetClass){
		$this->multiPageMode = array($entries, $page, $entriesPerPage, $targetFrame, $targetClass);
	}
	
	function useAutoCompletion($attributeName, $C){
		$this->autoCompletion[$attributeName] = $C;
	}
	
	/**
	 * This is a cool but complex function which lets you define another function to 
	 * evaluate the value of the attribute before displaying it.
	 *
	 * E.g. setParser("AttributeName","HTMLGUI::attribParser",array("param3 of function attribParser","param4 of function attribParser"));
	 *
	 * The first parameter $w of attribParser($w,$l,$p); is the old value of the attribute.
	 *
	 * The second parameter $l can be "load" or "store" for automatic conversion between display- and database-mode (e.g. to calc 23.7.2007 from the timestamp and backwards) (unused most time)
	 *
	 * $p contains a string of the parameters given to setParser(). You need to split it with $s = HTMLGUI::getArrayFromParametersString($p); first.
	 * 
	 * @param string $attributeName
	 * @param string $function
	 * @param string[] $parameters
	 */
	function setParser($attributeName, $function, $parameters = array()) {
		$this->parsers[$attributeName] = $function;
		$this->parserParameters[$attributeName] = $parameters;
	}
	
	function setDisplayGroupParser($function,$parameters = array()){
		$this->dgParser = $function;
		$this->dgParserParameters = $parameters;
	}
	
	/**
	 * You may give an array of attribute names to display instead of all without hideAttribute().
	 * 
	 * @param string[] $attributeNames
	 */
	function setShowAttributes($attributeNames) {
		$this->showAttributes = $attributeNames;
	}
	
	function getShowAttributes() {
		return $this->showAttributes;
	}
	
	/**
	 * The width of a row in browser display mode may be set here.
	 * E.g. setColWidth("name","20px");
	 * 
	 * @param string $attributeName
	 * @param string $width e.g. "20px"
	 */
	function setColWidth($attributeName, $width) {
		$this->addColStyle($attributeName,"width:$width;");
	}
	
	function addColStyle($attributeName,$style){
		if(!isset($this->colStyles[$attributeName])) $this->colStyles[$attributeName] = "";
		$this->colStyles[$attributeName] .= $style;
		
		if($this->colStyles[$attributeName]{strlen($this->colStyles[$attributeName]-1)} != ";")
			$this->colStyles[$attributeName].=";";
	}
	
	function addColClass($attributeName,$className){
		if(!isset($this->colClasses[$attributeName])) $this->colClasses[$attributeName] = "";
		$this->colClasses[$attributeName] .= " ".$className;
	}
	
	/**
	 * A new row is created after the specified row.
	 * You can also use "top" and "bottom" as attributeName.
	 *
	 * As this new row has no value you need to set a parser for it! (Otherwise you get an error in the message log)
	 * 
	 * @param string $attributeName
	 * @param string $newRowName
	 */
	function addRowAfter($attributeName, $newRowName) {
		$this->addedRows[$attributeName] = $newRowName;
		 /*$this->addNotEditable($newRowName);*/
	}
	
	/**
	 * A new column is created left of the specified column.
	 * 
	 * @param string $attributeName
	 * @param string $newColName
	 */
	function addColLeftOf($attributeName, $newColName) {
		$this->addedCols[$attributeName] = $newColName;
	}

	
	function setIsDisplayMode($b) {
		$this->onlyDisplayMode = $b;
	}
	
	function setDeleteInDisplayMode($b) {
		$this->deleteInDisplayMode = $b;
	}
	
	function setEditInDisplayMode($b, $target = "contentRight") {
		$this->editInDisplayMode = $b;
		$this->editInDisplayModeTarget = $target;
	}
	
	function setIsMultiInputMode($b) {
		$this->multiInputMode = $b;
	}
	
	/**
	 * Groups the rows by $attributeName using the associative array $dg to label the group-rows.
	 * E.g. setDisplayGroup("Category",array("1" => "Group1", "2" => "Group2"));
	 * 
	 * @param string $attributeName
	 * @param string[] $dg
	 */
	function setDisplayGroup($attributeName, $dg = array()) {
		$this->displayGroupBy = $attributeName;
		$this->displayGroup = $dg;
	}
	
	function setMultiEditMode($f){
		$this->setIsDisplayMode(true);
		$this->multiEditMode = $f;
	}
	
	function autoCheckSelectionMode($className){
		if($_SESSION["BPS"]->setActualClass($className))
			if($_SESSION["BPS"]->isACPropertySet("selectionMode"))
				$this->setMode($_SESSION["BPS"]->getACProperty("selectionMode"));
	}
	
	function showFilteredCategoriesWarning($bool, $plugin){
		$this->showFilteredCategoriesWarning = array($bool, $plugin);
	}
	
	function setMode($mode){ 
		if($mode != "") {
			$m = explode(",",$mode);
			if($m[0] == "singleSelection"){
				#$_SESSION["messages"]->addMessage("adding column for singleSelection mode (".implode(", ",$m).")");
				$this->shownCols[] = "selectionCol";
				$this->setColWidth("selectionCol","20px");
				$this->selectionFunctions = "saveSelection('$m[1]','$m[2]','$m[3]','%%VALUE%%','".(isset($m[5]) ? $m[5] : "")."','".(isset($m[6]) ? $m[6] : "")."','".(isset($m[7]) ? $m[7] : "")."');".(isset($m[4]) ? " contentManager.loadFrame('contentRight','$m[4]');" : "");
				$this->selectionRow = "<td><img class=\"mouseoverFade selectionButton\" onclick=\"$this->selectionFunctions\" src=\"./images/i2/cart.png\" /></td>";
			}
			if($m[0] == "customSelection"){
				$this->shownCols[] = "selectionCol";
				$this->setColWidth("selectionCol","20px");
				$this->selectionFunctions = "$m[1]('$m[2]', '%%VALUE%%')";
				$this->selectionRow = "<td><img class=\"mouseoverFade selectionButton\" onclick=\"$this->selectionFunctions\" src=\"./images/i2/cart.png\" /></td>";
			}
			if($m[0] == "multiSelection"){
				#$_SESSION["messages"]->addMessage("adding column for multiSelection mode (".implode(", ",$m).")");
				
				$this->shownCols[] = "selectionCol";
				$this->setColWidth("selectionCol","20px");
				$this->selectionFunctions = "saveSelection('$m[1]','$m[2]','$m[3]','%%VALUE%%','$m[5]','$m[6]','$m[7]');";
				$this->selectionRow = "<td><img class=\"mouseoverFade selectionButton\" onclick=\"$this->selectionFunctions\" src=\"./images/i2/cart.png\" /></td>";
				$_SESSION["messages"]->addMessage("adding row to return from multiSelection mode");
				$this->addRowAfter("0","addReturnButton");
				$this->setParser("addReturnButton","HTMLGUI::addReturnButton",array($m[4]));
			}
		}
	}
	/**
	 * This function creates several different input-fields and is not intended to be used externally
	 * 
	 * @param string $as
	 */
	private function getInput($as){
		if(
			$this->onlyDisplayMode AND
			!isset($this->parsers[$as]) AND
			(!isset($this->types[$as]) OR ($this->types[$as] != "select" AND $this->types[$as] != "custom" AND $this->types[$as] != "checkbox")))
			return $this->attributes->$as;
		
		$eve = "";
		$onchange = "";
		if(isset($this->events[$as])) for($j=0;$j<count($this->events[$as]);$j++) {
				$eve .= " ".$this->events[$as][$j];
				if(strpos($this->events[$as][$j], "onchange=\"") !== false) $onchange .= str_replace("\"","",str_replace("onchange=\"","",$this->events[$as][$j]));
		}
	
		if(isset($this->types[$as]) AND $this->types[$as] == "select") {
			if($this->onlyDisplayMode){
				return $this->options[$as][array_search($this->attributes->$as,$this->values[$as])];
			}
			$s = "";
			
			if(isset($this->optgroups[$as])) 
				$s .= "<optgroup label=\"".strip_tags($this->optgroups[$as][$this->values[$as][0]])."\">";
			
			for($i = 0;$i < count($this->values[$as]);$i++){
				if(isset($this->optgroups[$as]) AND $i > 0 AND $this->optgroups[$as][$this->values[$as][$i]] != $this->optgroups[$as][$this->values[$as][$i - 1]])
					$s .= "</optgroup><optgroup label=\"".strip_tags($this->optgroups[$as][$this->values[$as][$i]])."\">";
				
				$s .= "<option value=\"".$this->values[$as][$i]."\" ".($this->values[$as][$i] == $this->attributes->$as ? " selected=\"selected\"" : "").">".$this->options[$as][$i]."</option>";
			}
			
			if(isset($this->optgroups[$as])) $s .= "</optgroup>";
			
			return (isset($this->buttonsNextToFields[$as]) ? $this->buttonsNextToFields[$as] : "")."<select onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\"".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." name=\"".$as."\" id=\"".$as."\">$s</select>";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "checkbox") {
			if($this->onlyDisplayMode) return Util::catchParser($this->attributes->$as);
			$s = "";
			
			$s .= "<input ".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." type=\"checkbox\" value=\"1\" ".($this->attributes->$as == 1 ? " checked=\"checked\"" : "")." name=\"".$as."\" id=\"".$as."\"> ";
			return "$s";
		}

		if(isset($this->types[$as]) AND $this->types[$as] == "radio") {
			$s = "";
			for($i = 0;$i < count($this->values[$as]);$i++)
				$s .= "<input ".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." type=\"radio\" value=\"".$this->values[$as][$i]."\" ".($this->values[$as][$i] == $this->attributes->$as ? " checked=\"checked\"" : "")." name=\"".$as."\"> ".$this->options[$as][$i]." ";
			return "$s";
		}

		if(isset($this->types[$as]) AND $this->types[$as] == "custom"){
			$this->values[$as]->isDisplayMode($this->onlyDisplayMode);
			return $this->values[$as];
		}
	
		if(isset($this->types[$as]) AND $this->types[$as] == "password") {
			return "<input onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\" ".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." type=\"password\" name=\"".$as."\" id=\"".$as."\" value=\"".$this->attributes->$as."\" />";
		}
	
		if(isset($this->types[$as]) AND $this->types[$as] == "calendar") {
			$B = new Button("Kalender anzeigen", "calendar", "iconic");
			$B->onclick("\$j('#$as').trigger('focus');");
			$B->style("float:right;");
			
			return "$B
			<input 
				onfocus=\"focusMe(this);\" 
				onblur=\"blurMe(this);\" 
				".(isset($this->events[$as]) ? $eve : "")." 
				".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : " style=\"width:90%\"")."
				type=\"text\"
				name=\"".$as."\" id=\"".$as."\" value=\"".$this->attributes->$as."\" />
			<script type=\"text/javascript\">\$j('#$as').datepicker();</script>";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "hidden") {
			return "<input ".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." type=\"hidden\" name=\"".$as."\" id=\"".$as."\" value=\"".$this->attributes->$as."\" />";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "TextEditor") {
			return "<input ".(isset($this->events[$as]) ? $eve : "")." style=\"background-image:url(./images/navi/editor.png);".(isset($this->inputStyle[$as]) ? "".$this->inputStyle[$as]."" : "")."\" type=\"button\" class=\"bigButton backgroundColor2\" onclick=\"TextEditor.show('$as','$this->FormID');\" value=\"".$this->texts["in Editor bearbeiten"]."\" /><textarea style=\"display:none;\" name=\"".$as."\" id=\"".$as."\">".$this->attributes->$as."</textarea>";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "HTMLEditor") {
			$_SESSION["BPS"]->registerClass("WysiwygGUI");
			$_SESSION["BPS"]->setACProperty("FieldClass", get_class($this->object));
			$_SESSION["BPS"]->setACProperty("FieldClassID",$this->editedID);
			$_SESSION["BPS"]->setACProperty("FieldName",$as);
			
			return "<input ".(isset($this->events[$as]) ? $eve : "")." style=\"background-image:url(./images/navi/editor.png);".(isset($this->inputStyle[$as]) ? "".$this->inputStyle[$as]."" : "")."\" type=\"button\" class=\"bigButton backgroundColor2\" onclick=\"windowWithRme('Wysiwyg','','getEditor','');\" value=\"in HTML-Editor\nbearbeiten\" />";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "TextEditor64") {
			return "<input ".(isset($this->events[$as]) ? $eve : "")." style=\"background-image:url(./images/navi/editor.png);".(isset($this->inputStyle[$as]) ? "".$this->inputStyle[$as]."" : "")."\" type=\"button\" class=\"bigButton backgroundColor2\" onclick=\"TextEditor.show64('$as','$this->FormID');\" value=\"".$this->texts["in Editor bearbeiten"]."\" /><textarea style=\"display:none;\" name=\"".$as."\" id=\"".$as."\">".$this->attributes->$as."</textarea>";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "readonly") {
			return (isset($this->buttonsNextToFields[$as]) ? $this->buttonsNextToFields[$as] : "")."<input ".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." type=\"text\" name=\"".$as."\" id=\"".$as."\" value=\"".$this->attributes->$as."\" readonly=\"readonly\" />";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "textarea") {
			return (isset($this->buttonsNextToFields[$as]) ? $this->buttonsNextToFields[$as] : "")."<textarea onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\" ".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." name=\"".$as."\" id=\"".$as."\" >".$this->attributes->$as."</textarea>";
		}
		
		if(isset($this->types[$as]) AND $this->types[$as] == "image") {
			#$_SESSION["BPS"]->registerClass("DBImage");
			#$_SESSION["BPS"]->setACProperty("plugin",str_replace("Attributes","",get_class($this->attributes)));
			#$_SESSION["BPS"]->setACProperty("id",$this->editedID);
			#$_SESSION["BPS"]->setACProperty("attribute",$as);

			if($this->editedID == -1) return $this->texts["zuerst speichern"];
			return "
				<img 
					src=\"./images/i2/settings.png\" 
					style=\"float:right;\"
					class=\"mouseoverFade\"
					onclick=\"phynxContextMenu.start(this, 'HTML','upload:$this->editedID:".$this->classParentName.":".$as."','".$this->texts["Bild hochladen"].":','right');\" 
				/>
				<img
					src=\"./images/i2/delete.gif\"
					style=\"float:right;margin-right:3px;\"
					title=\"".$this->texts["Bild löschen"]."\"
					class=\"mouseoverFade\"
					onclick=\"if(confirm('".$this->texts["Bild wirklich löschen?"]."')) new Ajax.Request('./interface/set.php?class=".str_replace("Attributes","",get_class($this->attributes))."&id=$this->editedID&emptyAttribute=$as',{
						onSuccess: function(transport) { \$('uploadImage').style.display='none'; }
					});\"
				/>
				<img 
					id=\"uploadImage\" ".($this->attributes->$as != "" ? "":"style=\"display:none;\"")." 
					src=\"./interface/loadFrame.php?p=DBImage&id=".$this->classParentName.":::".$this->editedID.":::".$as."&r=".rand()."\" 
				/>";
		}

		if(isset($this->attributes->$as) AND !isset($this->parsers[$as])) 
			return (isset($this->buttonsNextToFields[$as]) ? $this->buttonsNextToFields[$as] : "")."<input onfocus=\"focusMe(this); ".(isset($this->autoCompletion[$as]) ? " ACInputHasFocus=true; AC.start(this);" : "")."\" onblur=\"blurMe(this);".(isset($this->autoCompletion[$as]) ? " ACInputHasFocus = false; AC.end(this);" : "")."\" ".(isset($this->autoCompletion[$as]) ? "autocomplete=\"off\" onkeyup=\"AC.update(event.keyCode, this, '".$this->autoCompletion[$as]."');\"" : "")."".(isset($this->events[$as]) ? $eve : "")." ".(isset($this->inputStyle[$as]) ? "style=\"".$this->inputStyle[$as]."\"" : "")." type=\"text\" name=\"".$as."\" id=\"".$as."\" value=\"".htmlspecialchars($this->attributes->$as)."\" /> ";
		
		if(isset($this->parsers[$as])) {
			$r = "";
			$m = explode("::", $this->parsers[$as]);
			$r = Util::invokeStaticMethod($m[0], $m[1], array((isset($this->attributes->$as) ? $this->attributes->$as : ""), "", implode("%§%",$this->parserParameters[$as])));
			#return("\$r = ".$this->parsers[$as]."(\"".(isset($this->attributes->$as) ? $this->attributes->$as : "")."\",\"\",\"".implode("%§%",$this->parserParameters[$as])."\");");
			return $r;
		}
		$_SESSION["messages"]->addMessage("No value and no parser for \"$as\" given. Is this an added row? You need to set a parser then.");
	}
	
	function getOperationsHTML($pluginName, $id = -1){
		$userCanDelete = mUserdata::isDisallowedTo("cantDelete".$pluginName);
		$userCanCreate = mUserdata::isDisallowedTo("cantCreate".$pluginName);
		if($this->texts == null) {
			$c = $this->loadLanguageClass("HTML");
			$this->texts = $c->getEditTexts();
		}

		$html = "";
		if(PMReflector::implementsInterface($pluginName,"iNewWithValues") AND $userCanCreate) $os = "1";
		else $os = "0";

		if(PMReflector::implementsInterface($pluginName,"iCloneable") AND $userCanCreate) $os .= "1";
		else $os .= "0";
		
		if((PMReflector::implementsInterface($pluginName,"iDeletable") OR PMReflector::implementsInterface($pluginName,"iDeletable2")) AND $userCanDelete) $os .= "1";
		else $os .= "0";
		
		if(PMReflector::implementsInterface($pluginName,"iRepeatable") AND Session::isPluginLoaded("mRepeatable")) $os .= "1";
		else $os .= "0";
		
		if(PMReflector::implementsInterface($pluginName,"iXMLExport")) $os .= "1";
		else $os .= "0";

		if($id != -1 AND $os != "00000"){
			$B = new Button("Operationen", "wrench", "iconic");
			$B->id($pluginName."Operations");
			$B->onclick("phynxContextMenu.start(this, 'HTML','operations:$pluginName:$id:$os','".$this->texts["Operationen"].":');");
			$B->style("float:right;margin-top:-3px;");
			
			return $B;#"<span title=\"Operationen\" id=\"".$pluginName."Operations\" class=\"iconic wrench\" onclick=\"\" style=\"\" ></span>";
		}
		return "";
	}
	
	/**
	 * Call getEditHTML() if you want a form to edit the values of each single attribute
	 */
	function getEditHTML(){

		$AName = get_class($this->attributes);
		if($AName == "stdClass" AND $this->className == null)
			die("Can't determine the name of the plugin!<br />If you extended the class PersistentObject please use HTMLGUI::setStandardSaveButton(\$this);");
		
		if($AName == "stdClass" OR $AName == "Attributes") $AName = str_replace("GUI","",$this->className);
		
		$pluginName = str_replace("Attributes","",$AName);
		$userCanEdit = mUserdata::isDisallowedTo("cantEdit".$pluginName);
		$userCanCreate = mUserdata::isDisallowedTo("cantCreate".$pluginName);
		
		$html = "";

		if(count($this->prependedElements) > 0)
			foreach($this->prependedElements as $k => $v)
				$html .= $v->getHTML();

		$userLabels = mUserdata::getRelabels($pluginName);
		
		$userHiddenFields = mUserdata::getHides($pluginName);
		
		$this->texts = $this->languageClass->getEditTexts();

		if(!$userCanEdit AND (($userCanCreate AND $this->editedID != -1) OR !$userCanCreate)){
			$html .= "
			<table>
				<tr>
					<td><img style=\"float:left;margin-right:10px;\" src=\"./images/navi/restrictions.png\" />".$this->texts["kein Speichern"]."</tr>
			</table>";
		}

		try {
			$as = PMReflector::getAttributesArray($this->attributes);
		
			foreach($this->addedRows as $key => $value){
				if($key == "top") array_unshift($as, $value);
				if($key == "bottom") array_push($as, $value);
			}

		} catch(ReflectionException $e) {
			$_SESSION["messages"]->addMessage(get_class($e)." thrown!");
			$_SESSION["messages"]->addMessage("You need to set the attributes with setAttributes() of class HTMLGUI!");
			return "An Error was caught. Please check the system log for additional information.";
		}

		$DesktopLinkButton = $this->getDesktopLinkButton();
			

		$html .= "
			<form id=\"$this->FormID\">
				<div class=\"backgroundColor1 Tab\">
					<p>".$this->getOperationsHTML($pluginName, $this->editedID).$DesktopLinkButton."".($this->labelCaption == null ? $this->name." editieren:" : $this->labelCaption)."</p>
				</div>
				<div>
				<table>
					<colgroup>
					   <col class=\"backgroundColor2\" style=\"width:120px;\" />
					   <col class=\"backgroundColor3\" />
					</colgroup>";

		$tab = 0;
		
		$sortOrder = $as;
		if(count($this->showAttributes) > 0)
			$sortOrder = $this->showAttributes;

		foreach($sortOrder as $key => $value){
			if(isset($this->dontShow[$value])) continue;
			if(isset($userHiddenFields[$value])) continue;
			
			if(isset($this->types[$value]) AND $this->types[$value] == "hidden") {
				$this->hiddenInputs .= $this->getInput($value);
				continue;
			}
			
			if(isset($this->insertSpaceBefore[$value])){
				$html .= "";
				if($this->insertSpaceBefore[$value] == "") $html .= "
					<tr>
						<td class=\"backgroundColor0\" colspan=\"2\"></td>
					</tr>";
				if($this->insertSpaceBefore[$value] != "" AND !$this->tabs[$value]) $html .= "
					<tr class=\"FormSeparatorWithLabel\">
						<td colspan=\"2\">".$this->insertSpaceBefore[$value]."</td>
					</tr>";
				elseif($this->insertSpaceBefore[$value] != "" AND $this->tabs[$value]) {
					$html .= "
				</table>
				</div>
				
				<div onclick=\"if($('Tab$this->className$tab').style.display == 'none') new Effect.BlindDown('Tab$this->className$tab', {queue: 'end'}); else new Effect.BlindUp('Tab$this->className$tab', {queue: 'end'});\" class=\"backgroundColor1 Tab borderColor1\">
					<p>".$this->insertSpaceBefore[$value]."</p>
				</div>
				<div id=\"Tab$this->className$tab\" style=\"display:none;\">
				<table>
					<colgroup>
						<col class=\"backgroundColor2\" style=\"width:120px;\" />
						<col class=\"backgroundColor3\" />
					</colgroup>";
					$tab++;	
				}
			}
			
			$label = ucfirst($value);
			if(isset($this->labels[$value])) $label = $this->labels[$value];
			if(isset($userLabels[$value])) $label = $userLabels[$value];
			
			$html .= "
					<tr ".(isset($this->style[$value]) ? "style=\"".$this->style[$value]."\"" : "").">
						<td id=\"".$value."EditL\"><label for=\"".$value."\">".$label.":".(isset($this->labelDescriptions[$value]) ? "<br /><small>".$this->labelDescriptions[$value]."</small>" : "")."</label></td>
						<td id=\"".$value."EditR\">".$this->getInput($value)."".(isset($this->fieldDescriptions[$value]) ? "<br /><small style=\"color:grey;\">".$this->fieldDescriptions[$value]."</small>" : "")."</td>
					</tr>";
			
		}

		if($tab > 0) $html .= "
				</table>
				</div>
				<table style=\"\">
					<colgroup>
						<col class=\"backgroundColor2\" style=\"width:120px;\" />
						<col class=\"backgroundColor3\" />
					</colgroup>";
		
		if(!$this->replaceSaveButton){
			if(!(!$userCanEdit AND (($userCanCreate AND $this->editedID != -1) OR !$userCanCreate)) AND !$this->onlyDisplayMode) $html .= "
					<tr>
						<td colspan=\"2\">
							<input 
								type=\"button\" 
								name=\"currentSaveButton\"
								value=\"".($this->labelSaveButton == null ? $this->name." speichern" : $this->labelSaveButton)."\" 
								onclick=\"".$this->saveButtonEvent."\" 
								style=\"background-image:url(./images/i2/save.gif);\"
							/>".$this->hiddenInputs."
						</td>
					</tr>";
		} else {
			$html .= "
					<tr>
						<td colspan=\"2\">
							".$this->replaceSaveButton.$this->hiddenInputs."
						</td>
					</tr>";
		}
		/*
		if(PMReflector::implementsInterface($pluginName,"iScrollable") AND $this->editedID != -1) {
			$html .= "
					<tr>
						<td class=\"backgroundColor0\" style=\"height:20px;\"></td>
					</tr>
					<tr>
						<td colspan=\"2\" class=\"backgroundColor3\">
							<table>
								<tr>
									<td 
										title=\"[Shift] + [Alt] + y\"
										style=\"cursor:pointer;width:7%;\"
										onclick=\"rme('$pluginName', '$this->editedID', 'getNextID','true','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\"
										onmouseover=\"this.className='backgroundColor1';\"
										onmouseout=\"this.className='';\">
										<a href=\"javascript:rme('$pluginName', '$this->editedID', 'getNextID','true','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\" accesskey=\"y\"></a><img src=\"./images/left.gif\">
									</td>
									<td 
										title=\"[Shift] + [Alt] + x\"
										style=\"cursor:pointer;width:23%;\"
										onclick=\"rme('$pluginName', '$this->editedID', 'getPreviousID','false','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\"
										onmouseover=\"this.className='backgroundColor1';\"
										onmouseout=\"this.className='';\">
										<a href=\"javascript:rme('$pluginName', '$this->editedID', 'getPreviousID','false','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\" accesskey=\"x\"></a><img src=\"./images/lefts.gif\">
									</td>
									
									<td style=\"width:40%;\">".$this->texts["gehe zu Datensatz ID"].": <input onblur=\"blurMe(this);\" onfocus=\"focusMe(this);\" style=\"width:40px;text-align:right;\" onkeydown=\"if(event.keyCode == 13) rme('$pluginName', '$this->editedID', 'checkInputID',this.value,'if(checkResponse(transport)) loadLeftFrameV2(\'$pluginName\', transport.responseText);');\" type=\"text\" value=\"$this->editedID\" /></td>
									
									<td 
										title=\"[Shift] + [Alt] + c\"
										style=\"cursor:pointer;width:23%;\" 
										onclick=\"rme('$pluginName', '$this->editedID', 'getNextID','false','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\"
										onmouseover=\"this.className='backgroundColor1';\"
										onmouseout=\"this.className='';\">
										<a href=\"javascript:rme('$pluginName', '$this->editedID', 'getNextID','false','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\" accesskey=\"c\" ></a><img style=\"float:right;\" src=\"./images/rights.gif\">
									</td>
									<td 
										title=\"[Shift] + [Alt] + v\"
										style=\"cursor:pointer;width:7%;\" 
										onclick=\"rme('$pluginName', '$this->editedID', 'getPreviousID','true','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\"
										onmouseover=\"this.className='backgroundColor1';\"
										onmouseout=\"this.className='';\">
										<a href=\"javascript:rme('$pluginName', '$this->editedID', 'getPreviousID','true','if(checkResponse(transport)) contentManager.loadFrame(\'contentLeft\',\'$pluginName\', transport.responseText);');\" accesskey=\"v\"></a><img style=\"float:right;\" src=\"./images/right.gif\">
									</td>
								</tr>
							</table>
						</td>
					</tr>";
		}*/
		
		$html .= "
					</table>
				</div>
			</form>";
		
		if(count($this->appendedElements) > 0)
			foreach($this->appendedElements as $k => $v)
				$html .= $v->getHTML();
		
		
		return $html.GUIFactory::editFormOnchangeTest($this->FormID);
	}
	
	/**
	 * Call getBrowserHTML() if you want a table containing all the elements of a collection-class
	 * When called with an id, only one row is returned to easily replace an old one with JavaScript
	 * 
	 * @param int $lineWithId 
	 */
	function getBrowserHTML($lineWithId = -1){
		$string = "";
		$top = "";
		
		$this->texts = $this->languageClass->getBrowserTexts();
		$singularLanguageClass = $this->loadLanguageClass($this->singularClass);
		
		if(isset($_SESSION["phynx_errors"]) AND (!isset($_SESSION["HideErrors"]) OR $_SESSION["HideErrors"] == false) AND $lineWithId == -1 AND ($_SERVER["HTTP_HOST"] == "dev.furtmeier.lan" OR strpos(__FILE__, "nemiah") !== false)) $top .= "
		<table>
			<colgroup>
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td>
					<img style=\"float:left;margin-right:10px;\" src=\"./images/navi/warning.png\" />
					<b>Es ".(count($_SESSION["phynx_errors"]) != 1 ? "liegen" : "liegt")." ".count($_SESSION["phynx_errors"])." PHP-Fehler vor:</b><br />
					<a href=\"javascript:windowWithRme('Util','','showPHPErrors','');\">Fehler anzeigen</a>,<br />
					<a href=\"javascript:rme('Util','','deletePHPErrors','','contentManager.reloadFrameRight();');\">Fehler löschen</a></td>
			</tr>
		</table>";
		$userCanDelete = mUserdata::isDisallowedTo("cantDelete".$this->singularClass);
		$userCanCreate = mUserdata::isDisallowedTo("cantCreate".$this->singularClass);
		
		$userHiddenFields = mUserdata::getHides($this->singularClass);
		
		if($this->singularClass == "none") {
			echo "collectionOf is not set. See message log for further details.";
			throw new CollectionOfNotSetException();
		}
		
		if($this->name == "Noname")
			$_SESSION["messages"]->addMessage("There is no name set. You might use setName of HTMLGUI to do that.");
		
		
		#$firstKey = null;
		$colspan = 0;
		$oldValueForDisplayGroup = "";
		#if($this->attributes != null)
			#foreach($this->attributes AS $ei => $vi) {
		for($i=0;$i < count($this->attributes);$i++){
			#if($firstKey == null) $firstKey = $ei;
			#$i = $ei;
			$aid = $this->attributes[$i]->getID(); // get the id of an object separately
			$sc = $this->attributes[$i]->getA(); // get the attributes-object from the object
			$as = PMReflector::getAttributesArray($sc); // get an array of attribute-names from the object

			if(count($this->addedCols) != 0) { // adding specified new columns which are not in $as
				foreach($as as $key => $value) {
					if(isset($this->addedCols[$value])) {
						#$_SESSION["messages"]->addMessage("adding col ".$this->addedCols[$value]." right of $value");
						array_splice($as, $key+1, 0, $this->addedCols[$value]);
						#$_SESSION["messages"]->addMessage("new attributes list: ".implode(", ",$as));
					}
				}
			}
			
			if($this->displayGroupBy != null){
				$f = $this->displayGroupBy;
				#if($i == 0) $_SESSION["messages"]->addMessage("displayGroupBy activated. Using value of ".$f.": ".$sc->$f);
				if($oldValueForDisplayGroup != $sc->$f) if($lineWithId == -1) {
					$dgf = $sc->$f."";
					$kTv = (isset($this->displayGroup[$dgf]) ? $this->displayGroup[$dgf] : " ");
					#if($this->dgParser != "") eval("\$kTv = ".$this->dgParser."(\"".$sc->$f."\",\"load\",\"".implode("%§%",$this->dgParserParameters)."\");");
					if($this->dgParser != "") $kTv = $this->invokeParser($this->dgParser, $sc->$f, implode("%§%",$this->dgParserParameters));
					$string .= "
				<tr class=\"kategorieTeiler\">
					<td colspan=\"%%COLSPAN%%\">".$kTv."</td>
				</tr>";
				}
			}
		
			if($lineWithId == -1) $string .= "
				<tr id=\"".$this->RowIDPrefix."".($this->onlyDisplayMode ? "D" : "")."$aid\">";
			
			if($this->editInDisplayMode and $this->editInDisplayModeTarget == "contentLeft") $string .= "
					".$this->editIcon($aid);
			
			if($this->selectionRow == "" AND !$this->onlyDisplayMode) $string .= "
					<td><img onclick=\"contentManager.selectRow(this); contentManager.loadFrame('contentLeft','$this->singularClass','$aid'".($this->JSOnEdit != null ? ",".$this->JSOnEdit : "").");\" src=\"./images/i2/edit.png\" class=\"mouseoverFade editButton\" /></td>";
			
			if($this->selectionRow != "") $string .=
					"".str_replace("%%VALUE%%","$aid",$this->selectionRow);

			foreach($this->parserParameters as $schluessel => $werte)
				if(isset($sc->$schluessel)) $sc->$schluessel = addslashes($sc->$schluessel);
			
			if(count($this->showAttributes) == 0)
				for($j=0;$j<count($as);$j++) {
					if(isset($this->dontShow[$as[$j]])) continue;
					if(isset($userHiddenFields[$as[$j]])) continue;

					if($i == 0) $this->shownCols[] = $as[$j];
					
					if(isset($this->parsers[$as[$j]])) {
						$parameters = $this->makeParameterStringFromArray($this->parserParameters[$as[$j]], $sc, $aid);
						$t = $this->invokeParser($this->parsers[$as[$j]], $sc->$as[$j], $parameters);
					}
					else $t = htmlspecialchars($sc->$as[$j]);

					if($this->multiEditMode != null AND in_array($as[$j], $this->multiEditMode)) $string .= "
					<td><input onfocus=\"oldValue = this.value;\" onblur=\"if(oldValue != this.value) saveMultiEditInput('".$this->singularClass."','".$aid."','".$as[$j]."');\" onkeydown=\"if(event.keyCode == 13) saveMultiEditInput('".$this->singularClass."','$aid','".$as[$j]."');\" type=\"text\" id=\"".$as[$j]."ID$aid\" value=\"".htmlspecialchars($t)."\" class=\"multiEditInput2\" /></td>";
					else $string .= "
					<td ".(isset($this->colStyles[$as[$j]]) ? "style=\"".$this->colStyles[$as[$j]]."\"" : "")." id=\"Browser".$as[$j]."$aid\">".$t."</td>";
				}
			else
				foreach($this->showAttributes as $key => $value) {
					if(isset($userHiddenFields[$value])) continue;
					if($i == 0) $this->shownCols[] = $value;

					if(isset($this->parsers[$value])) {
						$parameters = $this->makeParameterStringFromArray($this->parserParameters[$value], $sc, $aid);
						$t = $this->invokeParser($this->parsers[$value], $sc->$value, $parameters);
					}
					else $t = htmlspecialchars($sc->$value);
					
					$string .= "
					<td id=\"Browser".$value."$aid\" ".(isset($this->colStyles[$value]) ? "style=\"".$this->colStyles[$value]."\"" : "").">".$t."</td>";			
				}

			if((!$this->onlyDisplayMode OR $this->deleteInDisplayMode) AND $userCanDelete)  $string .= "
				<td><span class=\"mouseoverFade iconic trash_stroke\" onclick=\"deleteClass('".$this->singularClass."','$aid', ".($this->JSOnDelete == null ? "function() { /*$('BrowserMain".($this->onlyDisplayMode ? "D" : "")."$aid').style.display='none';*/ contentManager.reloadFrameRight(); if(typeof lastLoadedLeft != 'undefined' && lastLoadedLeft == '$aid') $('contentLeft').update(''); }" : $this->JSOnDelete).",'".str_replace("%1",$this->singularName, $this->texts["%1 wirklich löschen?"])."');\"></span></td>";
			elseif(!$userCanDelete) $string .= "<td><img src=\"./images/i2/empty.png\" /></td>";
			
			if($this->editInDisplayMode AND $this->editInDisplayModeTarget != "contentLeft") $string .= "
					".$this->editIcon($aid);

			
			if($lineWithId == -1) $string .= "	
				</tr>";
				
			if($this->displayGroupBy != null) $oldValueForDisplayGroup = $sc->$f;
		}
		if($this->attributes == null) $colspan = 1;
		else $colspan = count($this->shownCols);
		if($this->editInDisplayMode) $colspan++;
		
		$cols = "";
		$c = 0;
		if($this->selectionRow != "") $c++; 
		
		
		foreach($this->shownCols as $key => $value)
			$cols .= "<col class=\"backgroundColor".((++$c) % 2 + 2)." ".(isset($this->colClasses[$value]) ? $this->colClasses[$value] : "")."\" ".(isset($this->colStyles[$value]) ? "style=\"".$this->colStyles[$value]."\"" : "")." />\n";
		
		if(count($this->attributes) == 0) $cols .= "<col class=\"backgroundColor3\" />\n";
		
		if($this->onlyDisplayMode){
			if($this->editInDisplayMode)
				if($this->editInDisplayModeTarget == "contentLeft") $cols = "<col class=\"backgroundColor2\" style=\"width:20px;\" />\n".$cols;
				else $cols .= "<col style=\"width:20px;\" />\n";
				
			if($this->deleteInDisplayMode) $cols .= "<col style=\"width:20px\" />\n";
		} else {
			if(!$this->selectionRow) $cols = "<col class=\"backgroundColor2\" style=\"width:20px;\" />\n".$cols;
			$cols .= "<col style=\"width:20px;\" class=\"backgroundColor".((++$c) % 2 + 2)."\" />\n";
		}
			
		$determinedNumberofCols = substr_count($cols, "\n");
		
		if($this->displayGroupBy != null) $string = str_replace("%%COLSPAN%%",($this->selectionRow == "" ? $colspan+2 : $colspan+1),$string);
		
		$multiPageRow = "";
		$filtered = "";
		if(count($this->multiPageMode) != 0){
			$userDefinedEntriesPerPage = false;
			
			if($this->multiPageMode[2] == 0){
				$userDefinedEntriesPerPage = true;
				$mU = new mUserdata();
				$this->multiPageMode[2] = $mU->getUDValue("entriesPerPage{$this->multiPageMode[4]}");
				if($this->multiPageMode[2] == null) $this->multiPageMode[2] = 20;
			}
			
			if($this->multiPageMode[1] == "undefined") $this->multiPageMode[1] = 0;
			
			$pages = ceil($this->multiPageMode[0] / $this->multiPageMode[2]);
			
			if($this->multiPageMode[1] != 0) $pageLinks = "<a href=\"javascript:contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."', -1, 0, '');\"><span class=\"iconic arrow_left\" style=\"border-left-width:2px;\"></span></a> ";
			else $pageLinks = "<span class=\"iconic arrow_left inactive\" style=\"border-left-width:2px;\"></span> ";
			
			if($this->multiPageMode[1] != 0) $pageLinks .= "<a href=\"javascript:contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."',-1,'".($this->multiPageMode[1]-1)."');\"><span class=\"iconic arrow_left\" style=\"margin-right:7px;\"></span></a> ";
			else $pageLinks .= "<span class=\"iconic arrow_left inactive\" style=\"margin-right:7px;\"></span> ";
			
			if($this->multiPageMode[1] != $pages - 1) $pageLinks .= "<a href=\"javascript:contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."',-1,'".($this->multiPageMode[1]+1)."');\"><span class=\"iconic arrow_right\" style=\"margin-left:7px;\"></span></a> ";
			else $pageLinks .= "<span class=\"iconic arrow_right inactive\" style=\"margin-left:7px;\"></span> ";
			
			if($this->multiPageMode[1] != $pages - 1) $pageLinks .= "<a href=\"javascript:contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."',-1,'".($pages-1)."');\"><span class=\"iconic arrow_right\" style=\"border-right-width:2px;\"></span></a> | ";
			else $pageLinks .= "<span class=\"iconic arrow_right inactive\" style=\"border-right-width:2px;\"></span> | ";
			
			$start = $this->multiPageMode[1] - 3;
			if($start < 0) $start = 0;
			
			$end = $this->multiPageMode[1] + 3;
			if($end > $pages - 1) $end = $pages - 1;
			
			for($i=$start; $i<=$end; $i++)
				if($this->multiPageMode[1] != "$i") $pageLinks .= "<a href=\"javascript:contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."',-1,'".$i."');\">".($i+1)."</a> ";
				else $pageLinks .= ($i+1)." ";
			
				if($lineWithId == -1) $multiPageRow = "
					<tr class=\"backgroundColorHeader\">
						".($userDefinedEntriesPerPage ? "<td><span class=\"iconic wrench settingsButtonBrowser\" onclick=\"phynxContextMenu.start(this, 'HTML','multiPageSettings:{$this->multiPageMode[4]}','".$this->texts["Einstellungen"].":');\"></span></td>" : "")."
						<td colspan=\"".($colspan+1+($userDefinedEntriesPerPage ? 0 : 1))."\"><!--<input type=\"text\"onkeydown=\"if(event.keyCode == 13) contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."',-1,this.value - 1);\" style=\"width:30px;float:right;text-align:right;\" value=\"".($this->multiPageMode[1]+1)."\" onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\" />-->".$this->multiPageMode[0]." ".($this->multiPageMode[0] == 1 ? $this->texts["Eintrag"] : $this->texts["Einträge"])."<!--, ".$pages." ".($pages != 1 ? $this->texts["Seiten"] : $this->texts["Seite"])."-->, ".($pages == 0 ? 1 : $pages)." ".(($pages == 0 ? 1 : $pages) != 1 ? $this->texts["Seiten"] : $this->texts["Seite"]).": $pageLinks</td>
					</tr>";
				
					
					
				if($lineWithId == -1 AND $this->showFilteredCategoriesWarning AND isset($this->showFilteredCategoriesWarning[0]) AND $this->showFilteredCategoriesWarning[0] == true) {
					#<img src=\"./images/i2/delete.gif\" style=\"float:right;\" class=\"mouseoverFade\" onclick=\"rme('mUserdata','','delUserdata',Array('filteredCategoriesInHTMLGUI{$this->showFilteredCategoriesWarning[1]}'),'contentManager.reloadFrameRight();');\" alt=\"".$this->texts["Filter löschen"]."\" title=\"".$this->texts["Filter löschen"]."\" />
					$dB = new Button($this->texts["Filter löschen"],"./images/i2/delete.gif");
					$dB->style("float:right;");
					$dB->type("icon");
					$dB->rme("HTML","","saveContextMenu",array("'deleteFilters'","'{$this->showFilteredCategoriesWarning[1]}'"), "if(checkResponse(transport)) contentManager.reloadFrameRight();");
					$filtered = "
					<tr>
						<td class=\"backgroundColor0\">".((isset($this->showFilteredCategoriesWarning[0]) AND $this->showFilteredCategoriesWarning[0] == true) ? "<img src=\"./images/i2/note.png\" /></td><td class=\"backgroundColor0\" colspan=\"".($determinedNumberofCols - 2)."\" style=\"color:grey;\" >".$this->texts["Anzeige wurde gefiltert"]."</td><td class=\"backgroundColor0\">$dB</td>" : " ")."</td>
					</tr>";
				}
		}
		
		$separator = "";
		if($lineWithId == -1)
			$separator = "
			<tr class=\"browserSeparatorTop\">
				<td class=\"backgroundColor0\" colspan=\"".($determinedNumberofCols)."\"></td>
			</tr>";
		#" : "")."\" ".(isset($this->autoCompletion[$as]) ? "onkeyup=\"updateAutoComplete(event.keyCode, this, '".$this->autoCompletion[$as]."');\"" : "")."
		$quickSearchRow = "";
		if($this->quickSearchPlugin != ""){
			
			$B = "";
			$K = "";
			$showSF = PMReflector::implementsInterface($this->quickSearchPlugin."GUI","iSearchFilter");
			if($showSF){
				$B = new Button("Suche als Filter anwenden","./images/i2/searchFilter.png", "icon");
				$B->style("float:right;");
				$B->rme("HTML","","saveContextMenu", array("'searchFilter'","'$this->quickSearchPlugin;:;'+$('quickSearch$this->quickSearchPlugin').value"),"if(checkResponse(transport)) contentManager.reloadFrameRight();");
				
				$mU = new mUserdata();
				$K = $mU->getUDValue("searchFilterInHTMLGUI".$this->quickSearchPlugin);
			}
			
			$quickSearchRow = "
					<tr class=\"backgroundColorHeader\">
						<td><span onclick=\"phynxContextMenu.start(this, '$this->quickSearchPlugin','searchHelp','".$this->texts["Suche"].":','left');\" class=\"iconic info\" style=\"cursor:help;\"></span></td>
						<td colspan=\"".($colspan+1)."\">
							$B
							<input
								autocomplete=\"off\"
								onfocus=\"focusMe(this); ACInputHasFocus=true; AC.start(this); if(this.value != '') AC.update('10', this, '$this->quickSearchPlugin', 'quickSearchLoadFrame');\"
								onblur=\"blurMe(this); ACInputHasFocus=false; AC.end(this);\"
								id=\"quickSearch$this->quickSearchPlugin\"
								onkeyup=\"AC.update(event.keyCode, this, '$this->quickSearchPlugin','quickSearchLoadFrame');\"
								type=\"text\"
								placeholder=\"Suche\"
								value=\"$K\"
								".($showSF ? "style=\"width:90%;\"" : "")."
							/>
						</td>
					</tr>";
		}
		#".(!$this->onlyDisplayMode ? ($singularLanguageClass == null ? /*Bitte ".$this->name." auswählen:*/"&nbsp;" : $singularLanguageClass->getBrowserCaption().":") : ($singularLanguageClass == null ? $this->name : $singularLanguageClass->getPlural() ).":")."
		if($lineWithId == -1) $top .= "$this->tip
				<div class=\"backgroundColor1 Tab\">
					<p>&nbsp;</p>
				</div>
				<table class=\"contentBrowser\">
					<colgroup>
						$cols
					</colgroup>
					$quickSearchRow
					$multiPageRow$separator$filtered
					".((!$this->onlyDisplayMode AND $this->selectionRow == "" AND $userCanCreate) ? "
					<tr id=\"addNewRow\">
						<td><img class=\"mouseoverFade\" onclick=\"contentManager.newClassButton('$this->singularClass',".($this->JSOnNew != null ? $this->JSOnNew : "''").");\" src=\"./images/i2/new.gif\" id=\"buttonNewEntry$this->singularClass\" /></td>
						<td colspan=\"".($colspan+1)."\" style=\"font-weight:bold;\">".($singularLanguageClass == null ? $this->singularName." neu anlegen" : $singularLanguageClass->getBrowserNewEntryLabel())."</td>
					</tr>" : "" );
		
		if(isset($top)) foreach($this->addedRows as $key => $value)
			$top .= "<tr><td colspan=\"".($colspan+1)."\">".$this->getInput($value)."</td>";
		
		
		return (isset($top) ? $top : "").$string.$filtered.str_replace("browserSeparatorTop", "browserSeparatorBottom", $separator).$multiPageRow."</table>";
	}
	
	/**
	 * Creates standard auto completion forms for use as quicksearch results.
	 * Available modes:
	 * 
	 * quickSearchLoadFrame
	 * 
	 * quickSearchSelectionMode
	 * 
	 * @param string $mode
	 * @return string
	 */
	public function getACHTMLBrowser($mode = ""){
		$random = rand();
		$_SESSION["BPS"]->setActualClass(get_class($this));
		$bps = $_SESSION["BPS"]->getAllProperties();

		if($this->selectionFunctions != "") $mode = "quickSearchSelectionMode";
		#else if($mode == "") $mode = "quickSearchLoadFrame";
		$html = "
		<input type=\"hidden\" id=\"AutoCompleteFields_$random\" value=\"".implode(", ",(count($this->showAttributes) == 0) ? $as : $this->showAttributes)."\" />
		<input type=\"hidden\" id=\"AutoCompleteNumRows_$random\" value=\"".count($this->attributes)."\" />
		<input type=\"hidden\" id=\"ACTranslator\" value=\"$random\" />
		".(($mode == "quickSearchLoadFrame" OR $mode == "quickSearchSelectionMode") ? "<div onmouseover=\"AC.SetMouseIn();\" onmouseout=\"AC.SetMouseOut();\" class=\"ACHandler backgroundColor1\" id=\"ACHandler_$random\"><input type=\"checkbox\" id=\"keepOpen\" value=\"1\" onclick=\"AC.makeFreeWindow(this, '$random')\" /> Ergebnisse geöffnet lassen</div>" : "")."
		<table style=\"border:0px;width:100%;\">
			<colgroup>
				<col class=\"backgroundColor2\" />
			</colgroup>";

		$l = 1;
		for($i=0;$i<count($this->attributes);$i++) {
			$aid = $this->attributes[$i]->getID(); // get the id of an object separately
			$sc = $this->attributes[$i]->getA(); // get the attributes-object from the object
			$as = PMReflector::getAttributesArray($sc); // get an array of attribute-names from the object
			$html .= "
			<tr onclick=\"AC.update(13, '', '$random');\" onmouseover=\"AC.selectByMouse('autoCompleteTRId$l"."_$random');\" onmouseout=\"AC.SetMouseOut();\" id=\"autoCompleteTRId$l"."_$random\" style=\"cursor:pointer;\">";
			
			$modeFunction = "";
			$actionCol = "";
			if($mode != ""){
				switch($mode){
					case "quickSearchLoadFrame":
						$_SESSION["BPS"]->setActualClass(get_class($this));
						$bps = $_SESSION["BPS"]->getAllProperties();
						if($bps == -1)
							die("you need to post targetFrame- and targetPlugin-values via BPS!");
						
						$html .= "<td class=\"ACCell\" style=\"width:20px;\"><img src=\"./images/i2/edit.png\" /></td>";
						$actionEditButton = "contentManager.backupFrame('".$bps["targetFrame"]."', 'lastCollection'); contentManager.loadFrame('".$bps["targetFrame"]."','$bps[targetPlugin]','$aid')";
						if($this->JSOnEdit != null) $actionEditButton = str_replace("%%VALUE%%", $aid, $this->JSOnEdit);
						$modeFunction = "<input type=\"hidden\" id=\"doACJS%attributeNameId$l"."_$random\" value=\"$actionEditButton\" />";

					break;
					case "quickSearchSelectionMode":
						$html .= "<td class=\"ACCell\" style=\"width:20px;\"><img class=\"mouseoverFade\" src=\"./images/i2/cart.png\" /></td>";
						$modeFunction = "<input type=\"hidden\" id=\"doACJS%attributeNameId$l"."_$random\" value=\"".str_replace("%%VALUE%%",$aid,$this->selectionFunctions)."\" />";
					break;
				}
			}
			
			if(count($this->showAttributes) == 0)
				for($j=0;$j<count($as);$j++) {
					if(isset($this->dontShow[$as[$j]])) continue;
					
					if($i == 0) $this->shownCols[] = $as[$j];
					
					if(isset($this->parsers[$as[$j]])) {
						$parameters = $this->makeParameterStringFromArray($this->parserParameters[$as[$j]], $sc, $aid);
						$t = $this->invokeParser($this->parsers[$as[$j]], $sc->$as[$j], $parameters);
					#eval("\$t = ".$this->parsers[$as[$j]]."(\"".$sc->$as[$j]."\",\"load\",\"".implode("%§%",$this->parserParameters[$as[$j]])."\");");
					}
					else $t = $sc->$as[$j];
					
					$html .= "
					<td class=\"ACCell\">".$t."<input type=\"hidden\" value=\"".htmlspecialchars(strip_tags($t))."\" id=\"autoComplete".$as[$j]."Id$l"."_$random\" />".str_replace("%attributeName",$as[$j],$modeFunction)."</td>";
				}
			else 
				foreach($this->showAttributes as $key => $value) {
					if($i == 0) $this->shownCols[] = $value;
										
					if(isset($this->parsers[$value])){
						$parameters = $this->makeParameterStringFromArray($this->parserParameters[$value], $sc, $aid);
						$t = $this->invokeParser($this->parsers[$value], $sc->$value, $parameters);
					}
						#eval("\$t = ".$this->parsers[$value]."(\"".$sc->$value."\",\"load\",\"".implode("%§%",$this->parserParameters[$value])."\");");
					else $t = $sc->$value;
					
					$html .= "
					<td class=\"ACCell\">".$t."<input type=\"hidden\" value=\"".htmlspecialchars(strip_tags($t))."\" id=\"autoComplete".$value."Id$l"."_$random\" />".str_replace("%attributeName",$value,$modeFunction)."</td>";		
				}
			$l++;
		}
		if(count($this->attributes) == 0){
			$html .= "<tr><td class=\"ACCell\">kein Ergebnis</td></tr>";
		}
		$html .= "
		</table>";
		
		return $html;
	}
	
	protected function invokeParser($function, $value, $parameters){
		$c = explode("::", $function);
		$method = new ReflectionMethod($c[0], $c[1]);
		return $method->invoke(null, $value, "load", $parameters);
	}
	
	protected function makeParameterStringFromArray($array, $sc, $aid){
		foreach($array AS $k => $v) {
			$v = str_replace("\$aid", $aid, $v);
			if(strpos($v,"\$sc->") !== false OR strpos($v,"\$") !== false ){
				$v = str_replace("\$sc->","",$v);
				$v = str_replace("\$","",$v);
				$array[$k] = $sc->$v;
			} else
				$array[$k] = $v;
		}
		return implode("%§%",$array);
	}
	
	public static function getArrayFromParametersString($string){
		return explode("%§%",$string);
	}
	
	public static function addReturnButton($w, $t, $p){
		$s = HTMLGUI::getArrayFromParametersString($p);
		$p2 = array_flip($_SESSION["CurrentAppPlugins"]->getAllPlugins());
		return "<input type=\"button\" value=\"zurück zu\n".$p2[$s[0]]."\" style=\"background-image:url(./images/navi/back.png);\" class=\"bigButton backgroundColor3\" onclick=\"contentManager.loadFrame('contentRight','$s[0]');\" />";
	}
	
	public static function getOptions($keys, $values, $selectedValue){
		$h = "";
		for($i=0;$i<count($keys);$i++)
			$h .= "<option value=\"".$keys[$i]."\" ".($selectedValue == $keys[$i] ? "selected=\"selected\"" : "").">".$values[$i]."</option>";
		
		return $h;
	}
	
	private function editIcon($aid){
		$B = new Button("", "./images/i2/edit.png", "icon");
		$B->className("editButton");

		$onSuccessFunction = $this->JSOnEdit != null ? str_replace("%%VALUE%%","$aid","$this->JSOnEdit") : "";
		$B->doBefore("contentManager.selectRow(this); %AFTER");
		$B->loadFrame($this->editInDisplayModeTarget, $this->singularClass, $aid, 0, "", $onSuccessFunction);

		return "<td>$B</td>";
		/*
		 * <img
			onclick=\"
				".($this->editInDisplayModeTarget == "contentLeft" ? "lastLoadedLeft = $aid;lastLoadedLeftPlugin = '$this->singularClass';" : "")."
				new Ajax.Request('./interface/loadFrame.php?p=".$this->singularClass."&id=$aid'".($this->JSOnEdit != null ? str_replace("%%VALUE%%","$aid",",{onSuccess: $this->JSOnEdit}") : ",{onSuccess: function(transport){ if(checkResponse(transport)) $('$this->editInDisplayModeTarget').update(transport.responseText); }}").");\" src=\"./images/i2/edit.png\" class=\"mouseoverFade\" />
		 */
	}
	
	public function getContextMenu($keysAndLabels, $saveTo, $identifier, $selectedKey, $onSuccessFunction = 'phynxContextMenu.stop();', $onClickFunction = ""){
		
		$html = "
		<table>";
		foreach($keysAndLabels as $key => $label){
			$action = "phynxContextMenu.saveSelection('$saveTo','$identifier','$key','".addslashes(stripslashes($onSuccessFunction))."');";

			if($onClickFunction != "")
				$action = str_replace("%VALUE", $key, $onClickFunction);

			$html .= "
			<tr onclick=\"$action\" id=\"cMEntry$key\" style=\"cursor:pointer;\" ".($selectedKey == $key ? "class=\"backgroundColor1\"" : "")." onmouseover=\"oldStyle = this.className;this.className='backgroundColor2';\" onmouseout=\"this.className=oldStyle;\">
				<td>$label</td>
			</tr>";
		}
		$html .= "
		</table>";
		
		return $html;
		
	}
	
	/**
	 * 
	 * 
	 * @param string $attributeName
	 * @param Collection $C
	 * @param string $labelAttribute
	 * @param string $zeroElement
	 */
	public function selectWithCollection($attributeName, Collection $C, $labelAttribute, $zeroElement = ""){
		#$C->lCV3();
		$this->setType($attributeName,"select");
		
		$values = array();
		$labels = array();
		
		if($zeroElement != "") {
			$values[] = "0";
			$labels[] = $zeroElement;
		}
		
		while(($Ci = $C->getNextEntry())){
			$CiA = $Ci->getA();
			$values[] = $Ci->getID();
			$labels[] = $CiA->$labelAttribute;
		}
		
		$this->setOptions($attributeName, $values, $labels);
	}
	
	public function selectOptgroup($attributeName, $value2optgroup){
		$this->optgroups[$attributeName] = $value2optgroup;
	}
	
	/**
	 * Creates HTML for several global context menus
	 */
	public function getContextMenuHTML($identifier){
		
		$s = explode(":",$identifier);
		switch($s[0]){
			case "operations":
				$onDeleteEvent = "";
				$onDeleteQuestion = "";
				if(PMReflector::implementsInterface($s[1],"iDeletable2")){
					$c = $s[1];
					$c = new $c(-1);
					$onDeleteEvent = $c->getOnDeleteEvent();
					$onDeleteQuestion = $c->getOnDeleteQuestion();
				}

				$texts = $this->languageClass->getEditTexts();

				#$BRepeatable = "";

				$T = new HTMLTable(1);
				
				$Buttons = "";
				if($s[3]{0} == "1"){
					$B = new Button($texts["Neu mit Werten"], "new", "icon");
					$B->onclick(OnEvent::reload("Left", "HTMLGUI;insertAsNew:true")/*"contentManager.reloadFrameLeft('HTMLGUI;insertAsNew:true');"*/);
					$B->style("margin-right:10px;");
					
					$Buttons .= $B;
				}
				
				if($s[3]{1} == "1"){
					$B = new Button($texts["Kopieren"], "seiten", "icon");
					$B->rmePCR(str_replace("GUI", "", $s[1]), $s[2], 'cloneMe', "", "function(transport){ lastLoadedLeft = (transport.responseText == '' ? -1 : transport.responseText); contentManager.reloadFrameLeft(); contentManager.reloadFrameRight(); }");
					#$B->onclick("rme('$s[1]','$s[2]','cloneMe','', 'lastLoadedLeft = (transport.responseText == \'\' ? -1 : transport.responseText); contentManager.reloadFrameLeft(); contentManager.reloadFrameRight();');");
					$B->style("margin-right:10px;");
					
					$Buttons .= $B;
				}
				
				if($s[3]{2} == "1"){
					$B = new Button($texts["Löschen"], "trash", "icon");
					$B->onclick("deleteClass('".str_replace("GUI", "", $s[1])."','$s[2]',".($onDeleteEvent == "" ? "function() {  contentManager.reloadFrameRight(); if(typeof lastLoadedLeft != 'undefined' && lastLoadedLeft == '$s[2]') $('contentLeft').update(''); }" : $onDeleteEvent).",'".($onDeleteQuestion == "" ? $texts["Wirklich löschen?"] : $onDeleteQuestion)."');");
					$B->style("margin-right:10px;");
					
					$Buttons .= $B;
				}
				
				if($s[3]{3} == "1"){
					$BRepeatable = new Button($texts["Repeatable erstellen"],"redo");
					$BRepeatable->type("icon");
					$BRepeatable->onclick("contentManager.newClassButton('Repeatable','','contentLeft','RepeatableGUI;RepeatablePlugin:$s[1];RepeatablePluginElementID:$s[2]');");
					
					$Buttons .= $BRepeatable;
				}
				
				if($s[3]{4} == "1"){
					$B = new Button($texts["XML Export"], "export", "icon");
					$B->onclick("windowWithRme('$s[1]', '$s[2]', 'getXML', '');phynxContextMenu.stop();");
					$B->style("margin-right:10px;");
					
					$Buttons .= $B;
				}
				
				
				$T->addRow(array($Buttons));
				
				$T->addRowClass("backgroundColor0");
						
				echo $T."<p><small style=\"color:grey;\">Interne ID des Eintrags: $s[2]</small></p>";
						
				/*echo "
				<table style=\"text-align:center;border:0px;\">
					<tr>
						".($s[3]{0} == "1" ? "<td><img class=\"mouseoverFade\" src=\"./images/navi/new.png\" title=\"".$texts["Neu mit Werten"]."\" onclick=\"contentManager.reloadFrameLeft('HTMLGUI;insertAsNew:true');\" /></td>" : "")."
						".($s[3]{1} == "1" ? "<td><img class=\"mouseoverFade\" src=\"./images/navi/seiten.png\" title=\"".$texts["Kopieren"]."\" onclick=\"rme('$s[1]','$s[2]','cloneMe','', 'lastLoadedLeft = (transport.responseText == \'\' ? -1 : transport.responseText); contentManager.reloadFrameLeft(); contentManager.reloadFrameRight();');\" /></td>" : "")."
						".($s[3]{2} == "1" ? "<td><img class=\"mouseoverFade\" src=\"./images/navi/trash.png\" title=\"".$texts["Löschen"]."\" onclick=\"deleteClass('$s[1]','$s[2]',".($onDeleteEvent == "" ? "function() {  contentManager.reloadFrameRight(); if(typeof lastLoadedLeft != 'undefined' && lastLoadedLeft == '$s[2]') $('contentLeft').update(''); }" : $onDeleteEvent).",'".($onDeleteQuestion == "" ? $texts["Wirklich löschen?"] : $onDeleteQuestion)."');\" /></td>" : "")."
						".($s[3]{4} == "1" ? "<td><img class=\"mouseoverFade\" src=\"./images/navi/export.png\" title=\"".$texts["XML Export"]."\" onclick=\"windowWithRme('$s[1]', '$s[2]', 'getXML', '');phynxContextMenu.stop();\" /></td>" : "")."
						$BRepeatable
					</tr>
				</table>";*/
			break;
			
			case "multiPageSettings":
				$texts = $this->languageClass->getBrowserTexts();
				$mU = new mUserdata();
				$entriesPerPage = $mU->getUDValue("entriesPerPage$s[1]");
				if($entriesPerPage == null) $entriesPerPage = 20;
				echo "
				<table style=\"border:0px;\">
					<tr>
						<td class=\"backgroundColor3\">".$texts["Anzahl Einträge pro Seite"].":</td>
					</tr>
					<tr>
						<td>
						<input
								type=\"image\" 
								src=\"./images/i2/save.gif\"
								style=\"border: 0px none ; width: 18px;float:right;\" 
								onclick=\"contentManager.rmePCR('HTML','', 'saveContextMenu', Array('multiPageSettings', '$s[1]:'+$('entriesPerPageCM').value), 'phynxContextMenu.stop(); contentManager.reloadFrame(\'".(!isset($s[2]) ? "contentRight" : $s[2])."\');');\" />
						<input style=\"width:130px;text-align:right;\" id=\"entriesPerPageCM\" type=\"text\" value=\"$entriesPerPage\" /></td>
					</tr>
					<tr>
						<td class=\"backgroundColor3\">
						</td>
					</tr>
				</table>";
				
				$selectForOrderByField = "";
				$n = $s[1]."GUI";
				if(PMReflector::implementsInterface($n,"iOrderByField")){
					
					$mU = new mUserdata();
					$HKs = $mU->getUDValue("OrderByFieldInHTMLGUI$s[1]");
						
					$selectForOrderByField = "<select onchange=\"rme('HTML','','saveContextMenu',Array('setOrderByField','$s[1];:;'+this.value),'if(checkResponse(transport)) { phynxContextMenu.stop(); contentManager.reloadFrameRight(); }');\"><option ".(($HKs == null OR $HKs == "default") ? "selected=\"selected\"" : "")." value=\"default\">Standard-Sortierung</option><optgroup label=\"aufsteigend\">";
					$cFOBy = new $n();
					$cFOBy = $cFOBy->getOrderByFields();
					
					foreach($cFOBy as $k => $v)
						$selectForOrderByField .= "<option ".($HKs == "$k;ASC" ? "selected=\"selected\"" : "")." value=\"$k;ASC\">".$v."</option>";
					
					$selectForOrderByField .= "</optgroup><optgroup label=\"absteigend\">";
					
					foreach($cFOBy as $k => $v)
						$selectForOrderByField .= "<option ".($HKs == "$k;DESC" ? "selected=\"selected\"" : "")." value=\"$k;DESC\">".$v."</option>";
					
					$selectForOrderByField .= "</optgroup></select>";
					
					echo "<div style=\"height:10px;\" class=\"backgroundColor1\"></div>
							<table style=\"border:0px;\">
								<colgroup>
									<col />
								</colgroup>
								<tr>
									<td class=\"backgroundColor3\">".$texts["nach Spalte sortieren"].":</td>
								</tr>
								<tr>
									<td>$selectForOrderByField</td>
								</tr>
							</table>";
				}
				
				try {
					$n = $s[1]."GUI";
					$c = new $n();
					if(PMReflector::implementsInterface($n,"iCategoryFilter")){
						$Ks = $c->getAvailableCategories();
						if($Ks == null) return;
						
						$mU = new mUserdata();
						$HKs = $mU->getUDValue("filteredCategoriesInHTMLGUI$s[1]");
						$HKs = explode(";",$HKs);
						
						$checks = "";
						foreach($Ks as $key => $value){
							$checks .= "
							<tr>
								<td><input type=\"checkbox\" id=\"hide$key\" value=\"$key\" name=\"$key\" ".(in_array("$key",$HKs) ? "checked=\"checked\"" : "")." /></td>
								<td onclick=\"$('hide$key').checked = !$('hide$key').checked;\" style=\"cursor:pointer;\">$value</td>
							</tr>";
						}
						
						echo "<div style=\"height:10px;\" class=\"backgroundColor1\"></div>
						<form id=\"filterCatsOf$s[1]\">
							<table style=\"border:0px;\">
								<colgroup>
									<col class=\"backgroundColor2\" style=\"width:20px;\" />
									<col />
								</colgroup>
								<tr>
									<td colspan=\"2\" class=\"backgroundColor3\">".$texts["nach Kategorien filtern"].":</td>
								</tr>
								$checks
								<tr>
									<td colspan=\"2\" class=\"backgroundColor3\">
										<input
											type=\"button\" 
											value=\"".$texts["speichern"]."\" 
											style=\"background-image:url(./images/i2/save.gif);\" 
											onclick=\"contentManager.rmePCR('HTML','', 'saveContextMenu', Array('filterCategories', '$s[1]--'+joinFormFields('filterCatsOf$s[1]').replace(/\&/g,';').replace(/=/g,':')), 'phynxContextMenu.stop(); contentManager.reloadFrame(\'contentRight\', \'\', 0);');\" />
									</td>
								</tr>
							</table>
						</form>";
					}
				} catch (ClassNotFoundException $e) {}
				
				
				if(PMReflector::implementsInterface($n,"iCustomSettings")){
					$cFOBy = new $n();
					echo "<div style=\"height:10px;\" class=\"backgroundColor1\"></div>";
					echo $cFOBy->getCustomSettings();
				}
			break;
			
			/*case "upload":
				$texts = $this->languageClass->getBrowserTexts();
				echo "
					<form 
						action=\"./interface/set.php\" 
						method=\"post\" 
						enctype=\"multipart/form-data\" 
						id=\"formImage\" 
						onsubmit=\"return AIM.submit($('formImage'), {'onComplete' : function(){
							$('uploadImage').src = $('uploadImage').src+'&r2=".rand()."'
							$('uploadImage').style.display = '';
							phynxContextMenu.stop();
						}});\"
					><p>
						<input type=\"file\" name=\"datei\" size=\"4\" /><br /><br />
						<input type=\"submit\" value=\"".$texts["hochladen"]."\" />
						<input type=\"hidden\" name=\"id\" value=\"$s[1]\" />
						<input type=\"hidden\" name=\"class\" value=\"$s[2]\" />
						<input type=\"hidden\" name=\"saveToAttribute\" value=\"$s[3]\" /></p>
						<p>".$texts["<=100KB"]."</p>
					</form>";
			break;*/
		}
	}
	
	public function saveContextMenu($identifier, $key){
		switch($identifier){
			case "multiPageSettings":
				$s = explode(":", $key);
				$z = Util::parseFloat("de_DE", $s[1]);
				if($z == null) Red::alertD("Bitte geben Sie eine Zahl ein");
				if($z > 50) $z = 50;
				$mU = new mUserdata();
				$mU->setUserdata("entriesPerPage$s[0]",floor($z));
			break;
			
			case "filterCategories":
				$c = explode("--",$key);
				$keys = explode(";",$c[1]);
				$ausgeblendet = array();
				foreach($keys AS $k => $v){
					if(strpos($v, ":1") !== false){
						$ausgeblendet[] = str_replace(":1","",$v);
					}
				}
				
				$mU = new mUserdata();
				$mU->setUserdata("filteredCategoriesInHTMLGUI$c[0]",implode(";",$ausgeblendet));
				echo implode(";",$ausgeblendet);
			break;
			
			case "searchFilter":
				$v = explode(";:;",$key);
				
				$mU = new mUserdata();
					
				if($v[1] == "") {
					$mU->delUserdata("searchFilterInHTMLGUI$v[0]");
				} else {
					$mU->setUserdata("searchFilterInHTMLGUI$v[0]",$v[1]);
				}
				
			break;
			
			case "setOrderByField":
				$v = split(";:;",$key);
				
				$mU = new mUserdata();
				if($v[1] != "default")
					$mU->setUserdata("OrderByFieldInHTMLGUI$v[0]",$v[1]);
				else
					$mU->delUserdata("OrderByFieldInHTMLGUI$v[0]");
				
			break;
			
			case "deleteFilters":
				
				$mU = new mUserdata();
				$mU->delUserdata("filteredCategoriesInHTMLGUI$key");
				$mU = new mUserdata();
				$mU->delUserdata("searchFilterInHTMLGUI$key");
				
				#echo "message:'$key'";
			break;
		}
	}
	
	/**
	 * You may use this default version check to see if the version of the plugin matches the application's version
	 * 
	 * @param string $plugin
	 */
	public function VersionCheck($plugin){
		$l = $this->languageClass->getBrowserTexts();

		if(Util::versionCheck($_SESSION["applications"]->getRunningVersion(), $_SESSION["CurrentAppPlugins"]->getVersionOfPlugin($plugin) , "!=")){
					
			$t = new HTMLTable(1);
			$t->addRow(str_replace(array("%1","%2"),array($_SESSION["CurrentAppPlugins"]->getVersionOfPlugin($plugin), $_SESSION["applications"]->getRunningVersion()),$l["versionError"]));
			$t->addRow(Installation::getReloadButton());
			die($t->getHTML());
		}
	}

	public function customize($customizer){
		if($customizer == null) return;

		try {
			if($this->object == null) die("please use HTMLGUI::setObject instead of HTMLGUI::setAttributes");
			$customizer->customizeGUI($this->object, $this);
		} catch (ClassNotFoundException $e){

		}
	}

	public function translate($translationClass){
		if($translationClass == null) return;

		$labels = $translationClass->getLabels();
		$labelDescriptions = $translationClass->getLabelDescriptions();
		$fieldDescriptions = $translationClass->getFieldDescriptions();

		$this->setLabelCaption($translationClass->getEditCaption());
		$this->setLabelSaveButton($translationClass->getSaveButtonLabel());

		if($labels != null)
			foreach($labels AS $k => $v)
				$this->setLabel($k, $v);

		if($labelDescriptions != null)
			foreach($labelDescriptions AS $k => $v)
				$this->setLabelDescription($k, $v);

		if($fieldDescriptions != null)
			foreach($fieldDescriptions AS $k => $v)
				$this->setFieldDescription($k, $v);
	}

	// <editor-fold defaultstate="collapsed" desc="getMultiPageButtons">
	protected function getMultiPageButtons(){

		if(count($this->multiPageMode) > 0){
			if(isset($this->inSubFrame) AND $this->inSubFrame) $this->multiPageMode[3] = "subFrame".get_class($this->object);

			if($this->multiPageMode[2] == 0){
				$userDefinedEntriesPerPage = true;
				$mU = new mUserdata();
				$this->multiPageMode[2] = $mU->getUDValue("entriesPerPage{$this->multiPageMode[4]}");
				if($this->multiPageMode[2] == null) $this->multiPageMode[2] = 20;
			}

			if($this->multiPageMode[1] == "undefined") $this->multiPageMode[1] = 0;

			$pages = ceil($this->multiPageMode[0] / $this->multiPageMode[2]);

			if($this->multiPageMode[1] != 0) $pageLinks = "<a href=\"javascript:contentManager.loadPage('".$this->multiPageMode[3]."', '0');/*contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."','', '0');*/\"><span class=\"iconic arrow_left\" style=\"border-left-width:2px;\"></span></a> ";
			else $pageLinks = "<span class=\"iconic arrow_left inactive\" style=\"border-left-width:2px;\"></span> ";

			if($this->multiPageMode[1] != 0) $pageLinks .= "<a href=\"javascript:contentManager.backwardOnePage('".$this->multiPageMode[3]."');/*contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."','','".($this->multiPageMode[1]-1)."');*/\"><span class=\"iconic arrow_left\" style=\"margin-right:7px;\"></span></a> ";
			else $pageLinks .= "<span class=\"iconic arrow_left inactive\" style=\"margin-right:7px;\"></span> ";

			if($this->multiPageMode[1] != $pages - 1) $pageLinks .= "<a href=\"javascript:contentManager.forwardOnePage('".$this->multiPageMode[3]."');/*contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."','','".($this->multiPageMode[1]+1)."');*/\"><span class=\"iconic arrow_right\" style=\"margin-left:7px;\"></span></a> ";
			else $pageLinks .= "<span class=\"iconic arrow_right inactive\" style=\"margin-left:7px;\"></span> ";

			if($this->multiPageMode[1] != $pages - 1) $pageLinks .= "<a href=\"javascript:contentManager.loadPage('".$this->multiPageMode[3]."',".($pages-1).");/*contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."','','".($pages-1)."');*/\"><span class=\"iconic arrow_right\" style=\"border-right-width:2px;\"></span></a> | ";
			else $pageLinks .= "<span class=\"iconic arrow_right inactive\" style=\"border-right-width:2px;\"></span> | ";

			$start = $this->multiPageMode[1] - 3;
			if($start < 0) $start = 0;

			$end = $this->multiPageMode[1] + 3;
			if($end > $pages - 1) $end = $pages - 1;

			for($i=$start; $i<=$end; $i++)
				if($this->multiPageMode[1] != "$i") $pageLinks .= "<a href=\"javascript:contentManager.loadPage('".$this->multiPageMode[3]."','".$i."');/*contentManager.loadFrame('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."','','".$i."');*/\">".($i+1)."</a> ";
				else $pageLinks .= ($i+1)." ";
		} else $pageLinks = "";

		return "".($pages == 0 ? 1 : $pages)." ".(($pages == 0 ? 1 : $pages) != 1 ? $this->texts["Seiten"] : $this->texts["Seite"]).": ".$pageLinks;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getPageOptionsButton">
	/**
	 * returns the button for the context menu to set the number of displayed entries
	 *
	 * @return Button
	 */
	protected function getPageOptionsButton(){
		$BSettings = new Button($this->texts["Einstellungen"], "wrench", "iconic");
		$BSettings->onclick("phynxContextMenu.start(this, 'HTML','multiPageSettings:{$this->multiPageMode[4]}:{$this->multiPageMode[3]}','".$this->texts["Einstellungen"].":');");
		#$BSettings->type("icon");
		#$BSettings->className("settingsButtonBrowser");
		
		return $BSettings;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getPageSelectionField">
	/**
	 * returns the text field to select a specific page
	 *
	 * @return HTMLInput
	 */
	public function getPageSelectionField(){
		$IPage = new HTMLInput("page", "text", $this->multiPageMode[1]+1);
		$IPage->onkeyup("if(event.keyCode == 13 && this.value > 0) contentManager.loadPage('{$this->multiPageMode[3]}',this.value - 1);");
		$IPage->hasFocusEvent(true);

		return $IPage;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getQuicksearchField">
	protected function getQuicksearchField(){
		if($this->quickSearchPlugin != ""){

			$B = "";
			$K = "";
			$showSF = PMReflector::implementsInterface($this->quickSearchPlugin."GUI","iSearchFilter");
			if($showSF){
				
				$B = new Button("Suche als Filter anwenden","./images/i2/searchFilter.png", "icon");
				$B->style("float:right;");
				$B->rme("HTML","","saveContextMenu", array("'searchFilter'","'$this->quickSearchPlugin;:;'+$('quickSearch$this->quickSearchPlugin').value"),"if(checkResponse(transport)) contentManager.reloadFrameRight();");

				$mU = new mUserdata();
				$K = $mU->getUDValue("searchFilterInHTMLGUI".$this->quickSearchPlugin);
			}

			$BSearchInfo = new Button("","info","iconic");
			$BSearchInfo->onclick("phynxContextMenu.start(this, '$this->quickSearchPlugin','searchHelp','".$this->texts["Suche"].":','left');");
			$BSearchInfo->style("cursor:help;");
			#$BSearchInfo->type("icon");

			$quickSearchRow = "$B
							<input
								autocomplete=\"off\"
								onfocus=\"focusMe(this); ACInputHasFocus=true; AC.start(this); if(this.value != '') AC.update('10', this, '$this->quickSearchPlugin', 'quickSearchLoadFrame');\"
								onblur=\"blurMe(this); ACInputHasFocus=false; AC.end(this);\"
								id=\"quickSearch$this->quickSearchPlugin\"
								onkeyup=\"AC.update(event.keyCode, this, '$this->quickSearchPlugin','quickSearchLoadFrame');\"
								type=\"text\"
								placeholder=\"Suche\"
								value=\"$K\"
								".($showSF ? "style=\"width:90%;\"" : "")."
							/>";
			return array($quickSearchRow, $BSearchInfo);
		}
		return array("","");
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getDesktopLinkSymbol">
	public function getDesktopLinkButton(){
		try {
		if(!PMReflector::implementsInterface(get_class($this->object), "iDesktopLink")) return "";
		} catch(ReflectionException $e){
			return "";
		}

		$B = new Button("Desktop-Link anlegen","link", "iconic");
		$B->style("float:right;margin-right:10px;margin-top:-3px;");
		$B->onclick("DesktopLink.createNew('".$this->object->getClearClass()."','".$this->object->getID()."','contentLeft','".$_SESSION["applications"]->getActiveApplication()."');");

		return $B;
	}
	// </editor-fold>

}
?>
