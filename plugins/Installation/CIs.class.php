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
class CIs extends anyC {
	private $myDBFile = "";
	private $myDBFolder = "";

	function __construct() {
		$this->setCollectionOf("CI");
		$this->storage = "phpFileDB";
	}

	function setMyDBFile($f){
		$this->myDBFile = $f;
	}

	function setMyDBFolder($f){
		$this->myDBFolder = $f;
	}

	function loadAdapter(){
		parent::loadAdapter();
		$this->Adapter->setDBFile($this->myDBFile);
		if(is_file($this->myDBFolder."/CI.pfdb.php")) $this->Adapter->setDBFolder($this->myDBFolder);
		else $this->Adapter->setDBFolder(".".$this->myDBFolder);
		#$this->Adapter->setDBFile($this->myDBFile);
	}
}
?>
