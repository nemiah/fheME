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
class HTMLGUIX {

	protected $object;
	#protected $frame;
	protected $attributes;
	protected $parsers = array();

	protected $showTrash = true;
	protected $showEdit = true;
	protected $showNew = true;
	protected $showQuicksearch = false;
	protected $showPageFlip = true;
	protected $showSave = true;
	protected $showInputs = true;

	protected $colWidth = array();
	protected $colStyle = array();

	protected $className;

	protected $displayMode = null;
	protected $displayGroup = null;

	protected $caption;

	protected $GUIFactory;

	protected $multiPageDetails;

	protected $name;
	protected $features = array();

	protected $appended = array();
	protected $prepended = array();

	protected $languageClass;

	protected $sideButtons = array();
	protected $topButtons = array();
	protected $fieldButtons = array();
	protected $fieldEvents = array();
	protected $hiddenLines = array();
	protected $hiddenUseInit = false;

	protected $labels = array();
	protected $types = array();
	protected $options = array();
	protected $descriptionsField = array();
	protected $spaces = array();
	protected $formID;

	protected $header;
	
	protected $functionEntrySave = "function(transport){ contentManager.reloadFrame('contentRight'); /*ADD*/ }";

	protected $blacklists;

	protected $inputStyles = array();
	
	protected $sortable;
	
	protected $autocomplete = array();
	protected $tip = "";
	protected $requestFocus;
	protected $targetFrame;
	
	public function __construct($object = null, $collectionName = null){
		if($object != null)
			$this->object($object, $collectionName);

		$this->languageClass = $this->loadLanguageClass("HTML");
	}
	
	public function requestFocus($first, $second = null){
		$this->requestFocus = array($first, $second);
	}

	public function tip(){
		$targetClass = $this->object->getClearClass();
		
		$this->tip = self::tipJS($targetClass);
	}
	
	public static function tipJS($plugin){
		$targetClass = $plugin;
		if(mUserdata::getUDValueS("hideTooltips", "0"))
			return;
		
		$hide = mUserdata::getUDValueS("hideTooltip$targetClass", "0");
		if($hide)
			return;
		
		$xml = SpellbookGUI::getSpell($targetClass);
		
		$entries = $_SESSION["CurrentAppPlugins"]->getMenuEntries();
		
		return OnEvent::script("\$j('#{$targetClass}MenuImage').qtip(\$j.extend({}, qTipSharedRed, {
		content: {
			text: '".$xml->plugin[0]->description."<br /><div style=\"margin-top:10px;\"><a href=\"#\" style=\"color:grey;\" onclick=\"".addslashes(OnEvent::rme(new mUserdata(-1), "setUserdata", array("'hideTooltip$targetClass'", "'1'", "''", "0", "1")))." return false;\">Diesen Tipp nicht mehr anzeigen</a></div><div style=\"clear:both;margin-top:5px;\"><a href=\"#\" style=\"color:grey;\" onclick=\"".addslashes(OnEvent::rme(new mUserdata(-1), "setUserdata", array("'hideTooltips'", "'1'", "''", "0", "1")))." return false;\">Keine Tipps mehr anzeigen</a></div>', 
			title: {
				text: '".array_search($targetClass, $entries)."',
				button: true
			}
		}
	}));");
	}
	
	public function name($name){
		$this->name = $name;
	}

	public function autoComplete($fieldName, $targetClass, $onSelectionFunction = null){
		$this->autocomplete[$fieldName] = array($targetClass, $onSelectionFunction);
	}
	
	public function blacklists(array $EditIDs, array $DeleteIDs = null){
		if($DeleteIDs == null)
			$DeleteIDs = $EditIDs;
		
		$this->blacklists = array($EditIDs, $DeleteIDs);
	}

	/**
	 * @param string/Object $labelOrButton
	 * @param string $image
	 * @return Button
	 */
	public function addTopButton($labelOrButton, $image = ""){
		if(!is_object($labelOrButton))
			$B = new Button($labelOrButton, $image);
		else
			$B = $labelOrButton;

		$this->topButtons[] = $B;

		return $B;
	}

	public function operationsButton(){
		$pluginName = str_replace("GUI", "", get_class($this->object));
		$id = $this->object->getID();
		
		$userCanDelete = mUserdata::isDisallowedTo("cantDelete".$pluginName);
		$userCanCreate = mUserdata::isDisallowedTo("cantCreate".$pluginName);
		/*if($this->texts == null) {
			$c = $this->loadLanguageClass("HTML");
			$this->texts = $c->getEditTexts();
		}*/

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
			$B->style("float:right;margin-top:-3px;");
			$B->contextMenu("HTML", "operations:$pluginName:$id:$os", "Operationen");
			
			$html = $B;
		}
			#$html .= "<img title=\"Operationen\" id=\"".$pluginName."Operations\" src=\"./images/i2/settings.png\" onclick=\"phynxContextMenu.start(this, 'HTML','operations:$pluginName:$id:$os','".$this->texts["Operationen"].":');\" style=\"float:right;\" />";

		return $html;
	}
	
	public function header(array $header){
		$this->header = $header;
	}
	
	/**
	 * @param string/Object $labelOrButton
	 * @param string $image
	 * @return Button
	 */
	public function addSideButton($labelOrButton, $image = ""){
		if($labelOrButton == null)
			return;
		
		if(!is_object($labelOrButton))
			$B = new Button($labelOrButton, $image);
		else
			$B = $labelOrButton;
		
		$this->sideButtons[] = $B;

		return $B;
	}

	public function addFieldButton($fieldName, $labelOrButton, $image = ""){
		if(!is_object($labelOrButton)){
			$B = new Button($labelOrButton, $image);
		} else
			$B = $labelOrButton;
		
		$B->type("icon");
		$B->style("float:right;");
		
		$this->fieldButtons[$fieldName] = $B;
		
		return $B;
	}

	public function inputStyle($fieldName, $style){
		$this->inputStyles[$fieldName] = $style;
	}
	
	public function addFieldEvent($fieldName, $onEvent, $action){
		$this->fieldEvents[] = array($fieldName, $onEvent, $action);
	}

	public function toggleFieldsInit($fieldName, $initiallyHide){
		$this->addFieldEvent($fieldName, "onChange", "contentManager.toggleFormFields('hide', [".(count($initiallyHide) > 0 ? "'".implode("','", $initiallyHide)."'" : "")."], 'edit".get_class($this->object)."');");
	
		foreach($initiallyHide AS $field)
			$this->hideLine($field);
		
		$this->hiddenUseInit = true;
	}
	
	public function toggleFields($fieldName, $values, $showOnTrue, $showOnFalse = null){
		if(!is_array($values))
			$values = array($values);

		if(!is_array($showOnTrue))
			$showOnTrue = array($showOnTrue);

		if(!is_array($showOnFalse) AND $showOnFalse != null)
			$showOnFalse = array($showOnFalse);

		$cTest = false;
		$test = "";
		foreach ($values AS $v){
			$test .= ($test != "" ? " OR " : "").((isset($this->types[$fieldName]) AND $this->types[$fieldName] != "checkbox") ? "this.value == '$v'" : "this.checked");

			if($this->object->A($fieldName) == $v)
				$cTest = true;
		}

		if($cTest){
			if($showOnFalse != null)
				$this->hideLine($showOnFalse);
			
			$this->showLine($showOnTrue);
		} elseif(!$this->hiddenUseInit)
			$this->hideLine($showOnTrue);
		
		$this->addFieldEvent($fieldName, "onChange", "contentManager.toggleFormFieldsTest((".  str_replace("OR", "||", $test)."), [".(count($showOnTrue) > 0 ? "'".implode("','", $showOnTrue)."'" : "")."], [".($showOnFalse != null ? "'".implode("','", $showOnFalse)."'" : "")."], 'edit".get_class($this->object)."', ".($this->hiddenUseInit ? "true" : "false").");");
	}

	public function showLine($fieldNames){
		if(!is_array($fieldNames))
			$fieldNames = array($fieldNames);

		foreach($fieldNames AS $field){
			$pos = array_search($field, $this->hiddenLines);
			if($pos !== false)
				unset($this->hiddenLines[$pos]);
		}
	}
	
	public function hideLine($fieldNames){
		if(is_array($fieldNames))
			$this->hiddenLines = array_merge($this->hiddenLines, $fieldNames);
		else
			$this->hiddenLines[] = $fieldNames;
	}

	// <editor-fold defaultstate="collapsed" desc="displayGroup">
	function displayGroup($attributeName, $parser = null){
		$this->displayGroup = array($attributeName, $parser);
	}
	// </editor-fold>

	public function label($fieldName, $label){
		$this->labels[$fieldName] = $label;
	}

	public function labels($fieldName = null){
		if($fieldName != null){
			if(isset($this->labels[$fieldName]))
				return $this->labels[$fieldName];
			else
				return ucfirst($fieldName);
		}
		
		return $this->labels;
	}
	
	public function formID($formID){
		$this->formID = $formID;
	}

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
		

 		$this->options[$fieldName] = $options;
	}

	public function descriptionField($fieldName, $description){
		$this->descriptionsField[$fieldName] = $description;
	}

	public function space($fieldName, $label = ""){
		$this->spaces[$fieldName] = $label;
	}

	// <editor-fold defaultstate="collapsed" desc="displayMode">
	/**
	 * Supported display modes:
	 * BrowserRight
	 * BrowserLeft
	 * popup
	 * CRMSubframeContainer
	 *
	 * @param string $DM
	 */
	public function displayMode($DM = null, $targetFrame = null){
		if($DM == null) return $this->displayMode;
		
		$this->displayMode = $DM;

		if($DM == "popup")
			$this->addToEvent("onSave", "/*ADD*/ contentManager.reloadFrame('contentLeft'); Popup.close('".$this->object->getClearClass("GUI")."', 'edit');");

		if($DM == "popupN")
			$this->addToEvent("onSave", "/*ADD*/ Popup.close('".$this->object->getClearClass("GUI")."', 'edit');");
		
		if($DM == "popupS")
			$this->addToEvent("onSave", "/*ADD*/ contentManager.reloadFrame('contentScreen'); Popup.close('".$this->object->getClearClass("GUI")."', 'edit');");

		if($DM == "popupL")
			$this->replaceEvent("onSave", "function(transport) { /*ADD*/ contentManager.reloadFrame('contentLeft'); Popup.close('".$this->object->getClearClass("GUI")."', 'edit'); }");
			
		if($DM == "popupC")
			$this->addToEvent("onSave", "/*ADD*/ contentManager.reloadFrame('contentLeft'); Popup.close('m".$this->object->getClearClass("GUI")."', 'edit');");

		if($DM == "popup" AND $this->object instanceof Collection)
			$this->addToEvent("onDelete", "Popup.refresh('".$this->object->getClearClass()."');");

		return $this->displayMode;
	}
	// </editor-fold>

	public function targetFrame($frame){
		$this->targetFrame = $frame;
	}

	// <editor-fold defaultstate="collapsed" desc="caption">
	public function caption($defaultCaption){
		$this->caption = $defaultCaption;
	}
	// </editor-fold>

	/**
	 * Use this method to set the Object you want to create a GUI for.
	 *
	 * @param Collection PersistentObject $object
	 */
	// <editor-fold defaultstate="collapsed" desc="object">
	public function object($object, $collectionName = null){
		if($object instanceof PersistentObject){
			$this->object = $object;
			#$this->frame("contentLeft");
			$this->className = str_replace("GUI", "", get_class($object));
			$this->caption($this->className);
		}

		if($object instanceof Collection){
			$this->object = $object;
			#$this->attributes = null;
			#$this->frame("contentRight");
			$this->className = $object->getCollectionOf();
			$this->multiPageDetails = $object->getMultiPageDetails();
		}
		$this->GUIFactory = new GUIFactory($this->className, $collectionName);
	}
	// </editor-fold>

	/**
	 * This is a cool but complex function which lets you define another function to
	 * evaluate the value of the attribute before displaying it.
	 *
	 * E.g. setParser("AttributeName","HTMLGUIX::attribParser");
	 *
	 * The first parameter $w of attribParser($w, $E); is the old value of the attribute.
	 * The second parameter is the object which is currently processed
	 *
	 * @param string $attributeName
	 * @param string $function
	 */
	// <editor-fold defaultstate="collapsed" desc="parser">
	function parser($attributeName, $function) {
		$this->parsers[$attributeName] = $function;
		#$this->parserParameters[$attributeName] = $parameters;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="colWidth">
	public function colWidth($attributeName, $width){
		#$this->colWidth[$attributeName] = $width;
		if(!isset($this->colStyle[$attributeName])) $this->colStyle[$attributeName] = "";
		$this->colStyle[$attributeName] .= "width:$width".(strpos($width, "px") === false ? "px" : "").";";
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="colStyle">
	public function colStyle($attributeName, $style){
		if(!isset($this->colStyle[$attributeName])) $this->colStyle[$attributeName] = "";
		$this->colStyle[$attributeName] .= $style;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="attributes">
	public function attributes(array $attributes){
		$this->attributes = $attributes;
	}
	// </editor-fold>


	public function addToEvent($event, $function){
		$this->GUIFactory->addToEvent($event, $function);

		switch($event){
			case "onSave":
				$this->functionEntrySave = str_replace("/*ADD*/", $function, $this->functionEntrySave);
			break;
		}
	}

	public function replaceEvent($event, $function){
		$this->GUIFactory->replaceEvent($event, $function);

		switch($event){
			case "onSave":
				$this->functionEntrySave = $function;
			break;
		}
	}

	public function insertAttribute($where, $fieldName, $insertedFieldName){
		if($where == "after")
			$add = 1;

		if($where == "before")
			$add = 0;

		$resetKeys = array();
		foreach($this->attributes AS $v)
			$resetKeys[] = $v;
		
		$this->attributes = $resetKeys;
		
		$first = array_splice($this->attributes, 0, array_search($fieldName, $this->attributes) + $add);
		$last = array_splice($this->attributes, array_search($fieldName, $this->attributes));

		$this->attributes = array_merge($first, array($insertedFieldName), $last);
		
	}

	public function removeAttribute($fieldName){
		if(array_search($fieldName, $this->attributes) !== false)
			unset($this->attributes[array_search($fieldName, $this->attributes)]);
	}

	// <editor-fold defaultstate="collapsed" desc="frame">
	#public function frame($frame){
	#	$this->frame = $frame;
	#}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="options">
	public function options($showTrash = true, $showEdit = true, $showNew = true, $showQuicksearch = false, $showPageFlip = true){
		$this->showTrash = $showTrash;
		$this->showEdit = $showEdit;
		$this->showNew = $showNew;
		$this->showQuicksearch = $showQuicksearch;
		$this->showPageFlip = $showPageFlip;
	}
	// </editor-fold>

	public function optionsEdit($showSave = true, $showInputs = true){
		$this->showSave = $showSave;
		$this->showInputs = $showInputs;
	}

	function getEditHTML(){
		$this->object->loadMeOrEmpty();

		if($this->object->getID() == -1)
			$this->addToEvent("onSave", "$('contentLeft').update('');");

		$F = new HTMLForm($this->formID == null ? "edit".get_class($this->object) : $this->formID, $this->attributes == null ? $this->object : $this->attributes, strpos($this->displayMode, "popup") === false ? $this->operationsButton().$this->name : null);
		$F->getTable()->setColWidth(1, 120);
		$F->getTable()->addTableClass("contentEdit");
		
		if($this->showSave)
			$F->setSaveClass(get_class($this->object), $this->object->getID(), $this->functionEntrySave, $this->name);

		$F->isEditable($this->showInputs);

		foreach($this->object->getA() AS $n => $v){
			$F->setValue($n, $v);
			$F->setLabel($n, str_replace($this->object->getClearClass(), "", $n));
		}

		foreach($this->types AS $n => $l)
			$F->setType($n, $l, null, isset($this->options[$n]) ? $this->options[$n] : null);
		
		foreach($this->labels AS $n => $l)
			$F->setLabel($n, T::_($l));

		foreach($this->descriptionsField AS $n => $l)
			$F->setDescriptionField($n, T::_($l));

		foreach($this->parsers AS $n => $l)
			$F->setType($n, "parser", null, array($l, $this->object));

		foreach($this->spaces AS $n => $l)
			$F->insertSpaceAbove($n, T::_($l));

		foreach($this->fieldButtons AS $n => $B)
			$F->addFieldButton($n, $B);

		foreach($this->fieldEvents AS $k => $v)
			$F->addJSEvent($v[0], $v[1], $v[2]);

		foreach($this->hiddenLines AS $n)
			$F->inputLineStyle($n, "display:none;");
		
		foreach($this->inputStyles AS $k => $n)
			$F->setInputStyle($k, $n);
		
		foreach($this->autocomplete AS $k => $a)
			$F->setAutoComplete($k, $a[0], $a[1]);
		
		$requestFocus = "";
		if($this->requestFocus)
			$requestFocus = OnEvent::script("setTimeout(function(){ var target1 = \$j('input[name=".$this->requestFocus[0]."]:visible, textarea[name=".$this->requestFocus[0]."]:visible'); if(target1.length > 0) target1.focus(); ".($this->requestFocus[1] != null ? "else \$j('input[name=".$this->requestFocus[1]."]:visible, textarea[name=".$this->requestFocus[1]."]:visible').focus();" : "")."}, 100);");
		
		return $this->topButtons().$this->sideButtons().$F.$requestFocus.GUIFactory::editFormOnchangeTest($this->formID == null ? "edit".get_class($this->object) : $this->formID);
	}

	/**
	 * Call getBrowserHTML() if you want a table containing all the elements of a collection-class
	 * When called with an id, only one row is returned to easily replace an old one with JavaScript
	 *
	 * @param int $lineWithId
	 */
	// <editor-fold defaultstate="collapsed" desc="getBrowserHTML">
	function getBrowserHTML($lineWithId = -1, $useBPS = true){
		T::load(Util::getRootPath()."libraries");
		
		$bps = BPS::getAllProperties("m".$this->className."GUI");
		if(!$useBPS)
			$bps = false;
		
		$GUIF = $this->GUIFactory;
		$GUIF->setMultiPageDetails($this->multiPageDetails);
		$GUIF->setTableMode($this->displayMode);
		$GUIF->options($this->showTrash, $this->showEdit, $this->showNew);

		if($this->blacklists != null)
			$GUIF->blacklists($this->blacklists);

		if(isset($bps["selectionMode"]))
			$GUIF->selection($bps["selectionMode"]);

		#$GUIF->features($this->features);

		#$this->multiPageDetails["target"] = $this->frame;#"contentRight";
		#$GUIF->setMultiPageDetails($this->multiPageDetails);

		if($this->targetFrame != null)
			$GUIF->targetFrame($this->targetFrame);
		
		$E = $this->object->getNextEntry();


		if($this->attributes == null AND $E != null)
			$this->attributes = PMReflector::getAttributesArrayAnyObject($E->getA());

		#if($E == null) //To fix display error when no entry
		#	$this->attributes = array("");
		
		if($this->caption == null AND $this->caption !== false)
			$this->caption(($this->displayMode == "BrowserLeft") ? ($this->name == null ? $this->className : $this->name) : "&nbsp;");#"Bitte ".($this->name == null ? $this->className : $this->name)." auswählen:");


		$Tab = $GUIF->getTable($E == null ? array("") : $this->attributes, $this->colStyle, $this->caption);
		$Tab->setTableID("Browserm$this->className");
		$Tab->addTableClass("contentBrowser");
		
		if($this->header != null)
			$Tab->addHeaderRow($this->header);
		
		if($lineWithId == -1) {
			if($this->showQuicksearch) $GUIF->buildQuickSearchLine();

			#if($this->multiPageDetails["total"] > $this->multiPageDetails["perPage"])
			if($this->showPageFlip)
				$GUIF->buildFlipPageLine("top");

			if($this->object->isFiltered()) $GUIF->buildFilteredWarningLine();

			$GUIF->buildNewEntryLine(($this->name == null ? $this->className : $this->name)." neu anlegen");
		}

		$this->object->resetPointer();


		$DisplayGroup = null;
		while($E = $this->object->getNextEntry()){

			/**
			 * DisplayGroup
			 */
			if($lineWithId == -1 AND $this->displayGroup != null AND $DisplayGroup != $E->A($this->displayGroup[0])){
				if($this->displayGroup[1] != null){
					$DGP = explode("::", $this->displayGroup[1]);
					$GUIF->buildGroupLine(Util::invokeStaticMethod($DGP[0], $DGP[1], array($E->A($this->displayGroup[0]), $E)));
				} else
					$GUIF->buildGroupLine($E->A($this->displayGroup[0]));
			}

			$Line = array();

			foreach($this->attributes AS $attributeName){
				$LineContent = $E->A($attributeName);

				if(isset($this->parsers[$attributeName]))
					$LineContent = $this->invokeParser($this->parsers[$attributeName], $LineContent, $E);
				else
					$LineContent = htmlspecialchars($LineContent);

				$Line[] = $LineContent;
			}


			$GUIF->buildLine($E->getID(), $Line);

			if($this->displayGroup != null)
				$DisplayGroup = $E->A($this->displayGroup[0]);
		}

		if($lineWithId == -1) {
			if($this->object->isFiltered()) $GUIF->buildFilteredWarningLine();

			if($this->multiPageDetails["total"] > $this->multiPageDetails["perPage"] AND $this->showPageFlip)
				$GUIF->buildFlipPageLine("bottom");
			
			if($this->object->numLoaded() == 0)
				$GUIF->buildNoEntriesLine();
		}
		else
			return $Tab->getHTMLForUpdate();

		return $this->topButtons($bps).$this->sideButtons($bps).$GUIF->getContainer($Tab, $this->caption).str_replace("%CLASSNAME", $this->className, $this->sortable).$this->tip;
	}
	// </editor-fold>

	public function sortable($handleClass = null){
		$this->sortable = "<script type=\"text/javascript\">
			\$j('#Browserm%CLASSNAME tbody').sortable({
				helper: function(e, ui) {
					ui.children().each(function() {
						\$j(this).width(\$j(this).width());
					});

					return ui;
				},
				update: function(){
					var newOrder = \$j(this).sortable('serialize', {expression: /([a-zA-Z]+)([0-9]+)/}).replace(/\[\]=/g, '').replace(/&/g, ';').replace(/[a-zA-Z]*/g, '');
					contentManager.rmePCR('m%CLASSNAME', '-1', 'saveOrder', newOrder);
				},
				axis: 'y'".($handleClass != null ? ",
				handle: \$j('.$handleClass')" : "")."
			}).disableSelection();
		</script>";
	}
	
	private function topButtons($bps = null){
		T::D($this->className);
		$TT = "";
		if(count($this->topButtons) > 0 AND ($bps == null OR !isset($bps["selectionMode"]))){
			$TT = new HTMLTable(1);

			foreach($this->topButtons AS $B)
				$TT->addRow($B."");
		}

		T::D("");
		return $TT;
	}

	private function sideButtons($bps = null){
		T::D($this->className);
		$ST = "";
		if(count($this->sideButtons) > 0 AND ($bps == null OR !isset($bps["selectionMode"]))){
			$position = "left";
			if($this->object instanceof PersistentObject) $position = "right";

			if($this->displayMode == "BrowserLeft")
				$position = "right";

			$ST = new HTMLSideTable($position);
			$ST->setTableID("SideTable".get_class($this->object));

			foreach($this->sideButtons AS $B)
				$ST->addRow($B."");
		}

		T::D("");
		return $ST;
	}

	// <editor-fold defaultstate="collapsed" desc="invokeParser">
	protected function invokeParser($function, $value, $element){
		$c = explode("::", $function);
		$method = new ReflectionMethod($c[0], $c[1]);
		try {
			return $method->invoke(null, $value, $element, $element); //second $element due to legacy reasons!
		} catch(ReflectionException $e){
			echo "<p>Die Methode $function existiert nicht oder ist nicht statisch!</p>";
		}
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="makeParameterStringFromArray">
	protected function makeParameterStringFromArray($array, $E){
		foreach($array AS $k => $v) {
			$v = str_replace("\$ID", $E->getID(), $v);
			if(strpos($v,"\$") !== false){
				$v = str_replace("\$", "", $v);
				$array[$k] = $E->A($v);
			} else
				$array[$k] = $v;
		}
		return implode("%§%",$array);
	}
	// </editor-fold>

	public function append($element){
		$this->appended[] = $element;
	}

	public function prepend($element){
		$this->prepended[] = $element;
	}

	public function customize($customizer){
		if($customizer == null) return;

		try {
			if($this->object == null) die("please use HTMLGUIX::object");
			$customizer->customizeGUI($this->object, $this);
		} catch (ClassNotFoundException $e){

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

	/**
	 * You may use this default version check to see if the version of the plugin matches the application's version
	 *
	 * @param string $plugin
	 */
	public function version($plugin){
		$l = $this->languageClass->getBrowserTexts();

		if(Util::versionCheck($_SESSION["applications"]->getRunningVersion(), $_SESSION["CurrentAppPlugins"]->getVersionOfPlugin($plugin) , "!=")){

			$t = new HTMLTable(1);
			$t->addRow(str_replace(array("%1","%2"),array($_SESSION["CurrentAppPlugins"]->getVersionOfPlugin($plugin), $_SESSION["applications"]->getRunningVersion()),$l["versionError"]));
			$t->addRow(Installation::getReloadButton());
			die($t->getHTML());
		}
	}

	/**
	 *  This Method activates several features. Possible values for HTMLGUIX are:
	 *
	 *  reloadOnNew
	 *  CRMEditAbove
	 *  editInPopup
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
			case "reloadOnNew":
				#if($class instanceof PersistentObject AND $class->getID() == -1)
					#$this->setJSEvent("onSave","function(transport){ contentManager.reloadOnNew(transport, '".$class->getClearClass()."'); }");
				if($class instanceof Collection)
					$this->GUIFactory->addToEvent("onNew", "contentManager.reloadFrame('contentRight');");
			break;
			case "CRMEditAbove":
				#$this->features["CRMEditAbove"] = "";
				$new = "contentManager.loadFrame('subFrameEdit%COLLECTIONNAME', '%CLASSNAME', %CLASSID, 0, '', function(transport) { if($('subFrameEdit%COLLECTIONNAME').style.display == 'none') new Effect.BlindDown('subFrameEdit%COLLECTIONNAME', {duration:0.5}); });";
				if($par1 != null)
					$new = $par1;
				
				$this->GUIFactory->replaceEvent("onNew", $new);
				$this->GUIFactory->replaceEvent("onDelete", "deleteClass('%CLASSNAME','%CLASSID', function() { contentManager.reloadFrame('contentLeft'); },'Eintrag wirklich löschen?');");
				$this->GUIFactory->replaceEvent("onEdit", $new);
				#$this->functionDelete = ;
				#$this->functionNew = ;
				#$this->functionEdit = $this->functionNew;
			break;
			case "editInPopup":
				#$new = "contentManager.editInPopup('%CLASSNAME', %CLASSID, 'Eintrag bearbeiten', ''".($par1 != null ? ", $par1" : "").");";
				#$this->GUIFactory->replaceEvent("onNew", $new);
				#$this->GUIFactory->replaceEvent("onEdit", $new);
				$this->GUIFactory->editInPopup($par1);
			break;
			
			case "addSaveDefaultButton":
				$B = new Button("als Standard-Wert speichern", "./images/i2/save.gif", "icon");
				$name = "DefaultValue".$class->getClearClass()."$par1";
				if(mb_strlen($name) > 50)
					$name = "DV".sha1($name);
				
				$B->rme("mUserdata","","setUserdata",array("'$name'","\$j('[name=$par1]').val()"),"checkResponse(transport);");
				$B->style("float:right;");
				#$this->inputStyle($par1, "width:90%;");
				#$this->buttonsNextToFields[$par1] = $B;
				$this->addFieldButton($par1, $B);
			break;
		}
	}
	 // </editor-fold>
}
?>