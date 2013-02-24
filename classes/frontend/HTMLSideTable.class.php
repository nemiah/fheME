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
class HTMLSideTable extends HTMLTable  {
	function  __construct($where) {
		parent::__construct(1);
		$this->setColClass(1, "");

		switch($where){
			case "left":
				$this->setTableStyle("float:left;");
			break;
		
			case "right":
				$this->setTableStyle("float:right;");
			break;
		}
		
		$this->addTableClass("sideTable".ucfirst($where));
	}

	/**
	 * Creates a new Button and adds it to the table.
	 * Then the Button will be returned to add some more functionality
	 *
	 * @param string $label
	 * @param string $image
	 * @return Button
	 */
	function addButton($label, $image = "", $type = "bigButton"){
		$B = new Button($label, $image, $type);

		$this->addRow($B);

		return $B;
	}

	function addRow($content){
		parent::addRow($content);
		#$this->addRowClass("backgroundColor0");
		#$this->addCellStyle(1, "background-color:transparent;");
		#$this->addColStyle(1, "background-color:transparent;");
		#$this->addRowStyle("background-color:transparent;");
	}
}
?>
