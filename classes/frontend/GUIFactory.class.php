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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class GUIFactory {

	private $mode = "HTML";
	private $className = null;
	private $collectionName = null;
	private $classID = null;
	private $table = null;
	private $numCols = null;

	private $showTrash = null;
	private $showEdit = null;
	private $showNew = null;
	private $showFlipPage = true;

	private $tableMode = null;
	private $referenceLine = null;

	private $functionNew = "contentManager.backupFrame('contentLeft','lastPage'); contentManager.newClassButton('%CLASSNAME',  function(transport){ /*ADD*/ }, 'contentLeft', '%CLASSNAMEGUI;edit:ok');";
	private $functionDelete = "deleteClass('%CLASSNAME','%CLASSID', function() { contentManager.reloadFrame('%TARGETFRAME'); /*ADD*/ },'Eintrag wirklich löschen?');";
	private $functionEdit = "contentManager.loadFrame('contentLeft','%CLASSNAME', '%CLASSID', '0');";
	private $functionSelect;
	private $functionAbort;

	private $functionPageFirst = "contentManager.loadPage('%TARGET', '0');";
	private $functionPageLast = "contentManager.loadPage('%TARGET', %PAGE);";
	private $functionPageNext = "contentManager.forwardOnePage('%TARGET');";
	private $functionPagePrevious = "contentManager.backwardOnePage('%TARGET');";
	private $functionPageSpecific = "contentManager.loadPage('%TARGET', %PAGE);";

	private $multiPageDetails;
	private $isSelection = false;
	#private $features;

	private $blacklists = array();
	private $targetFrame;
	
	function  __construct($className, $collectionName = null) {
		$this->className = $className;

		
		if($collectionName == null)
			$collectionName = "m".$className;

		$this->collectionName = $collectionName;
	}

	/*public function features($features){
		$this->features = $features;

		if(isset($this->features["CRMEditAbove"])){
		}
	}*/

	public function selection($mode){
		if($mode != "") {
			$m = explode(",",$mode);
			if($m[0] == "singleSelection"){
				$this->functionSelect = "contentManager.saveSelection('$m[1]','$m[2]','$m[3]','%CLASSID','contentLeft'); contentManager.restoreFrame('contentRight','selectionOverlay');";
				$this->functionAbort = "contentManager.restoreFrame('contentRight','selectionOverlay');";
				$this->isSelection = true;
			}
			if($m[0] == "multiSelection"){
				$this->functionSelect = "contentManager.saveSelection('$m[1]','$m[2]','$m[3]','%CLASSID','contentLeft');";
				$this->functionAbort = "contentManager.restoreFrame('contentRight','selectionOverlay');";
				$this->isSelection = true;
				/*$this->selectionFunctions = "contentManager.saveSelection('$m[1]','$m[2]','$m[3]','%%VALUE%%','".($this->displaySide == "default" ? "contentLeft" : ($this->displaySide == "left" ? "contentRight" : "contentLeft"))."');";

				$B = new Button("Auswahl hinzufügen","./images/i2/cart.png");
				$B->onclick($this->selectionFunctions);
				$B->type("icon");
				$this->newColsLeft["select"] = $B;
				$this->isSelection = true;*/
			}
			if($m[0] == "customSelection"){
				$this->functionSelect = $m[1]."('$m[2]','%CLASSID')";#"contentManager.saveSelection('$m[1]','$m[2]','$m[3]','%CLASSID','contentLeft');";
				$this->functionAbort = "contentManager.restoreFrame('contentRight','selectionOverlay');";
				if(in_array("noExitButton", $m))
					$this->functionAbort = null;
				$this->isSelection = true;

				/*$this->selectionFunctions = $m[1]."('$m[2]','%%VALUE%%')";

				$B = new Button("Auswahl hinzufügen","./images/i2/cart.png");
				$B->onclick($this->selectionFunctions);
				$B->type("icon");
				$this->newColsLeft["select"] = $B;
				$this->isSelection = true;*/
			}
		}
	}

	public function setMultiPageDetails($mpd){
		$this->multiPageDetails = $mpd;
	}

	public function addToEvent($event, $function){
		switch($event){
			case "onNew":
				$this->functionNew = str_replace("/*ADD*/", $function, $this->functionNew);
			break;
			case "onDelete":
				$this->functionDelete = str_replace("/*ADD*/", $function, $this->functionDelete);
			break;
		}
	}

	public function replaceEvent($event, $function){
		switch($event){
			case "onNew":
				$this->functionNew = $function;
			break;
			case "onDelete":
				$this->functionDelete = $function;
			break;
			case "onEdit":
				$this->functionEdit = $function;
			break;
		}
	}

	public function options($showTrash = true, $showEdit = true, $showNew = true){
		$this->showTrash = $showTrash;
		$this->showEdit = $showEdit;
		$this->showNew = $showNew;
	}

	function setCurrentID($ID){
		$this->classID = $ID;
	}

	/**
	 * creates a new button
	 *
	 * @param string $label
	 * @param string $image
	 * @return Button
	 */
	private function getButton($label, $image, $type = "bigButton"){
		if($this->mode == "HTML")
			return new Button($label, $image, $type);
	}

	private function getAbortButton(){
		if($this->functionAbort == null)
			return "";
		
		$B = new Button("Auswahl\nbeenden","stop");
		$B->onclick(str_replace(array("%CLASSNAME"),array($this->className), $this->functionAbort));
		$B->style("margin-left:10px;margin-bottom:10px;");
		$B->className("selectionAbortButton browserContainerSubHeight");
		return $B;
	}

	public function getSelectButton($onClick = null){
		$B = new Button("Auswahl hinzufügen","./images/i2/cart.png", "icon");
		$B->onclick(str_replace(array("%COLLECTIONNAME","%CLASSNAME", "%CLASSID"), array($this->collectionName,$this->className, $this->classID), $this->functionSelect));
		if($onClick != null)
			$B->onclick($onClick);
		$B->className("selectionButton");
		return $B;
	}

    public function getEditButton(){
		$B = $this->getButton("Eintrag bearbeiten", "./images/i2/edit.png", "icon");
		$B->className("editButton");
		$B->onclick("if(typeof contentManager.selectRow == 'function') contentManager.selectRow(this); ".str_replace(array("%COLLECTIONNAME","%CLASSNAME", "%CLASSID"), array($this->collectionName,$this->className, $this->classID), $this->functionEdit));

		return $B;
	}

	public function getDeleteButton(){
		$targetFrame = "contentRight";
		
		if($this->tableMode == "screen")
			$targetFrame = "contentScreen";
		
		if($this->targetFrame != null)
			$targetFrame = $this->targetFrame;
		
		$B = $this->getButton("Eintrag löschen", "trash_stroke", "iconic");
		$B->onclick(str_replace(array("%COLLECTIONNAME","%CLASSNAME", "%CLASSID", "%TARGETFRAME"), array($this->collectionName, $this->className, $this->classID, $targetFrame), $this->functionDelete));
		
		return $B;
	}

	private function getNewButton(){
		$icon = "new.gif";

		if($this->tableMode == "CRMSubframeContainer")
			$icon = "neu.gif";

		$B = $this->getButton("Eintrag erstellen", "./images/i2/$icon");
		$B->type("icon");
		$B->id("buttonNewEntry".$this->className);
		if($this->functionNew != null)
			$B->onclick(str_replace(array("%COLLECTIONNAME","%CLASSNAME", "%CLASSID"), array($this->collectionName, $this->className, -1), $this->functionNew));

		return $B;
	}

	private function getSettingsButton(){
		$B = $this->getButton("Einstellungen anzeigen", "wrench", "iconic");
		$B->onclick("phynxContextMenu.start(this, 'HTML','multiPageSettings:$this->collectionName','Einstellungen:');");

		return $B;
	}
	
	private function getQuicksearchButton(){
		$B = $this->getButton("Suche-Details anzeigen", "info", "iconic");
		#$B->type("icon");
		$B->onclick("phynxContextMenu.start(this, '$this->collectionName','searchHelp','Suche:','left');");
		$B->style("cursor: help;");

		return $B;
	}

	public function getPageBrowser(){
		if($this->multiPageDetails["total"] == null) return;
		
		if($this->targetFrame != null)
			$this->multiPageDetails["target"] = $this->targetFrame;
		
		if(!isset($this->multiPageDetails["target"]))
			$this->multiPageDetails["target"] = "";
		
		$pages = ceil($this->multiPageDetails["total"] / $this->multiPageDetails["perPage"]);

		$pageLinks = $pages." Seite".($pages != 1 ? "n" : "").": ";
		
		if($this->multiPageDetails["page"] != 0)
			$pageLinks .= "<a href=\"javascript:".str_replace(array("%TARGET","%PAGE"), array($this->multiPageDetails["target"], 0), $this->functionPageFirst)."\"><span class=\"iconic arrow_left\" style=\"border-left-width:2px;\"></span></a> ";
		else $pageLinks .= "<span class=\"iconic arrow_left inactive\" style=\"border-left-width:2px;\"></span> ";

		if($this->multiPageDetails["page"] != 0)
			$pageLinks .= "<a href=\"javascript:".str_replace(array("%TARGET","%PAGE"), array($this->multiPageDetails["target"], $this->multiPageDetails["page"] - 1), $this->functionPagePrevious)."\"><span class=\"iconic arrow_left\" style=\"margin-right:7px;\"></span></a> ";
		else $pageLinks .= "<span class=\"iconic arrow_left inactive\" style=\"margin-right:7px;\"></span> ";

		if($this->multiPageDetails["page"] != $pages - 1)
			$pageLinks .= "<a href=\"javascript:".str_replace(array("%TARGET","%PAGE"), array($this->multiPageDetails["target"], $this->multiPageDetails["page"] + 1), $this->functionPageNext)."\"><span class=\"iconic arrow_right\" style=\"margin-left:7px;\"></span></a> ";
		else $pageLinks .= "<span class=\"iconic arrow_right inactive\" style=\"margin-left:7px;\"></span> ";

		if($this->multiPageDetails["page"] != $pages - 1)
			$pageLinks .= "<a href=\"javascript:".str_replace(array("%TARGET","%PAGE"), array($this->multiPageDetails["target"], $pages - 1), $this->functionPageLast)."\"><span class=\"iconic arrow_right\" style=\"border-right-width:2px;\"></span></a> | ";
		else $pageLinks .= "<span class=\"iconic arrow_right inactive\" style=\"border-right-width:2px;\"></span> | ";

		$start = $this->multiPageDetails["page"] - 3;
		if($start < 0) $start = 0;

		$end = $this->multiPageDetails["page"] + 3;
		if($end > $pages - 1) $end = $pages - 1;

		for($i=$start; $i<=$end; $i++)
			if($this->multiPageDetails["page"] != "$i")
				$pageLinks .= "<a href=\"javascript:".str_replace(array("%TARGET","%PAGE"), array($this->multiPageDetails["target"], $i), $this->functionPageSpecific)."\">".($i+1)."</a> ";
			else $pageLinks .= ($i+1)." ";

		return $pageLinks;
	}

	public function getQuickfilterInput(){
		$do = "if(checkResponse(transport)) contentManager.reloadFrameRight();";
		if($this->tableMode == "popup")
			$do = OnEvent::reloadPopup($this->collectionName);
		
		$I = new HTMLInput("quickFilter", "text", mUserdata::getUDValueS("searchFilterInHTMLGUI".$this->collectionName));
		#$I->hasFocusEvent(true);
		$I->id("quickFilter$this->collectionName");
		$I->onEnter(OnEvent::rme(new HTMLGUI(-1), "saveContextMenu", array("'searchFilter'","'$this->collectionName;:;'+$('quickFilter$this->collectionName').value"), $do));
		#$I->onkeyup("AC.update(event.keyCode, this, '$this->collectionName','quickSearchLoadFrame');");
		$I->autocompleteBrowser(false);
		#$I->onfocus("focusMe(this); ACInputHasFocus=true; AC.start(this); if(this.value != '') AC.update('10', this, '$this->collectionName', 'quickSearchLoadFrame');");
		#$I->onblur("blurMe(this); ACInputHasFocus=false; AC.end(this);");
		$I->placeholder("Filtern");
		
		return $I;
	}

	public function getQuicksearchInput(){
		$I = new HTMLInput("quickSearch", "text", "");
		#$I->hasFocusEvent(true);
		$I->id("quickSearch$this->collectionName");
		$I->onkeyup("AC.update(event.keyCode, this, '$this->collectionName','quickSearchLoadFrame');");
		$I->autocompleteBrowser(false);
		$I->onfocus("focusMe(this); ACInputHasFocus=true; AC.start(this); if(this.value != '') AC.update('10', this, '$this->collectionName', 'quickSearchLoadFrame');");
		$I->onblur("blurMe(this); ACInputHasFocus=false; AC.end(this);");
		$I->placeholder("Suche");
		
		$B = "";
		$showSF = PMReflector::implementsInterface($this->collectionName."GUI","iSearchFilter");
		if($showSF){
			$B = new Button("Suche als Filter anwenden","./images/i2/searchFilter.png", "icon");
			$B->style("float:right;");
			$B->rmePCR("HTML","","saveContextMenu", array("'searchFilter'","'$this->collectionName;:;'+$('quickSearch$this->collectionName').value"),"if(checkResponse(transport)) contentManager.reloadFrame('contentRight', '', 0);");

			$mU = new mUserdata();
			$K = $mU->getUDValue("searchFilterInHTMLGUI".$this->collectionName);
			$I->setValue($K);
			$I->style("width:90%;");
		}
		
		return $B.$I;
	}

	/**
	 * creates a new table for your entries
	 *
	 * @param array $attributes
	 * @param array $colStyles
	 * @return HTMLTable
	 */
	public function getTable($attributes, $colStyles = null, $caption = null){
		$this->buildReferenceLine($attributes);

		if($this->mode == "HTML"){
			if($this->tableMode == "CRMSubframeContainer") $caption = null;
			if($this->tableMode == "popup") $caption = null;
			
			$T = new HTMLTable(count($this->referenceLine), $caption);

			$this->table = $T;
			
			if($this->tableMode == "CRMSubframeContainer")
				$T->setTableStyle("width:100%;margin-left:0px;");

			if($this->tableMode == "screen")
				$T->setTableStyle("font-size:10px;");
			
			if($colStyles != null)
				foreach($colStyles AS $k => $v)
					$this->setColStyle($k, $v);

			$this->numCols = count($this->referenceLine);

			if($this->showTrash) $this->setColStyle("%TRASH", "width:20px;");
			if($this->showEdit) $this->setColStyle("%EDIT", "width:20px;");

			return $T;
		}
	}

	public function getContainer($Table, $caption, $appended = "", $prepended = ""){
		$widths = Aspect::joinPoint("changeWidths", $this, __METHOD__);
		if($widths == null) $widths = array(700);

		if($this->tableMode == "CRMSubframeContainer"){
			
			$newButton = "";
			if($this->showNew) {
				$newButton = $this->getNewButton();
				$newButton->style("float:right;margin-left:10px;margin-top:-2px;");
			}

			$pageBrowser = $this->getPageBrowser();

			return "
			<div id=\"subFrameContainer$this->collectionName\" style=\"min-height:500px;\">
				$prepended
				<div style=\"width:$widths[0]px;\" class=\"backgroundColor1 Tab\">
					<p>$newButton<span style=\"float:right;font-weight:normal;\">$pageBrowser</span>$caption</p><div style=\"clear:both;\"></div>
				</div>
				<div id=\"subFrameEdit$this->collectionName\" style=\"display:none;width:$widths[0]px;padding-bottom:15px;\"></div>
				<div id=\"subFrame$this->collectionName\" style=\"width:$widths[0]px;margin-left:10px;\">
				".Aspect::joinPoint("aboveList", $this, __METHOD__)."
					<div style=\"\">
					$Table
					</div>
				".Aspect::joinPoint("belowList", $this, __METHOD__)."</div>
				$appended
			</div>";
		}

		if($this->tableMode == "BrowserRight" OR $this->tableMode == "BrowserLeft" OR $this->tableMode == "popup" OR $this->tableMode == "screen"){
			$abort = "";
			if($this->isSelection)
				$abort = $this->getAbortButton();

			return $abort.$prepended.Aspect::joinPoint("aboveList", $this, __METHOD__).$Table.$appended;
		}
	}

	// <editor-fold defaultstate="collapsed" desc="getLeftButtons">
	public function getLeftButtons(&$List){

		switch($this->tableMode){
			case "popup":
			case "screen":
			case "BrowserRight":
				if($this->classID == null AND $this->showEdit) {
					$List[] = "%EDIT";
					break;
				}

				if($this->isSelection){
					$List[] = $this->getSelectButton();
					return;
				}

				
				if($this->showEdit AND $this->classID != -1) {
					if(!isset($this->blacklists[0][$this->classID]))
						$List[] = $this->getEditButton();
					else
						$List[] = "";
				}
				

				if($this->showNew AND $this->classID == -1) $List[] = $this->getNewButton();
			break;

			case "BrowserLeft":
			case "CRMSubframeContainer":
				if($this->classID == null AND $this->showTrash) {
					$List[] = "%TRASH";
					break;
				}

				if($this->showTrash AND $this->classID != -1) $List[] = $this->getDeleteButton();
			break;
		}
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getRightButtons">
	public function getRightButtons(&$List){
		switch($this->tableMode){
			case "popup":
			case "screen":
			case "BrowserRight":
				if($this->classID == null AND $this->showTrash) {
					$List[] = "%TRASH";
					break;
				}
				if($this->showTrash AND $this->classID != -1){
					if(!isset($this->blacklists[1][$this->classID]))
						$List[] = $this->getDeleteButton();
					else
						$List[] = "";
				}
				#else $List[] = "";
			break;

			case "BrowserLeft":
			case "CRMSubframeContainer":
				if($this->classID == null AND $this->showEdit) {
					$List[] = "%EDIT";
					break;
				}

				if($this->showEdit AND $this->classID != -1) $List[] = $this->getEditButton();
				if($this->showNew AND $this->classID == -1) $List[] = $this->getNewButton();
			break;
		}
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="buildReferenceLine">
	public function buildReferenceLine($attributes){
		$r = array();

		$this->getLeftButtons($r);

		$r = array_merge($r, $attributes);

		$this->getRightButtons($r);

		$this->referenceLine = $r;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="buildGroupLine">
	public function buildGroupLine($label = ""){
		if($label === false)
			return;
		
		if($label != ""){
			$this->table->addRow($label);
			$this->table->addRowColspan(1, $this->numCols);
			$this->table->addRowClass("kategorieTeiler");
		} else {
			$this->table->addRow("");
			$this->table->addRowColspan(1, $this->numCols);
			$this->table->addRowClass("backgrounColor0");
		}
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="buildLine">
	public function buildLine($lineID, &$Line){
		$this->setCurrentID($lineID);

		$wholeLine = array();

		$this->getLeftButtons($wholeLine);

		$wholeLine = array_merge($wholeLine, $Line);

		$this->getRightButtons($wholeLine);

		$this->table->addRow($wholeLine);
		$this->table->setRowID("Browser$this->collectionName$lineID");
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="buildFlipPageLine">
	public function buildFlipPageLine($where = "top"){
		if($this->multiPageDetails["total"] == null) return;
		if(!$this->showFlipPage) return;

		#$I = new HTMLInput("targetPage", "text", $this->multiPageDetails["page"] + 1);
		#$I->onEnter("javascript:contentManager.loadPage('".$this->multiPageDetails["target"]."', this.value - 1);");
		#$I->style("width: 30px; float: right; text-align: right;");
		#$I->hasFocusEvent(true);

		$wholeLine2 = $this->getPageBrowser();
		if($where == "bottom"){
			$this->table->addRow("");
			$this->table->addRowColspan(1, count($this->referenceLine));
			$this->table->addRowClass("backgroundColor0 browserSeparatorBottom");
			$this->table->setRowPart("tfoot");
		}
		
		#if($where == "top"){# OR $where == "bottom") {
			if($this->tableMode == "BrowserRight"){
				$wholeLine1 = array($this->getSettingsButton(), "".$this->multiPageDetails["total"]." ".($this->multiPageDetails["total"] != 1 ? "Einträge" : "Eintrag").", $wholeLine2");

				$this->table->addRow($wholeLine1);
				$this->table->addRowColspan(2, count($this->referenceLine) -1 == 1 ? 2 : count($this->referenceLine) -1); //or it will look quite bad with no entries
				$this->table->addRowClass("backgroundColorHeader");
				$this->table->setRowPart($where == "top" ? "thead" : "tfoot");
				$this->table->addCellStyle(2, "text-align:left;");
			}
			
			$this->table->addRowClass("backgroundColorHeader");
			
			if($this->tableMode == "BrowserLeft" OR $this->tableMode == "screen" OR $this->tableMode == "popup"){
				$wholeLine1 = array($this->multiPageDetails["total"]." ".($this->multiPageDetails["total"] != 1 ? "Einträge" : "Eintrag").", $wholeLine2");
				$wholeLine1 = array_pad($wholeLine1, count($this->referenceLine) - 1, "");
				if($this->multiPageDetails["perPage"] === "0")
					$wholeLine1[] = $this->getSettingsButton();
				else
					$wholeLine1[] = "";
				
				$this->table->addRow($wholeLine1);
				$this->table->addRowColspan(1, count($this->referenceLine) -1 == 1 ? 2 : count($this->referenceLine) -1); //or it will look quite bad with no entries
				$this->table->addRowClass("backgroundColorHeader");
				$this->table->addCellStyle(count($wholeLine1), "text-align:right;");
				$this->table->setRowPart($where == "top" ? "thead" : "tfoot");
				
				if($this->showTrash)
					$this->setColStyle($this->referenceLine[count($this->referenceLine) - 1], "width:20px;");
			}

		#}
			
		/*if($this->multiPageDetails["total"] > $this->multiPageDetails["perPage"]){
			
			$this->table->addRow($wholeLine2);
			$this->table->addRowColspan(1, count($this->referenceLine));
			$this->table->addRowClass("backgroundColorHeader");
		}*/
		if($where == "top"){
			$this->table->addRow("");
			$this->table->addRowColspan(1, count($this->referenceLine));
			$this->table->addRowClass("backgroundColor0 browserSeparatorTop");
			$this->table->setRowPart("thead");
		}
	}
	// </editor-fold>

	public function buildNoEntriesLine(){
		$this->table->addRow(array("Keine Einträge"));
		$this->table->addRowColspan(1, count($this->referenceLine));
		$this->table->addCellStyle(1, "text-align:left;");
	}
	
	// <editor-fold defaultstate="collapsed" desc="buildNewEntryLine">
	public function buildNewEntryLine($label){
		if($this->tableMode != "BrowserRight" AND $this->tableMode != "BrowserLeft") return;
		if($this->isSelection) return;
		if(!$this->showNew) return;
		
		$newLine = array_fill(0, count($this->referenceLine), "");
		$newLine[0] = "<b>$label</b>";

		if($this->tableMode == "BrowserLeft")
			unset($newLine[count($this->referenceLine) - 1]);

		$this->buildLine(-1, $newLine);
		$this->table->setRowPart("thead");
		
		if($this->tableMode == "BrowserRight"){
			$this->table->addRowColspan(2, count($this->referenceLine) - 1);
			$this->table->addCellStyle(2, "cursor:pointer;text-align:left;padding-top:10px;padding-bottom:10px;");
			$this->table->addCellEvent(2, "click", str_replace(array("%CLASSNAME", "%CLASSID"), array($this->className, -1), $this->functionNew));
		}

		if($this->tableMode == "BrowserLeft"){
			$this->table->addRowColspan(1, count($this->referenceLine) - 1);
			$this->table->addCellStyle(1, "text-align:right;cursor:pointer;padding-bottom:10px;");
			$this->table->addCellEvent(1, "click", str_replace(array("%CLASSNAME", "%CLASSID"), array($this->className, -1), $this->functionNew));
		}
		$this->table->addRowClass("backgroundColor0");
		#
		#
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="buildQuickSearchLine">
	public function buildQuickSearchLine(){
		if($this->tableMode == "BrowserRight") {
			$wholeLine2 = array($this->getQuicksearchButton(), $this->getQuicksearchInput());
			$this->table->addRow($wholeLine2);
			$this->table->addRowColspan(2, count($this->referenceLine) - 1);
			$this->table->addRowClass("backgroundColorHeader");
			$this->table->setRowPart("thead");
			$this->table->addCellStyle(2, "text-align:left;");
			
			$this->setColStyle(1, "width:20px;");
		}
		if($this->tableMode == "BrowserLeft") {
			$wholeLine2 = array($this->getQuicksearchInput());
			$wholeLine2 = array_pad($wholeLine2, count($this->referenceLine) - 1, "");
			$wholeLine2[] = $this->getQuicksearchButton();

			$this->table->addRow($wholeLine2);
			$this->table->addRowColspan(1, count($this->referenceLine) - 1);
			$this->table->addRowClass("backgroundColorHeader");
			$this->table->setRowPart("thead");
			
			$this->setColStyle($this->referenceLine[count($this->referenceLine) - 1], "width:20px;");
		}
		
		if($this->tableMode == "popup") {
			$wholeLine2 = array($this->getQuickfilterInput());
			$wholeLine2 = array_pad($wholeLine2, count($this->referenceLine) - 1, "");
			$wholeLine2[] = $this->getQuicksearchButton();

			$this->table->addRow($wholeLine2);
			$this->table->addRowColspan(1, count($this->referenceLine) - 1);
			$this->table->addRowClass("backgroundColorHeader");
			$this->table->setRowPart("thead");
			
			$this->setColStyle($this->referenceLine[count($this->referenceLine) - 1], "width:20px;");
		}

	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="buildFilteredWarningLine">
	public function buildFilteredWarningLine($label = null){
		$dB = new Button("Filter löschen", "./images/i2/delete.gif", "icon");
		$dB->style("float:right;");
		$dB->rmePCR("HTML","","saveContextMenu", array("'deleteFilters'","'$this->collectionName'"), "if(checkResponse(transport)) contentManager.reloadFrame('contentRight');");

		$BW = new Button("", "./images/i2/note.png", "icon");

		if($this->tableMode == "popup"){
			$dB->style("");
			$dB->rmePCR("HTML","","saveContextMenu", array("'deleteFilters'","'$this->collectionName'"), OnEvent::reloadPopup($this->collectionName));
			
			$BW->style("margin-right:5px;float:left;");
			
			$wholeLine2 = array("$BW<span>Die Anzeige wurde gefiltert ".($label != null ? "nach $label" : "")."</span>");
			for($i = 1; $i < count($this->referenceLine) - 1; $i++)
				$wholeLine2[] = "";
			
			$wholeLine2[] = $dB;

			$this->table->addRow($wholeLine2);
			$this->table->addRowColspan(1, count($this->referenceLine) - 1);
			$this->table->addRowClass("highlight");
		} else {
			$wholeLine2 = array($BW, $dB."<span>Die Anzeige wurde gefiltert ".($label != null ? "nach $label" : "")."</span>");

			$this->table->addRow($wholeLine2);
			$this->table->addRowColspan(2, count($this->referenceLine) - 1);
			$this->table->addRowClass("highlight");
		}
	}
	// </editor-fold>

	public function blacklists(array $IDs){
		$this->blacklists = array(array_flip($IDs[0]), array_flip($IDs[1]));
	}

	public function editInPopup($par1 = null){
		$new = "contentManager.editInPopup('%CLASSNAME', %CLASSID, 'Eintrag bearbeiten', ''".($par1 != null ? ", $par1" : "").");";
		$this->replaceEvent("onNew", $new);
		$this->replaceEvent("onEdit", $new);
	}
	
	// <editor-fold defaultstate="collapsed" desc="setTableMode">
	public function setTableMode($TM){
		if($TM == null)
			$TM = "BrowserRight";
		
		$this->tableMode = $TM;

		if($this->tableMode == "CRMSubframeContainer"){
			$this->showFlipPage = false;

			$this->functionPageSpecific = 
				$this->functionPagePrevious = 
				$this->functionPageNext = 
				$this->functionPageLast = 
				$this->functionPageFirst = "contentManager.loadFrame('subFrameContainer$this->collectionName', '$this->collectionName', -1, %PAGE);";

		}

		if($this->tableMode == "BrowserLeft"){
			$this->multiPageDetails["target"] = "contentLeft";
			$this->addToEvent("onDelete", "contentManager.reloadFrame('contentLeft');");
		}

		if($this->tableMode == "BrowserRight")
			$this->multiPageDetails["target"] = "contentRight";
		
		if($this->tableMode == "screen")
			$this->multiPageDetails["target"] = "contentScreen";
		
		
		if($this->tableMode == "popup"){
			$this->functionPageFirst = OnEvent::reloadPopup($this->collectionName, "", "0");
			$this->functionPageLast = OnEvent::reloadPopup($this->collectionName, "", "%PAGE");
			$this->functionPageNext = OnEvent::reloadPopup($this->collectionName, "", "%PAGE");
			$this->functionPagePrevious = OnEvent::reloadPopup($this->collectionName, "", "%PAGE");
			$this->functionPageSpecific = OnEvent::reloadPopup($this->collectionName, "", "%PAGE");
		}
	}
	// </editor-fold>

	public function targetFrame($frame){
		$this->targetFrame = $frame;
	}
	
	// <editor-fold defaultstate="collapsed" desc="setColStyle">
	public function setColStyle($colName, $style){
		$col = array_search($colName, $this->referenceLine) + 1;
		if($col === false) return;
		
		$this->table->addColStyle($col, $style);
	}
	// </editor-fold>
 /*
	// <editor-fold defaultstate="collapsed" desc="blindDownSubframe">
	public function blindDownSubframe($name){
		$ud = new mUserdata();
		$ud->setUserdata("sSF$name","down");
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="blindUpSubframe">
	public function blindUpSubframe($name){
		$ud = new mUserdata();
		$ud->setUserdata("sSF$name","up");
	}
	// </editor-fold>
 */
	
	public function spellBookEntry($name, $icon, $text, $go, $add = ""){
		
		if(is_object($icon) AND $icon instanceof Button)
			$icon->style("float:left;margin-right:10px;margin-top:-7px;margin-left:-5px;");
		
		if(is_object($go) AND $go instanceof Button){
			$go->style("float:right;margin-top:-7px;");
			$go->type("icon");
		}
		return "
		<div style=\"width:33%;display:inline-block;vertical-align:top;\">
			<div style=\"margin:10px;border-radius:5px;\" class=\"borderColor1 spell\">
				<div class=\"backgroundColor2\" style=\"padding:10px;padding-bottom:5px;border-top-left-radius:5px;border-top-right-radius:5px;\">
					$go$add$icon<h2 style=\"margin-bottom:0px;width:270px;padding-top:0px;min-height:25px;\">$name</h2>
				</div>
				".($text != "" ? "<div style=\"padding:7px;height:130px;overflow:auto;\">$text</div>" : "")."
			</div>
		</div>";
	}
	
	public static function editFormOnchangeTest($FormID){
		$js = OnEvent::script("
			if(\$j('#$FormID input[name=currentSaveButton], #$FormID input[name=submitForm]').length > 0) {
				
				\$j('#$FormID input[type=text], #$FormID textarea').keydown(function(event){
					
					if(event.keyCode == 17)
						return;
						
					if(event.keyCode == 83 && event.ctrlKey)
						return;
					
					\$j(event.currentTarget).addClass('recentlyChanged'); 
					\$j('#$FormID input[name=currentSaveButton], #$FormID input[name=submitForm]').closest('tr').addClass('recentlyChanged');
				}).change(function(event){ 
					if(event.keyCode == 83 && event.ctrlKey)
						return;
						
					\$j(event.currentTarget).addClass('recentlyChanged'); 
					\$j('#$FormID input[name=currentSaveButton], #$FormID input[name=submitForm]').closest('tr').addClass('recentlyChanged');
				});

				\$j('#$FormID input[type=checkbox]').change(function(event){ 
						
					\$j(event.currentTarget).addClass('recentlyChanged'); 
					\$j('#$FormID input[name=currentSaveButton], #$FormID input[name=submitForm]').closest('tr').addClass('recentlyChanged');
				});

				\$j('#$FormID input[type=hidden]').change(function(event){ 
						
					\$j(event.currentTarget).closest('td').addClass('recentlyChanged'); 
					\$j('#$FormID input[name=currentSaveButton], #$FormID input[name=submitForm]').closest('tr').addClass('recentlyChanged');
				});

				\$j('#$FormID select').change(function(event){
					\$j(event.currentTarget).addClass('recentlyChanged');
					\$j('#$FormID input[name=currentSaveButton], #$FormID input[name=submitForm]').closest('tr').addClass('recentlyChanged');
				});
			
			}");
		
		return $js;
	}
	
	public static function filesTree($tree, $depth = 0){
		$html = "<ul style=\"list-style-type:none;".($depth > 0 ? "display:none;" : "")."\">";
		
		$folder = new Button("Datei", "folder_stroke", "iconic");
		$folder->style("color:#333;");
		
		$file = new Button("Datei", "document_alt_stroke", "iconic");
		$file->style("color:#888;");
		
		$files = array();
		foreach($tree AS $k => $content){
			
			if(is_array($content))
				$html .= "<li style=\"margin-top:0px;-moz-user-select: -moz-none;-webkit-user-select: none;\"><div class=\"folder\" style=\"cursor:pointer;margin-right:10px;padding:3px;\" onclick=\"\$j(this).parent().children('ul:first').toggle();\">".$folder." ".$k."</div>".self::filesTree($content, $depth + 1)."</li>";
			else {
				$size = mb_substr($content, mb_strrpos($content, "_") + 1);
				$name = mb_substr($content, 0, mb_strrpos($content, "_"));
				
				$files[] = $file." ".$name."<small style=\"color:#555;margin-right:30px;\"> (".Util::formatByte($size).")</small>";
			}
			
		}
		
		foreach($files AS $content){
			$html .= "<li>$content</li>";
		}
		
		$html .= "</ul>".OnEvent::script("\$j('.folder').hover(function(){ \$j(this).css('background-color', '#999'); }, function(){ \$j(this).css('background-color', ''); });");
		
		return $html;
	}
}
?>
