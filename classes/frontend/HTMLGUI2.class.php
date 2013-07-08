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
class HTMLGUI2 extends HTMLGUI {
	private $displaySide = "default";
	private $newColsLeft = array();
	private $newColsRight = array();
	private $isSelection = false;
	protected $multiEditModeStyle;
	protected $multiEditModeInputs;

	protected $headerRow = null;

	protected $showNewButton = true;
	protected $showEditButton = true;
	protected $showDeleteButton = true;

	public function setDisplaySide($side){
		$this->displaySide = $side;
	}

	/**
	 *  This Method activates several features. Possible values for HTMLGUI2 are:
	 *
	 *  multiEdit:
	 *  creates multiEdit-Fields for the specified attributes when using getBrowserHTML()
	 *
	 *  par1: The attributes with multiEdit-fields
	 *  par2: An array of HTMLInput-Elements to use
	 *  par3: An array of style-attributes of the multiEdit-fields
	 *
	 *  ---
	 *
	 *  displayMode:
	 *  creates multiEdit-Fields for the specified attributes when using getBrowserHTML()
	 *
	 *  par1: set true to display the new button
	 *  par2: set true to display the edit button
	 *  par3: set true to display the delete button
	 *
	 *  ---
	 *
	 * Some more features are available via the HTMLGUI-Class
	 *
	 * @param string $feature The feature to activate
	 * @param PersistentObject Collection $class
	 * @param $par1
	 * @param $par2
	 * @param $par3
	 */
	
	function activateFeature($feature, $class, $par1 = null, $par2 = null, $par3 = null){
		switch($feature){
			case "multiEdit":
				$this->multiEditMode = !is_array($par1) ? array($par1) : $par1;
				$this->multiEditModeInputs = !is_array($par2) ? array($par2) : $par2;
				$this->multiEditModeStyle = !is_array($par3) ? array($par3) : $par3;
			break;

			case "headerRow":
				$this->headerRow = !is_array($par1) ? array($par1) : $par1;
			break;

			case "displayMode":
				$this->showNewButton = $par1;
				$this->showEditButton = $par2;
				$this->showDeleteButton = $par3;
			break;

			case "reloadOnNew":
				if($class instanceof PersistentObject AND $class->getID() == -1)
					$this->setJSEvent("onSave","function(transport){ contentManager.reloadOnNew(transport, '".$class->getClearClass()."'); }");
				if($class instanceof Collection)
					$this->setJSEvent("onNew","contentManager.newClassButton('$this->singularClass', function(transport){ contentManager.reloadFrame('contentRight'); }, 'contentLeft', '".$this->singularClass."GUI;edit:ok');");

			break;

			default:
				parent::activateFeature($feature, $class, $par1, $par2, $par3);
			break;
		}
	}
	
	function setMode($mode){
		if($mode != "") {
			$m = explode(",",$mode);
			if($m[0] == "singleSelection"){
				$this->selectionFunctions = "contentManager.saveSelection('$m[1]','$m[2]','$m[3]','%%VALUE%%','".($this->displaySide == "default" ? "contentLeft" : ($this->displaySide == "left" ? "contentRight" : "contentLeft"))."'); contentManager.restoreFrame('".($this->displaySide == "default" ? "contentRight" : ($this->displaySide == "left" ? "contentLeft" : "contentRight"))."','selectionOverlay');";

				$B = new Button("Auswahl hinzufügen","./images/i2/cart.png");
				$B->onclick($this->selectionFunctions);
				$B->type("icon");
				$this->newColsLeft["select"] = $B;
				$this->isSelection = true;
			}
			if($m[0] == "multiSelection"){
				$this->selectionFunctions = "contentManager.saveSelection('$m[1]','$m[2]','$m[3]','%%VALUE%%','".($this->displaySide == "default" ? "contentLeft" : ($this->displaySide == "left" ? "contentRight" : "contentLeft"))."');";

				$B = new Button("Auswahl hinzufügen","./images/i2/cart.png");
				$B->onclick($this->selectionFunctions);
				$B->type("icon");
				$this->newColsLeft["select"] = $B;
				$this->isSelection = true;
			}
			if($m[0] == "customSelection"){
				$this->selectionFunctions = $m[1]."('$m[2]','%%VALUE%%')";

				$B = new Button("Auswahl hinzufügen","./images/i2/cart.png");
				$B->onclick($this->selectionFunctions);
				$B->type("icon");
				$this->newColsLeft["select"] = $B;
				$this->isSelection = true;
			}
		}
	}

	function getBrowserHTML($lineWithId = -1){
		$this->texts = $this->languageClass->getBrowserTexts();
		$singularLanguageClass = $this->loadLanguageClass($this->singularClass);
		$userCanDelete = mUserdata::isDisallowedTo("cantDelete".$this->singularClass);
		$userCanCreate = mUserdata::isDisallowedTo("cantCreate".$this->singularClass);
		$userHiddenFields = mUserdata::getHides($this->singularClass);

		$defaultTarget = "contentRight";
		if($this->displaySide != "default") $defaultTarget = "content".ucfirst($this->displaySide);

		if($this->singularClass == "none") {
			echo "collectionOf is not set. See message log for further details.";
			throw new CollectionOfNotSetException();
		}

		if($this->name == "Noname")
			$_SESSION["messages"]->addMessage("There is no name set. You might use setName of HTMLGUI to do that.");

	
		/**
		 * ERROR-TABLE
		 */
	
		$errorTab = new HTMLTable(1);
		if(isset($_SESSION["phynx_errors"]) AND $lineWithId == -1 AND ($_SERVER["HTTP_HOST"] == "dev.furtmeier.lan" OR strpos(__FILE__, "nemiah") !== false))
			$errorTab->addRow("
					<img style=\"float:left;margin-right:10px;\" src=\"./images/navi/warning.png\" />
					<b>Es ".(count($_SESSION["phynx_errors"]) != 1 ? "liegen" : "liegt")." ".count($_SESSION["phynx_errors"])." PHP-Fehler vor:</b><br />
					<a href=\"javascript:windowWithRme('Util','','showPHPErrors','');\">Fehler anzeigen</a>,<br />
					<a href=\"javascript:rme('Util','','deletePHPErrors','','contentManager.reloadFrameRight();');\">Fehler löschen</a>");


		/**
		 * RETURN-BUTTON
		 */
		$returnTab = new HTMLTable(1);
		if($this->isSelection){
			$BReturn = new Button("Auswahl\nbeenden","back");

			$BReturn->onclick("contentManager.restoreFrame('content".ucfirst($this->displaySide)."','selectionOverlay');");
			#return "<input type=\"button\" value=\"zurück zu\n".$p2[$s[0]]."\" style=\"background-image:url(./images/navi/back.png);\" class=\"bigButton backgroundColor3\" onclick=\"loadFrameV2('contentRight','$s[0]');\" />";
			$returnTab->addRow($BReturn);
		}


		/**
		 * DELETE-BUTTON
		 */
		if((!$this->onlyDisplayMode OR $this->deleteInDisplayMode) AND $userCanDelete AND !$this->isSelection AND $this->showDeleteButton)  $this->newColsRight["delete"]  = "
			<span class=\"iconic trash_stroke\" onclick=\"deleteClass('".$this->singularClass."','%%VALUE%%', ".($this->JSOnDelete == null ? "function() { ".($this->displaySide == "left" ? "contentManager.reloadFrameLeft();" : "contentManager.reloadFrameRight(); if(typeof lastLoadedLeft != 'undefined' && lastLoadedLeft == '%%VALUE%%') $('contentLeft').update('');")." }" : $this->JSOnDelete).",'".str_replace("%1",$this->singularName, $this->texts["%1 wirklich löschen?"])."');\"></span>";
		elseif(!$userCanDelete) $this->newColsRight["delete"] = "<img src=\"./images/i2/empty.png\" />";


		/**
		 * EDIT-BUTTON
		 */
		if(!isset($this->newColsLeft["select"]) AND (!$this->onlyDisplayMode OR $this->editInDisplayMode) AND $this->showEditButton) {
			$EB = new Button("","./images/i2/edit.png");
			$EB->type("icon");
			$EB->className("editButton");
			if($this->JSOnEdit == null) $EB->onclick("contentManager.selectRow(this); contentManager.loadFrame('contentLeft','".$this->singularClass."','%%VALUE%%','0');");
			else $EB->onclick($this->JSOnEdit);

			$this->newColsLeft["select"] = $EB;
		}
		$cols = count($this->showAttributes) + count($this->newColsLeft) + count($this->newColsRight);
		$valuesTab = new HTMLTable($cols,($lineWithId == -1 ? $this->displaySide == "left" ? $this->name : "&nbsp;"/*(!$this->onlyDisplayMode ? ($singularLanguageClass == null ? "Bitte ".$this->name." auswählen:" : $singularLanguageClass->getBrowserCaption().":") : ($singularLanguageClass == null ? $this->name : $singularLanguageClass->getPlural() ).":")*/ : null));
		$valuesTab->addTableClass("contentBrowser");
		/*if(isset($this->newColsRight["delete"]) AND ($this->displaySide == "default" OR $this->displaySide == "right"))
			$valuesTab->setColClass($cols, "backgroundColor0");
		if(isset($this->newColsRight["delete"]) AND $this->displaySide == "left")
			$valuesTab->setColClass(1, "backgroundColor0");*/

		/**
		 * QUICKSEARCH
		 */
		#$quickSearchRow = "";
		if($this->quickSearchPlugin != "" AND $lineWithId == -1){
			list($quickSearchRow, $BSearchInfo) = $this->getQuicksearchField();

			if($this->displaySide == "left"){
				
				$insertRow = array($quickSearchRow);
				for($i=1; $i<$cols-1; $i++)
					$insertRow[] = "";
				$insertRow[] = $BSearchInfo;

				$valuesTab->addRow($insertRow);
				$valuesTab->addRowColspan(1, $cols-1);
			} else {
				$valuesTab->addRow(array($BSearchInfo, $quickSearchRow));
				$valuesTab->addRowColspan(2, $cols-1);
			}
			$valuesTab->addRowClass("backgroundColorHeader");
		}

		if($this->headerRow != null)
			$valuesTab->addHeaderRow($this->headerRow);

		/**
		 * PAGE-BROWSER
		 */
		#$multiPageRow = "";
		$separator = "";
		$userDefinedEntriesPerPage = false;
		$isMultiPageMode = false;
		if(count($this->multiPageMode) > 0){
			$isMultiPageMode = true;

			$this->multiPageMode[3] = $defaultTarget;

			if($this->multiPageMode[2] == 0){
				$userDefinedEntriesPerPage = true;
				#$mU = new mUserdata();
				#$this->multiPageMode[2] = $mU->getUDValue("entriesPerPage{$this->multiPageMode[4]}");
				#if($this->multiPageMode[2] == null) $this->multiPageMode[2] = 20;
			}/*

			if($this->multiPageMode[1] == "undefined") $this->multiPageMode[1] = 0;

			$pages = ceil($this->multiPageMode[0] / $this->multiPageMode[2]);

			if($this->multiPageMode[1] != 0) $pageLinks = "<a href=\"javascript:contentManager.loadPage('$defaultTarget', '0');\">&nbsp;&lt;&lt;&nbsp;</a> ";
			else $pageLinks = "&nbsp;&lt;&lt;&nbsp; ";

			if($this->multiPageMode[1] != 0) $pageLinks .= "<a href=\"javascript:contentManager.backwardOnePage('$defaultTarget');\">&nbsp;&lt;&nbsp;</a> ";
			else $pageLinks .= "&nbsp;&lt;&nbsp; ";

			if($this->multiPageMode[1] != $pages - 1) $pageLinks .= "<a href=\"javascript:contentManager.forwardOnePage('$defaultTarget');\">&nbsp;&gt;&nbsp;</a> ";
			else $pageLinks .= "&nbsp;&gt;&nbsp; ";

			if($this->multiPageMode[1] != $pages - 1) $pageLinks .= "<a href=\"javascript:contentManager.loadPage('$defaultTarget',".($pages-1).");\">&nbsp;&gt;&gt;&nbsp;</a> | ";
			else $pageLinks .= "&nbsp;&gt;&gt;&nbsp; | ";

			$start = $this->multiPageMode[1] - 3;
			if($start < 0) $start = 0;

			$end = $this->multiPageMode[1] + 3;
			if($end > $pages - 1) $end = $pages - 1;

			for($i=$start; $i<=$end; $i++)
				if($this->multiPageMode[1] != "$i") $pageLinks .= "<a href=\"javascript:contentManager.loadPage('$defaultTarget','".$i."');\">".($i+1)."</a> ";
				else $pageLinks .= ($i+1)." ";

				$pageLinks = "".($pages == 0 ? 1 : $pages)." ".(($pages == 0 ? 1 : $pages) != 1 ? $this->texts["Seiten"] : $this->texts["Seite"]).": ".$pageLinks;
			*/

			if($this->displaySide == "left") $pageLinks = "<span style=\"float:right;\">".$this->getMultiPageButtons()."</span>";
			else $pageLinks = $this->getMultiPageButtons();
			/*if($lineWithId == -1) $multiPageRow = "
					<tr>
						".($userDefinedEntriesPerPage ? "<td><img class=\"mouseoverFade\" src=\"./images/i2/settings.png\" onclick=\"phynxContextMenu.start(this, 'HTML','multiPageSettings:{$this->multiPageMode[4]}','".$this->texts["Einstellungen"].":');\" /></td>" : "")."
						<td colspan=\"".($colspan+1+($userDefinedEntriesPerPage ? 0 : 1))."\"><input type=\"text\"onkeydown=\"if(event.keyCode == 13) loadFrameV2('".$this->multiPageMode[3]."','".$this->multiPageMode[4]."','',this.value - 1);\" style=\"width:30px;float:right;text-align:right;\" value=\"".($this->multiPageMode[1]+1)."\" onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\" />".$this->multiPageMode[0]." ".($this->multiPageMode[0] == 1 ? $this->texts["Eintrag"] : $this->texts["Einträge"])."<!--, ".$pages." ".($pages != 1 ? $this->texts["Seiten"] : $this->texts["Seite"])."--></td>
					</tr>
					<tr>
						<td colspan=\"$determinedNumberofCols\">".($pages == 0 ? 1 : $pages)." ".(($pages == 0 ? 1 : $pages) != 1 ? $this->texts["Seiten"] : $this->texts["Seite"]).": $pageLinks</td>
					</tr>";


*/
			if($lineWithId == -1) {
				$BSettings = $this->getPageOptionsButton();

				#$IPage = $this->getPageSelectionField();
				#$IPage->style("width:30px;float:right;text-align:right;");

				$pageOptions = $this->multiPageMode[0]." ".($this->multiPageMode[0] == 1 ? $this->texts["Eintrag"] : $this->texts["Einträge"]).", $pageLinks";

				if(!$userDefinedEntriesPerPage){
					$valuesTab->addRow(array($pageOptions));
					$valuesTab->addRowColspan(1, $cols);
				} else
					if($this->displaySide == "left"){
						$insertRow = array($pageOptions);
						for($i=1; $i<$cols-1; $i++)
							$insertRow[] = "";
						$insertRow[] = $BSettings;

						$valuesTab->addRow($insertRow);
						#$valuesTab->addRow(array($pageOptions,$BSettings));
						$valuesTab->addRowColspan(1, $cols-1);
					} else {
						/*$insertRow = array($BSettings);
						for($i=1; $i<$cols-1; $i++)
							$insertRow[] = "";
						$insertRow[] = $pageOptions;
						$valuesTab->addRow($insertRow);*/
						$valuesTab->addRow(array($BSettings,$pageOptions));
						$valuesTab->addRowColspan(2, $cols-1);
					}
				$valuesTab->addRowClass("backgroundColorHeader");

				#$valuesTab->addRow(array($pageLinks));
				#$valuesTab->addRowColspan(1, $cols);
				#$valuesTab->addRowClass("backgroundColorHeader");
				
				$valuesTab->addRow("");
				$valuesTab->addRowColspan(1, $cols);
				$valuesTab->addRowClass("backgroundColor0 browserSeparatorTop");

			}
		}

		$filteredCol = null;
		if($lineWithId == -1 AND $this->showFilteredCategoriesWarning != null AND $this->showFilteredCategoriesWarning[0]) {
			$dB = new Button($this->texts["Filter löschen"],"./images/i2/delete.gif");
			$dB->style("float:right;");
			$dB->type("icon");
			$dB->rme("HTML","","saveContextMenu",array("'deleteFilters'","'{$this->showFilteredCategoriesWarning[1]}'"), "if(checkResponse(transport)) contentManager.reloadFrameRight();");
			/*$separator = "
			<tr>
				<td class=\"backgroundColor0\"".((isset($this->showFilteredCategoriesWarning[0]) AND $this->showFilteredCategoriesWarning[0] == true) ? "<img src=\"./images/i2/note.png\" /></td><td class=\"backgroundColor0\" colspan=\"".($determinedNumberofCols - 2)."\" style=\"color:grey;\" >".$this->texts["Anzeige wurde gefiltert"]."</td><td class=\"backgroundColor0\">$dB</td>" : " >")."</td>
			</tr>";*/

			$filteredCol = array("<img src=\"./images/i2/note.png\" />",$dB.$this->texts["Anzeige wurde gefiltert"]);
			$valuesTab->addRow($filteredCol);
			$valuesTab->addRowColspan(2, $cols-1);
			$valuesTab->addRowClass("backgroundColor0");
			$valuesTab->addRowStyle("color:grey;");
		}
		
		/**
		 * NEW-BUTTON
		 */
		if(!$this->onlyDisplayMode /*AND $this->selectionRow == ""*/ AND $userCanCreate AND $this->showNewButton AND $lineWithId == -1){
			$BNew = new Button("","./images/i2/new.gif");
			$BNew->type("icon");
			$BNew->id("buttonNewEntry$this->singularClass");
			#$BNew->onclick($this->JSOnNew == null ? "contentManager.newClassButton('$this->singularClass','');" : $this->JSOnNew);

			if($this->displaySide == "left"){
				#$valuesTab->addRow(array("<b>$this->singularName neu anlegen</b>",$BNew));
				#$valuesTab->addRowColspan(1, $cols-1);
			} else {
				$valuesTab->addRow(array($BNew,"<b>$this->singularName neu anlegen</b>"));
				$valuesTab->addRowColspan(2, $cols-1);
				$valuesTab->addRowEvent("click", $this->JSOnNew == null ? "contentManager.newClassButton('$this->singularClass','');" : $this->JSOnNew);
				$valuesTab->addRowStyle("cursor:pointer;");
			}

			#$valuesTab->addRowColspan(2, $cols-1);
		}
		
		/**
		 * TABLE-CONTENT
		 */
		$displayGroup = null;
		for($i=0;$i < count($this->attributes);$i++){
			$aid = $this->attributes[$i]->getID(); // get the id of an object separately
			$sc = $this->attributes[$i]->getA(); // get the attributes-object from the object

			if($this->displayGroupBy != null){
				$displayGroupField = $this->displayGroupBy;
				if($sc->$displayGroupField != $displayGroup AND $i > 0) {
					$valuesTab->addRow("");
					$valuesTab->addRowClass("backgroundColor0");
				}

			}

			$row = array();
			$styles = array();

			if($this->displaySide == "left"){
				$colsLeft = $this->newColsRight;
				$colsRight = $this->newColsLeft;
			} else {
				$colsLeft = $this->newColsLeft;
				$colsRight = $this->newColsRight;
			}

			if(count($colsLeft) > 0)
				foreach($colsLeft AS $key => $value){
					$row[] = str_replace("%%VALUE%%", $aid, $value);
					$valuesTab->setColWidth(count($row), "20px");
				}
			foreach($this->showAttributes as $key => $value) {
				if(isset($userHiddenFields[$value])) continue;

				if(isset($this->parsers[$value]))
					$t = $this->invokeParser($this->parsers[$value], $sc->$value, $this->makeParameterStringFromArray($this->parserParameters[$value], $sc, $aid));
				else $t = htmlspecialchars($sc->$value);

				if($this->multiEditMode != null AND in_array($value, $this->multiEditMode)){
					$posInArray = array_search($value, $this->multiEditMode);

					if($this->multiEditModeInputs[$posInArray] == null){
						$MI = new HTMLInput($value."ID$aid", "multiInput", $sc->$value,array($this->singularClass,$aid,$value));

						if($this->multiEditModeStyle != null)
							$MI->style($this->multiEditModeStyle[$posInArray]);
					} else {
						$MI = clone $this->multiEditModeInputs[$posInArray];
						$MI->setValue($sc->$value);
						$MI->activateMultiEdit($this->singularClass, $aid);
					}

					$t = $MI;
				}

				#<td id=\"Browser".$value."$aid\" ".(isset($this->colStyles[$value]) ? "style=\"".$this->colStyles[$value]."\"" : "").">".$t."</td>";
				$row[] = $t;
				if(isset($this->colStyles[$value]))
					$styles[count($row)] = $this->colStyles[$value];
			}

			if(count($colsRight) > 0)
				foreach($colsRight AS $key => $value){
					$row[] = str_replace("%%VALUE%%", $aid, $value);
					$valuesTab->setColWidth(count($row), "20px");
				}

			$valuesTab->addRow($row);
			if(count($styles) > 0)
				foreach($styles AS $col => $s)
					$valuesTab->addColStyle($col, $s);

			$valuesTab->setRowID("BrowserMain$aid");
			#foreach($this->showAttributes as $key => $value) {
			#	$valuesTab->addCellID($cellNo, "Browser".$value."$aid");
			#}


			if($this->displayGroupBy != null){
				$displayGroup = $sc->$displayGroupField;
			}
		}

		if($filteredCol !== null){
			$valuesTab->addRow($filteredCol);
			$valuesTab->addRowColspan(2, $cols-1);
			$valuesTab->addRowClass("backgroundColor0");
			$valuesTab->addRowStyle("color:grey;");
		}
		
		if($lineWithId == -1 AND $isMultiPageMode) {
			$valuesTab->addRow("");
			$valuesTab->addRowColspan(1, $cols);
			$valuesTab->addRowClass("backgroundColor0 browserSeparatorBottom");
			
			if(!$userDefinedEntriesPerPage){
				$valuesTab->addRow(array($pageOptions));
				$valuesTab->addRowColspan(1, $cols);
			} else
				if($this->displaySide == "left"){
					$valuesTab->addRow(array($pageOptions,$BSettings));
					$valuesTab->addRowColspan(1, $cols-1);
				} else {
					$valuesTab->addRow(array($BSettings,$pageOptions));
					$valuesTab->addRowColspan(2, $cols-1);
				}
			$valuesTab->addRowClass("backgroundColorHeader");
				
			#$valuesTab->addRow(array($pageLinks));
			#$valuesTab->addRowColspan(1, $cols);
			#$valuesTab->addRowClass("backgroundColorHeader");
		}

		if(count($this->attributes) == 0){
			$valuesTab->addRow(array("keine Einträge"));
			$valuesTab->addRowColspan(1, $cols);
		}

		if($lineWithId != -1)
			$valuesTab = $valuesTab->getHTMLForUpdate();

		return $errorTab.$returnTab.$valuesTab.($lineWithId == -1 ? $this->tip : "");
	}
}
?>