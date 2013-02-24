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
class HTMLTable extends UnifiedTable implements iUnifiedTable  {
	private $colStyles = array();

	private $rowEvents = array();

	private $rowIDs = array();
	private $cellIDs = array();
	private $data = array();
	
	private $hiddenCols = array();
	private $colOrder;

	private $uID;
	
	private $forms = array();

	private $tab;
	private $insertSpaceBefore = array();
	private $tabs = array();

	private $maxHeight;
	private $tableClass = "";
	private $appendJS = "";
	private $weight = "heavy";
	
	function __construct($numCols = 0, $caption = null){
		$this->numCols = $numCols;
		$this->caption = $caption;
	}
	
	function sortable($saveTo, $handleClass){
		if($this->tableID == null)
			$this->tableID = "RNDTable".rand (1, 99999999);
		
		$this->appendJS = "\$j('#$this->tableID tbody').sortable({
				helper: function(e, ui) {
					ui.children().each(function() {
						\$j(this).width(\$j(this).width());
					});

					return ui;
				},
				update: function(){
					var newOrder = \$j(this).sortable('serialize', {expression: /([a-zA-Z]+)_([0-9]+)/}).replace(/\[\]=/g, '').replace(/&/g, ';').replace(/[a-zA-Z]*/g, '');
					contentManager.rmePCR('$saveTo', '-1', 'saveOrder', newOrder);
				},
				axis: 'y'".($handleClass != null ? ",
				handle: \$j('.$handleClass')" : "")."
			});";
	}
	
	/**
	 * @param type $weight possible values: heavy, light
	 */
	function weight($weight = "heavy"){
		$this->weight = $weight;
		$this->addTableClass("tableWeight".ucfirst($weight));
		
		for($i = 0; $i < $this->numCols; $i++)
			$this->setColClass ($i+1, "");
		
		#if($weight == "light"){
		#	$this->appendJS .= "\$j('.tableWeightLight tr').hover(function(){ \$j(this).addClass('backgroundColor2'); }, function(){ \$j(this).removeClass('backgroundColor2'); });";
		#}
	}
	
	function useForSelection($fixCols = true){
		if($fixCols){
			$this->setColWidth(1, 32);
			$this->setColClass(2, "");
		}
		
		$this->addTableClass("tableForSelection");
	}
	
	function __toString(){
		return $this->getHTML();
	}

	function maxHeight($height){
		$this->maxHeight = $height;
	}

	#function setColClass($colNumber,$class){
	#	$this->colClass[$colNumber] = $class;
	#}

	#function setCaption($caption){
	#	$this->caption = $caption;
	#}
	
	function addRowData($key, $value){
		if(!isset($this->data[count($this->content) - 1]))
			$this->data[count($this->content) - 1] = array();
		
		$this->data[count($this->content) - 1][$key] = $value;
	}
	
	function insertSpaceAbove($label = "", $tab = false, $formName = ""){
		if(count($this->insertSpaceBefore) == 0) $this->uID = rand(1000000,9000000);
		
		$this->insertSpaceBefore[count($this->content) - 1] = $label;
		$this->tabs[count($this->content) - 1] = $tab;
		$this->forms[count($this->content) - 1] = $formName;
	}
	
	function setColWidth($colNumber, $width){
		$this->colWidth[$colNumber] = $width.((strpos($width, "px") === false AND strpos($width, "%") === false) ? "px": "");
	}

	function setColClass($colNumber, $class){
		$this->colClass[$colNumber] = $class;
	}
	
	#function setTableStyle($style){
	#	$this->tableStyle = $style;
	#}
	
	function hideCol($col){
		if(is_array($col)) $this->hiddenCols = $col;
		else $this->hiddenCols[] = $col;
	}
	
	function setColOrder($order){
		if(!is_array($order)) $this->colOrder = array($order);
		else $this->colOrder = $order;
	}

	function addLV($label, $value){
		$this->addRow(array("<label>".$label."</label>",$value));
	}

	/*function addRowColspan($colNumber, $span){
		if(!isset($this->rowColspan[count($this->content) - 1])) $this->rowColspan[count($this->content) - 1] = array();
		$this->rowColspan[count($this->content) - 1][0] = $colNumber;
		$this->rowColspan[count($this->content) - 1][1] = $span;
	}*/

	function setRowColspans($colspans){
		$this->rowColspan = $colspans;
	}

	function setColRowspans($rowspans){
		$this->colRowspan = $rowspans;
	}

	/*function addCellStyle($colNumber, $style){
		if(!isset($this->cellStyles[count($this->content) - 1]))
			$this->cellStyles[count($this->content) - 1] = array();
			
		if(!isset($this->cellStyles[count($this->content) - 1][$colNumber]))
			$this->cellStyles[count($this->content) - 1][$colNumber] = array();

			
		$this->cellStyles[count($this->content) - 1][$colNumber] = $style;
	}*/

	function setCellStyles($styles){
		$this->cellStyles = $styles;
	}

	function setCellClasses($classes){
		$this->cellClasses = $classes;
	}
	
	public function setCellEvents($events){
		$this->cellEvents = $events;
	}
	
	function addRowEvent($event, $action){
		if(!isset($this->rowEvents[count($this->content) - 1])) $this->rowEvents[count($this->content) - 1] = array();
		if(!isset($this->rowEvents[count($this->content) - 1][$event])) $this->rowEvents[count($this->content) - 1][$event] = "";
		$this->rowEvents[count($this->content) - 1][$event] .= $action;
	}

	function setRowClasses($classes){
		$this->rowClasses = $classes;
	}

	function addColStyle($colNumber, $style){
		if(!isset($this->colStyles[$colNumber])) $this->colStyles[$colNumber] = $style;
		else $this->colStyles[$colNumber] .= $style;
	}

	function setColStyle($colNumber, $style){
		if(!isset($this->colStyles[$colNumber])) $this->colStyles[$colNumber] = $style;
		else $this->colStyles[$colNumber] = $style;
	}

	function setRowStyles($styles){
		$this->rowStyles = $styles;
	}

	function setRowID($id){
		$this->rowIDs[count($this->content) - 1] = $id;
	}
	
	function addCellID($cellNo, $ID){
		if(!isset($this->cellIDs[count($this->content) - 1])) $this->cellIDs[count($this->content) - 1] = array();
		
		$this->cellIDs[count($this->content) - 1][$cellNo] = $ID;
	}
	
	function addTableClass($class){
		$this->tableClass .= $class." ";
	}

	function getHTMLForUpdate($addTR = false){
		$rows = "";

		foreach($this->content as $K => $V){

			$events = "";
			if(isset($this->rowEvents[$K]))
				foreach($this->rowEvents[$K] AS $n => $a)
					$events .= "on$n=\"$a\"";

			if(isset($this->insertSpaceBefore[$K-1]) AND $this->insertSpaceBefore[$K-1] == "")
			$rows .= "
			<tr>
				<td class=\"backgroundColor0\"></td>
			</tr>";

			if(isset($this->insertSpaceBefore[$K-1]) AND $this->insertSpaceBefore[$K-1] != "" AND !$this->tabs[$K-1]) $rows .= "
			<tr>
				<td class=\"backgroundColor0\"></td>
			</tr>
			<tr>
				<td class=\"backgroundColor1\" style=\"font-weight:bold;\" colspan=\"$this->numCols\">".$this->insertSpaceBefore[$K-1]."</td>
			</tr>";

			if(isset($this->insertSpaceBefore[$K-1]) AND $this->insertSpaceBefore[$K-1] != "" AND $this->tabs[$K-1]) {
				if($this->tab > 1) $rows .= "
			</table>
			</form>
			</div>";

				$rows .= "
			<div onclick=\"if($('Tab$this->uID$this->tab').style.display == 'none') new Effect.BlindDown('Tab$this->uID$this->tab', {queue: 'end'}); else new Effect.BlindUp('Tab$this->uID$this->tab', {queue: 'end'});\" class=\"backgroundColor1 Tab borderColor1\">
				<p>".$this->insertSpaceBefore[$K-1]."</p>
			</div>
			<div id=\"Tab$this->uID$this->tab\" style=\"display:none;\">
			<form ".($this->forms[$K-1] != "" ? "id=\"".$this->forms[$K-1]."\"" : "").">
			<table ".($this->tableStyle != null ? "style=\"$this->tableStyle\"" : "").">
				<colgroup>$cols
				</colgroup>";
				$this->tab++;
			}

			$data = "";
			if(isset($this->data[$K]))
				foreach($this->data[$K] AS $key => $value)
					$data .= " data-".strtolower($key)."=\"$value\"";
			
			
			if($addTR) $rows .= "
			<tr $events $data ".(isset($this->rowIDs[$K]) ? "id=\"".$this->rowIDs[$K]."\" " : "")."".(isset($this->rowStyles[$K]) ? "style=\"".$this->rowStyles[$K]."\"" : "")." ".(isset($this->rowClasses[$K]) ? "class=\"".$this->rowClasses[$K]."\"" : "").">";

			for($l = 0; $l < $this->numCols; $l++){

				if($this->colOrder != null AND isset($this->colOrder[$l]))
					$j = $this->colOrder[$l] - 1;
				else $j = $l;

				$style = (isset($this->colStyles[$j+1]) ? $this->colStyles[$j+1] : "");
				if(isset($this->cellStyles[$K][$j+1])) $style .= $this->cellStyles[$K][$j+1];

				if($style != "") $style = "style=\"$style\"";

				$cellEvents = "";
				if(isset($this->cellEvents[$K][$j+1]))
					foreach($this->cellEvents[$K][$j+1] as $on => $ac)
						$cellEvents .= " on$on=\"$ac\"";

				$rows .= "
				<td ".(isset($this->cellClasses[$K][$j+1]) ? "class=\"".$this->cellClasses[$K][$j+1]."\"" : "")." $cellEvents ".((isset($this->cellIDs[$K]) AND isset($this->cellIDs[$K][$j+1])) ? "id=\"".$this->cellIDs[$K][$j+1]."\"" : "")." ".((isset($this->rowColspan[$K]) AND $this->rowColspan[$K][0] == $j+1) ? "colspan=\"".$this->rowColspan[$K][1]."\"" : "")." ".((isset($this->colRowspan[$K]) AND $this->colRowspan[$K][0] == $j+1) ? "rowspan=\"".$this->colRowspan[$K][1]."\"" : "")." ".$style.">".(isset($this->content[$K][$j]) ? $this->content[$K][$j] : "")."</td>";

				if(isset($this->rowColspan[$K]) AND $this->rowColspan[$K][0] == $j+1)
					$l+= $this->rowColspan[$K][1] - 1;
			}
			if($addTR) $rows .= "
			</tr>";
		}

		return $rows;
	}

	function getHTML(){
		if($this->content == null) return "";

		$cols = "";
		$rows = "";
		
		for($i = 0; $i < $this->numCols; $i++)
			$cols .= "
				<col ".(isset($this->colWidth[$i+1]) ? "style=\"width:".$this->colWidth[$i+1]."\"" : "")." class=\"".(!isset($this->colClass[$i+1]) ? "backgroundColor".($i%2 + 2)."" : $this->colClass[$i+1])."\" />";
		
		if(count($this->header) > 0){
			$rows .= "
			<thead>
			<tr>";
			
			foreach($this->header as $K => $V){
				if($this->colOrder != null AND isset($this->colOrder[$K]))
					$j = $this->colOrder[$K] - 1;
				else $j = $K;
				
				#$style = "";
				$style = (isset($this->colStyles[$j+1]) ? $this->colStyles[$j+1] : "");
				#if(isset($this->cellStyles[$K][$j+1])) $style .= $this->cellStyles[$K][$j+1];
				
				if($style != "") $style = "style=\"$style\"";
				
				$rows .= "
				<th $style>".(($this->colOrder != null AND isset($this->colOrder[$K])) ? $this->header[$this->colOrder[$K] - 1] : $V)."</th>";
			}
			$rows .= "
			</tr>
			</thead>";
		}
		
		$this->tab = 1;

		$rows .= $this->getHTMLForUpdate(true);
		$divStyle = "";
		if(preg_match("/width:([0-9 ]*)px;/", $this->tableStyle, $regs))
			$divStyle = "style=\"".$regs[0]."\"";
		
		$tabClass = "backgroundColor1 Tab";
		if($this->weight == "light")
			$tabClass = "lightTab borderColor1";
		
		$R = "
		".($this->caption != null ? "
			<div>
			<div class=\"$tabClass\" $divStyle>
				<p>".T::_($this->caption)."</p>
			</div></div>" : "");
		
		if($this->tab == 1) $R .= "
		<div ".($this->maxHeight != null ? "style=\"max-height:{$this->maxHeight}px;overflow:auto;\"" : "").">
		<table ".($this->tableStyle != null ? "style=\"$this->tableStyle\"" : "")." ".($this->tableID != null ? "id=\"$this->tableID\"" : "")." ".($this->tableClass != "" ? "class=\"$this->tableClass\"" : "").">
			<colgroup>$cols
			</colgroup>";
		
		$R .= "
			$rows
		</table>
		</div>";
		if($this->tab > 1) $R .= "
		</form>
		</div>";
		return $R.($this->appendJS != "" ? OnEvent::script($this->appendJS) : "");
	}
}
?>
