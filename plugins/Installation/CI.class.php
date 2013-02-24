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
class CI extends PersistentObject {
	private $myDBFile = "";
	private $myDBFolder = "";

	function setMyDBFile($f){
		$this->myDBFile = $f;
	}
	
	function setMyDBFolder($f){
		$this->myDBFolder = $f;
	}

	public function getA(){
		if($this->A == null) $this->loadMe();
		return $this->A;
	}
	
	function __construct($ID) {
		parent::__construct($ID);
		$this->storage = "phpFileDB";	
	}
	
	function loadAdapter(){
		parent::loadAdapter();
		$this->Adapter->setDBFolder($this->myDBFolder);
	}
}
?>
