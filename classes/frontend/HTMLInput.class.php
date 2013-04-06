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
class HTMLInput {
	private $type;
	private $name;
	private $value;
	private $options;
	private $onclick = null;
	private $onchange = null;
	#private $onblur = null;
	protected $style = null;
	private $id = null;
	private $onkeyup = "";
	private $onkeydown = null;
	private $hasFocusEvent = false;
	private $isSelected = false;
	protected $isDisabled = false;
	private $isDisplayMode = false;
	private $tabindex;
	private $multiEditOptions;
	private $autocompleteBrowser = true;
	protected $onblur;
	protected $onenter;
	private $onfocus;
	private $className;
	private $requestFocus = "";
	private $autocomplete;
	private $connectTo;
	private $placeholder;
	private $callback;
	private $maxlength;
	
	public function __construct($name, $type = "text", $value = null, $options = null){
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
		$this->options = $options;
	}

	public function maxlength($length){
		$this->maxlength = $length;
	}
	
	public function autocomplete($targetClass, $onSelectionFunction = null, $hideOnSelection = false, $getACData3rdParameter = null, Button $ButtonEmptyValues = null){
		$cal = new Button("Suche", "./images/i2/details.png", "icon");
					
		$this->autocomplete = array($targetClass, $onSelectionFunction, $hideOnSelection, $getACData3rdParameter == null ? "null" : $getACData3rdParameter, $ButtonEmptyValues, $cal);
		
		return $cal;
	}
	
	public function setType($type){
		$this->type = $type;
	}
	
	public function placeholder($text){
		$this->placeholder = T::_($text);
	}

	public function setClass($className){
		$this->className = $className;
	}

	public function activateMultiEdit($targetClass, $targetClassID, $onSuccessFunction = null){
		if(strpos($onSuccessFunction, "function(") !== 0)
				$onSuccessFunction = "function(transport){ $onSuccessFunction }";
		
		$this->multiEditOptions = array($targetClass, $targetClassID, $onSuccessFunction);
	}

	public function setOptions($options, $labelField = null, $zeroEntry = "bitte auswählen", $additionalOptions = null){
		if(is_object($options) AND $options instanceof Collection AND $labelField != null){
			$this->options = array("0" => $zeroEntry);

			while($t = $options->getNextEntry())
				$this->options[$t->getID()] = $t->A($labelField);
		} else
			$this->options = $options;
		
		if($additionalOptions != null)
			foreach($additionalOptions AS $k => $v)
				$this->options[$k] = $v;
	}

	public function autocompleteBrowser($bool){
		$this->autocompleteBrowser = $bool;
	}

	public function tabindex($index){
		$this->tabindex = $index;
	}

	public function isDisplayMode($b){
		$this->isDisplayMode = $b;
	}

	public function id($id){
		$this->id = $id;
	}

	public function style($style){
		$this->style = $style;
	}

	public function onclick($function){
		$this->onclick = $function;
	}

	public function onchange($function){
		$this->onchange = $function;
	}

	public function onblur($function){
		$this->onblur = $function;
	}

	public function onfocus($function){
		$this->onfocus = $function;
	}

	public function onkeyup($function){
		$this->onkeyup .= $function;
	}

	public function onkeydown($function){
		$this->onkeydown = $function;
	}

	public function getValue(){
		return $this->value;
	}

	public function requestFocus(){
		if($this->id === null)
			$this->id = "Field".rand(100, 1000000);
		
		$this->requestFocus = "<script type=\"text/javascript\">setTimeout(function() { $('$this->id').focus(); }, 200);</script>";
	}
	
	public function onEnter($function){
		$this->onkeyup .= "if(event.keyCode == 13) { ".$function." }";
		$this->onenter = $function;
	}

	public function hasFocusEvent($bool){
		$this->hasFocusEvent = $bool;
	}

	public function isSelected($bool){
		$this->isSelected = $bool;
	}

	public function setValue($v){
		$this->value = $v;
	}

	public function getCallback(){
		return $this->callback;
	}
	
	public function isDisabled($bool){
		$this->isDisabled = $bool;
	}
	
	public function connectTo($elementID){
		$this->connectTo = $elementID;
	}

	public function  __toString() {
		#$style = "";
		if($this->type == "date" AND strpos($this->style, "width:") === false) $this->style .= "width:87%;";
		#if($this->style != null) $style = " style=\"$this->style\"";

		switch($this->type){
			case "audio":
				return "<audio controls preload=\"auto\" autobuffer style=\"$this->style\"><source src=\"$this->value\"></audio>";
			break;
		
			case "search":
				$currentId = ($this->id != null ? $this->id : $this->name.rand(100, 100000000));
				$enter = "if(\$j('#$currentId').val() != ''){ \$j('#SB$currentId').fadeOut(200, function(){ \$j('#SA$currentId').fadeIn();}); } else { \$j('#SA$currentId').fadeOut(200, function(){ \$j('#SB$currentId').fadeIn(); }); }";
				
				$I = new HTMLInput($this->name, "text", $this->value, $this->options);
				$I->style($this->style);
				$I->placeholder($this->placeholder);
				$I->onEnter($this->onenter.$enter);
				#$I->onEnter(" ");
				$I->id($currentId);
				
				$BSearch = new Button("Suchen", "question_mark", "iconicG");
				$BSearch->style("margin-left:5px;");
				$BSearch->id("SB$currentId");
				$BSearch->onclick($this->onenter.$enter);
				#$BSearch->id("searchMailsInfo");

				$BSearchClear = new Button("Suche beenden", "x_alt", "iconicR");
				$BSearchClear->style("margin-left:5px;display:none;");
				$BSearchClear->id("SA$currentId");
				$BSearchClear->onclick("\$j('#$currentId').val('').trigger('blur'); $this->onenter$enter");
				#$BSearchClear->id("searchMailsClear");

				return $I.$BSearch.$BSearchClear;
			break;
		
			case "HTMLEditor":

				$B = new Button("in HTML-Editor\nbearbeiten","editor");
				$B->windowRme("Wysiwyg","","getEditor","","WysiwygGUI;FieldClass:{$this->options[0]};FieldClassID:{$this->options[1]};FieldName:{$this->options[2]}");
				$B->className("backgroundColor2");

				return $B->__toString();
			break;
		
			case "TextEditor":
				#return "<input ".(isset($this->events[$as]) ? $eve : "")." style=\"background-image:url(./images/navi/editor.png);".(isset($this->inputStyle[$as]) ? "".$this->inputStyle[$as]."" : "")."\" type=\"button\" class=\"bigButton backgroundColor2\" onclick=\"TextEditor.show('$as','$this->FormID');\" value=\"".$this->texts["in Editor bearbeiten"]."\" /><textarea style=\"display:none;\" name=\"".$as."\" id=\"".$as."\">".$this->attributes->$as."</textarea>";
				$B = new Button("in Editor\nbearbeiten","editor");
				$B->className("backgroundColor2");
				$B->onclick("TextEditor.show('$this->name','{$this->options[0]}');");

				$ITA = new HTMLInput($this->name, "textarea", $this->value);
				$ITA->id($this->name);
				$ITA->style("display:none;");
				
				return $B->__toString().$ITA;
			break;
		
			case "nicEdit":
				$BO = array("'{$this->options[0]}'", "'{$this->options[1]}'");
				if(isset($this->options[2]))
					$BO[] = "'{$this->options[2]}'";
					
				$B = new Button("in Editor\nbearbeiten","editor");
				#$B->windowRme("Wysiwyg","","getEditor","","WysiwygGUI;FieldClass:{$this->options[0]};FieldClassID:{$this->options[1]};FieldName:{$this->options[2]}");
				$B->doBefore("Overlay.showDark(); %AFTER");
				$B->popup("", "Editor", "nicEdit", "-1", "editInPopup", $BO, "", "Popup.presets.large");
				$B->className("backgroundColor2");

				$ITA = new HTMLInput($this->name, "hidden", $this->value);
				
				return $B->__toString().$ITA;
			break;
		
			case "tinyMCE":

				$BO = array("'{$this->options[0]}'", "'{$this->options[1]}'");
				if(isset($this->options[2]))
					$BO[] = "'{$this->options[2]}'";
					
				$B = new Button("in Editor\nbearbeiten","editor");
				#$B->windowRme("Wysiwyg","","getEditor","","WysiwygGUI;FieldClass:{$this->options[0]};FieldClassID:{$this->options[1]};FieldName:{$this->options[2]}");
				$B->popup("", "Editor", "Wysiwyg", "-1", "editInPopup", $BO, "", "Popup.presets.large");
				$B->className("backgroundColor2");

				$ITA = new HTMLInput($this->name, "textarea", $this->value);
				
				return $B->__toString().$ITA;
			break;
		
			case "multiInput":
				return "<input
					class=\"multiEditInput2\"
					type=\"text\"
					".($this->style != null ? " style=\"$this->style\"" : "")."
					value=\"".htmlspecialchars($this->value)."\"
					onfocus=\"oldValue = this.value;\"
					id=\"".$this->options[2]."ID".$this->options[1]."\"
					onblur=\"if(oldValue != this.value) saveMultiEditInput('".$this->options[0]."','".$this->options[1]."','".$this->options[2]."');\"
					onkeydown=\"if(event.keyCode == 13) saveMultiEditInput('".$this->options[0]."','".$this->options[1]."','".$this->options[2]."');\"/>";
			break;

			/*case "customSelection":
				$B = new Button("Eintrag auswählen...", "gutschrift");
				$B->type("LPBig");
				$B->style("float:right;margin-left:10px;");
				#				 "contentRight"		"callingPluginID"  "selectPlugin"
				$B->customSelect($this->options[0], $this->options[1], $this->options[2], $this->options[3]);

				return $B."<input type=\"text\" name=\"$this->name\" value=\"$this->value\" />";
			break;*/

			case "textarea":
				if($this->isDisplayMode) return nl2br($this->value);

				if($this->multiEditOptions != null){
					$this->id($this->name."ID".$this->multiEditOptions[1]);
					$this->onfocus .= " contentManager.oldValue = this.value;";
					$this->onkeyup .= "if(event.keyCode == 13) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
					$this->onblur .= "if(contentManager.oldValue != this.value) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
				
					if($this->hasFocusEvent) {
						$this->onfocus .= "focusMe(this);";
						$this->onblur .= "blurMe(this);";
					}
					$this->hasFocusEvent = false;
				}

				return "<textarea
					".($this->placeholder != null ? " placeholder=\"$this->placeholder\"" : "")."
					".($this->style != null ? " style=\"$this->style\"" : "")."
					name=\"$this->name\"
					".($this->className != null ? "class=\"$this->className\"" : "")."
					".($this->onkeyup != null ? "onkeyup=\"$this->onkeyup\"" : "")."
					".($this->onblur != null ? "onblur=\"$this->onblur\"" : "")."
					".($this->onfocus != null ? "onfocus=\"$this->onfocus\"" : "")."
					".($this->onkeyup != null ? "onkeyup=\"$this->onkeyup\"" : "")."
					".($this->hasFocusEvent ? "onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\"" : "")."
					".($this->id != null ? "id=\"$this->id\"" : "").">$this->value</textarea>";
			break;

			case "file":
				$physion = Session::physion();

				$currentId = ($this->id != null ? $this->id : $this->name).rand(100, 100000000);
				
				if(isset($this->options["autoUpload"]) AND !$this->options["autoUpload"])
					$this->callback = "QQUploader$currentId.uploadStoredFiles();";
				
				return "
					<div id=\"progress_$currentId\" style=\"height:10px;width:95%;display:none;\" class=\"\">
						<div id=\"progressBar_$currentId\" style=\"height:10px;width:0%;\" class=\"backgroundColor1\"></div>
					</div>
					<div id=\"$currentId\" style=\"width:100%;$this->style\"></div>
					<script type=\"text/javascript\">
						QQUploader$currentId = new qq.FileUploader({
							maxSizePossible: '".ini_get("upload_max_filesize")."B',
							sizeLimit: ".Util::toBytes(ini_get("upload_max_filesize")).",
							element: \$j('#$currentId')[0],
							action: './interface/set.php',
							params: {
								'class': '".(($this->options == null OR !isset($this->options["class"])) ? "TempFile" : $this->options["class"])."'
								,'id':'-1'
								".(($this->options != null AND isset($this->options["path"])) ? ",'path':'".$this->options["path"]."'" : "")."
								".($physion ? ",'physion':'$physion[0]'" : "")."
							},
							".((isset($this->options["autoUpload"])) ? "autoUpload: ".($this->options["autoUpload"] ? "true" : "false")."," : "")."
							".((isset($this->options["multiple"])) ? "multiple: ".($this->options["multiple"] ? "true" : "false")."," : "")."
							onSubmit: function(id, fileName){ \$j('#progress_$currentId').css('display', 'block');},
							onComplete: function(id, fileName, transport){ \$j('progress_$currentId').css('display', 'none'); $this->onchange },
							onProgress: function(id, fileName, loaded, total){ \$j('#progressBar_$currentId').css('width', Math.ceil((loaded / total) * 100)+'%'); }});
					</script>";
			break;

			case "time":
				$this->type = "text";
				#$this->onkeyup .= "if(\$j(this).val().length == 2 && \$j(this).val().lastIndexOf(':') == -1) \$j(this).val(\$j(this).val()+':'); ";
				if($this->connectTo)
					$this->onkeyup .= "contentManager.connectedTimeInput(event, '$this->id', '$this->connectTo'); ";
				else
					$this->onkeyup .= "contentManager.timeInput(event, '$this->id'); ";
				
			case "radio1":
			case "date":
			case "email":
			case "text":
			case "hidden":
			case "submit":
			case "button":
			case "password":
			case "checkbox":
			case "readonly":
			case "fileold":
				$JS = "";
				if($this->type == "radio1")
					$this->type = "radio";
				
				if($this->type == "fileold")
					$this->type = "file";
				
				if($this->isDisplayMode) {
					if($this->type == "checkbox") return Util::catchParser($this->value);
					if($this->type == "hidden") return "";
					return $this->value."";
				}

				if($this->hasFocusEvent){
					$this->onfocus .= "focusMe(this);";
					$this->onblur .= "blurMe(this);";
				}

				$cal = "";
				$B2 = "";
				if($this->type == "date") {
					if($this->id == null) $this->id = rand(10000,90000);

					$cal = new Button("Kalender anzeigen","calendar", "iconic");
					$cal->onclick("\$j('#$this->id').focus();");
					$cal->style("float:right;");

					$JS = "<script type=\"text/javascript\">\$j('#$this->id').datepicker();</script>";
					
					$this->type = "text";
				}

				$value = "value=\"".htmlspecialchars($this->value)."\"";
				if($this->type == "checkbox") $value = $this->value == "1" ? "checked=\"checked\"" : "";

				if($this->multiEditOptions != null){
					$this->id($this->name."ID".$this->multiEditOptions[1]);
					
					if($this->type == "checkbox")
						$this->onchange = "saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
					else {
						$this->onfocus .= " oldValue = this.value;";
						$this->onkeyup .= "if(event.keyCode == 13) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
						$this->onblur .= "if(oldValue != this.value) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
					}
				}

				if($this->autocomplete != null){
					if($this->id == null)
						$this->id = $this->name;
					
					/*$this->onfocus .= " ACInputHasFocus=true; AC.start(this);";
					$this->onblur .= " ACInputHasFocus = false; AC.end(this);";
					$this->onkeyup .= " AC.update(event.keyCode, this, '".$this->autocomplete[0]."');";*/
					$this->autocompleteBrowser = false;
					
					
					$cal = $this->autocomplete[5];#new Button("Suche", "./images/i2/details.png");
					$cal->onclick("$('$this->id').style.display = ''; $('$this->id').value = ''; $('$this->id').focus();");
					if($cal->getStyle() == "")
						$cal->style("float:right;");
					
					if($this->autocomplete[4] != null){
						$B2 = $this->autocomplete[4];
						$B2->style("float:right;margin-left:5px;");
						if(strpos($this->style, "width") === false)
							$this->style .= "width:80%";
					} else {
						if(strpos($this->style, "width") === false)
							$this->style .= "width:87%";
					}
						
					
					if($this->autocomplete[1] == null){
						$cal->onclick("$('{$this->id}Display').style.display = ''; $('{$this->id}Display').value = ''; $('{$this->id}').value = ''; $('{$this->id}Display').focus();");
						
						$IN = new HTMLInput($this->name, "hidden", htmlspecialchars($this->value));
						$IN->id($this->name);
						$JS .= $IN;
						
						$this->autocomplete[1] = "function(selection){ $('$this->id').value = selection.value; $('{$this->id}Display').value = selection.label; return false; }";
						
						if($this->value != ""){
							$C = substr($this->autocomplete[0], 1)."GUI";
							$C = new $C($this->value);
							
							$value = "value=\"".htmlspecialchars($C->ACLabel())."\"";
						}
						
						$this->id.= "Display";
						
					}
					
					 $JS .= OnEvent::script("var OnSelectCallback$this->id = ".$this->autocomplete[1]."; \$j(\"input#$this->id\").autocomplete({
						source: function(request, response){ 
							 ".OnEvent::rme($this->autocomplete[0], "getACData", array("'$this->name'", "request.term", $this->autocomplete[3]), "function(transport){ response(jQuery.parseJSON(transport.responseText)); }")."
							 
						},
						select: function(event, ui) { var r = OnSelectCallback$this->id(ui.item); ".($this->autocomplete[2] ? "$('$this->id').style.display = 'none';" : "")." return r; }
					}).data(\"ui-autocomplete\")._renderItem = function( ul, item ) {
						return \$j( \"<li>\" )
							.data( \"item.ui-autocomplete\", item )
							.append( \"<a>\" + item.label + (item.description ? \"<br /><small>\"+item.description+\"</small>\" : \"\")+\"</a>\" )
							.appendTo( ul );
					};");
				}
				
				return "$B2$cal<input
					".($this->maxlength != null ? " maxlength=\"$this->maxlength\"" : "")."
					".($this->placeholder != null ? " placeholder=\"$this->placeholder\"" : "")."
					".($this->style != null ? " style=\"$this->style\"" : "")."
					".(!$this->autocompleteBrowser ? "autocomplete=\"off\"" : "")."
					".($this->className != null ? "class=\"$this->className\"" : "")."
					".($this->onclick != null ? "onclick=\"$this->onclick\"" : "")."
					".($this->onblur != null ? "onblur=\"$this->onblur\"" : "")."
					".($this->onfocus != null ? "onfocus=\"$this->onfocus\"" : "")."
					".($this->onkeyup != null ? "onkeyup=\"$this->onkeyup\"" : "")."
					".($this->onkeydown != null ? "onkeydown=\"$this->onkeydown\"" : "")."
					".($this->tabindex != null ? "tabindex=\"$this->tabindex\"" : "")."
					".($this->isSelected ? "checked=\"checked\"" : "")."
					".($this->type == "file" ? "size=\"1\"" : "")."
					".($this->type == "readonly" ? "readonly=\"readonly\"" : "")."
					name=\"$this->name\"
					".($this->isDisabled ? "disabled=\"disabled\"" : "")."
					type=\"".($this->type != "readonly" ? $this->type : "text" )."\"
					".($this->onchange != null ? "onchange=\"$this->onchange\"" : "")."
					".($this->id != null ? "id=\"$this->id\"" : "")."
					$value />$this->requestFocus$JS";
			break;

			case "option":
				return "<option".($this->style != null ? " style=\"$this->style\"" : "")." ".($this->isDisabled ? "disabled=\"disabled\"" : "")." ".($this->isSelected ? "selected=\"selected\"" : "")." value=\"$this->value\">$this->name</option>";
			break;

		
			case "radio":
				$html = "";
				foreach($this->options AS $k => $v)
					$html .= "<div style=\"margin-bottom:5px;\"><input name=\"$this->name\" value=\"$k\" ".($k == $this->value ? "checked=\"checked\"" : "")." style=\"float:left;margin-right:5px;\" type=\"radio\">$v</div>";
				
				return $html;
			break;
		
			case "select":
			case "select-multiple":
				if($this->type == "select-multiple")
					$values = trim($this->value) != "" ? explode(";:;", $this->value) : array();

				if($this->isDisplayMode) return is_object($this->options[$this->value]) ? $this->options[$this->value]->__toString() : $this->options[$this->value];

				if($this->multiEditOptions != null){
					$this->onchange("saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");");
					$this->id($this->name."ID".$this->multiEditOptions[1]);
				}

				$html = "<select ".($this->isDisabled ? "disabled=\"disabled\"" : "")." ".($this->type == "select-multiple" ? " multiple=\"multiple\"" : "")."".($this->style != null ? " style=\"$this->style\"" : "")." ".($this->onchange != null ? "onchange=\"$this->onchange\"" : "")." name=\"$this->name\" ".($this->id != null ? "id=\"$this->id\"" : "").">";

				if($this->options != null AND is_array($this->options))
					foreach($this->options AS $k => $v)
						if(!is_object($v)) {
							if($this->type == "select") $isThisIt = ($this->value == $k);
							else $isThisIt = in_array($k, $values);

							$html .= "<option ".($isThisIt ? "selected=\"selected\"" : "")." value=\"$k\">$v</option>";
						}
						else {
							if($this->value == $k OR $v->getValue() == $this->value)
								$v->isSelected(true);
							$html .= $v;
						}


				$html .= "</select>";

				return $html;
			break;
		}
		
	}
}
?>