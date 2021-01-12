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
class RowSizeTooLargeException extends StorageException {
	private $tableName = "";
	private $fieldName = "";
	function __construct($tableName = "", $newField = ""){
		parent::__construct();
		$this->tableName = $tableName;
		$this->fieldName = $newField;
	}

	function getTable(){
		return $this->tableName;
	}

	function getField(){
		return $this->fieldName;
	}
}
?>
