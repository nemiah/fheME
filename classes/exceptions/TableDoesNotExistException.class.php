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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
class TableDoesNotExistException extends StorageException {
	private $tableName = "";
	
	function __construct($tableName = ""){
		parent::__construct();
		$this->tableName = $tableName;
		$_SESSION["messages"]->addMessage("The table ".($tableName != "" ? "($tableName) " : "")."of this plugin has not yet been set up. Please use the install-plugin.");
	}

	function getTable(){
		return $this->tableName;
	}
}
?>
