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
class UnifiedTable implements iUnifiedTable {
	protected $content = array();
	protected $caption = null;
	protected $numCols = 0;
	protected $header = null;

	protected $colWidth = array();
	protected $colClass = array();
	protected $colAlign = array();
	protected $colRowspan = array();
	
	protected $cellEvents = array();
	protected $cellStyles = array();
	protected $cellClasses = array();
	
	protected $rowStyles = array();
	protected $rowClasses = array();
	protected $rowColspan = array();

	protected $tableStyle;
	protected $tableID;
	protected $CSVNewline = "\n";
	
	function __construct($numCols = 0, $caption = null){
		$this->caption = $caption;
		$this->numCols = $numCols;
	}

	function setCSVNewline($newline){
		$this->CSVNewline = $newline;
	}
	
	function addCellEvent($colNumber, $event, $action){
		if(!isset($this->cellEvents[count($this->content) - 1]))
			$this->cellEvents[count($this->content) - 1] = array();

		if(!isset($this->cellEvents[count($this->content) - 1][$colNumber]))
			$this->cellEvents[count($this->content) - 1][$colNumber] = array();

		if(!isset($this->cellEvents[count($this->content) - 1][$colNumber][$event]))
			$this->cellEvents[count($this->content) - 1][$colNumber][$event] = "";


		$this->cellEvents[count($this->content) - 1][$colNumber][$event] = $action;
	}

	function addCellStyle($colNumber, $style){
		if(!isset($this->cellStyles[count($this->content) - 1]))
			$this->cellStyles[count($this->content) - 1] = array();

		if(!isset($this->cellStyles[count($this->content) - 1][$colNumber]))
			$this->cellStyles[count($this->content) - 1][$colNumber] = "";


		$this->cellStyles[count($this->content) - 1][$colNumber] .= $style;
	}

	function addCellClass($colNumber, $class){
		if(!isset($this->cellClasses[count($this->content) - 1]))
			$this->cellClasses[count($this->content) - 1] = array();

		if(!isset($this->cellClasses[count($this->content) - 1][$colNumber]))
			$this->cellClasses[count($this->content) - 1][$colNumber] = array();


		$this->cellClasses[count($this->content) - 1][$colNumber] = $class;
	}

	function addRowStyle($style){
		if(!isset($this->rowStyles[count($this->content) - 1])) $this->rowStyles[count($this->content) - 1] = $style;
		else $this->rowStyles[count($this->content) - 1] .= $style;
	}

	function addRowClass($class){
		if(!isset($this->rowClasses[count($this->content) - 1])) $this->rowClasses[count($this->content) - 1] = "";
		$this->rowClasses[count($this->content) - 1] .= " ".$class;
	}

	function addRowColspan($colNumber, $span){
		if(!isset($this->rowColspan[count($this->content) - 1])) $this->rowColspan[count($this->content) - 1] = array();
		$this->rowColspan[count($this->content) - 1][0] = $colNumber;
		$this->rowColspan[count($this->content) - 1][1] = $span;
	}

	function addColRowspan($colNumber, $span){
		if(!isset($this->colRowspan[count($this->content) - 1])) $this->colRowspan[count($this->content) - 1] = array();
		$this->colRowspan[count($this->content) - 1][0] = $colNumber;
		$this->colRowspan[count($this->content) - 1][1] = $span;
	}

	function setTableStyle($style){
		$this->tableStyle = $style;
	}

	function setTableID($id){
		$this->tableID = $id;
	}

	public function setColWidth($colNumber, $width){
		$this->colWidth[$colNumber] = $width;
	}

	function setColClass($colNumber, $class){
		$this->colClass[$colNumber] = $class;
	}

	function setColAlign($colNumber, $align){
		$this->colAlign[$colNumber] = $align;
	}

	function setCaption($caption){
		$this->caption = $caption;
	}

	function addRow($content){
		if(!is_array($content) AND ($content instanceof stdClass OR $content instanceof Attributes)) {
			$c = array();
			foreach($content AS $k => $v)
				$c[] = $content->$k;

			$content = $c;
		}

		if(!is_array($content)) $content = array($content);
		$this->content[] = $content;
	}

	function addRowTop($content){
		if(!is_array($content)) $content = array($content);

		$this->content = array_merge(array($content), $this->content);
	}

	function addHeaderRow($content){
		if(!is_array($content)) $content = array($content);
		$this->header = $content;
	}

	function makeTo($mode){
		$E = new $mode($this->numCols, $this->caption);

		foreach($this->content AS $k => $v) {
			unset($this->content[$k]);
			$E->addRow($v);
		}

		foreach($this->colWidth AS $k => $v) $E->setColWidth($k, $v);
		foreach($this->colClass AS $k => $v) $E->setColClass($k, $v);
		if($this->header != null) $E->addHeaderRow($this->header);

		if($E instanceof ExcelExport){
			foreach($this->colAlign AS $k => $v)
				$E->setColClass($k, $v);

			return $E;
		}

		if($E instanceof HTMLTable){
			$E->setCellEvents($this->cellEvents);
			$E->setCellStyles($this->cellStyles);
			$E->setCellClasses($this->cellClasses);
			$E->setRowStyles($this->rowStyles);
			$E->setRowClasses($this->rowClasses);
			$E->setTableStyle($this->tableStyle);
			$E->setRowColspans($this->rowColspan);
			$E->setColRowspans($this->colRowspan);
			foreach($this->colAlign AS $k => $v)
				$E->addColStyle($k, "text-align:$v;");
			return $E;
		}

		if($E instanceof CSVExport){
			$E->setCSVNewline($this->CSVNewline);
			return $E;
		}
		/*switch($mode){
			case "ExcelExport":
				foreach($this->colAlign AS $k => $v)
					$E->setColClass($k, $v);

				return $E;
			break;

			case "HTMLTable":
			break;

			case "CSVExport":
				return $E;#->getExport($filename);
			break;
		}*/
	}

	function getAs($mode, $filename = null){
		$E = $this->makeTo($mode);

		if($E instanceof ExcelExport)
			$E->getExport($filename);
		
		if($E instanceof HTMLTable)
			return $E;
		
		if($E instanceof CSVExport)
			return $E->getExport($filename);
		
	}
}
?>
