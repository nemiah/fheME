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
class XMLC extends anyC {
	
	private $collection;
	private $xml;
	
	function __construct($collection = null){
		$this->collection = $collection;
		parent::__construct();
	}
	
	function loadAdapter(){
		$this->Adapter = new XML();
	}
	
	public function setXML($xml){
		$start = strpos($xml, "<?xml");
		$this->xml = substr($xml, $start);
	}
	
	public function lCV3($id = -1, $returnCollector = true){
		if($this->Adapter == null) $this->loadAdapter();

		if($this->xml == null AND $this->collection != null){
			if(is_object($this->collection)){
				$this->collection->lCV3();
				$XML = new XML();
				$XML->setCollection($this->collection);
				$this->xml = $XML->getXML();
			}
			elseif(is_string($this->collection)){
				$handle = @fopen($this->collection, "r");
				if ($handle) {
					while (!feof($handle)) 
						$this->xml .= fgets($handle, 4096);

					fclose($handle);
				} else die('error');
			}
		}
		
		$this->Adapter->setXML($this->xml);

		if($returnCollector) $this->collector = $this->Adapter->lCV4();
		else return $this->Adapter->lCV4();
	}
	
	public function getCollector(){
		return $this->collector;
	}
}
?>