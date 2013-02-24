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

class mGenericGUI extends anyC implements iGUIHTMLMP2 {
	public function __construct($id, $collectionOf){
		parent::__construct();
		$this->setCollectionOf(substr($collectionOf,1));
	}
	
	public function getHTML($id, $page){
		$collectionGUI = $_SESSION["CurrentAppPlugins"]->getCollectionGUI($this->collectionOf);
		
		$gesamt = $this->loadMultiPageMode($id, $page, 0);
		
		$gui = new HTMLGUI();
		$gui->setMultiPageMode($gesamt, $page, 0, 'contentRight', "m".$this->collectionOf);
		$gui->setName($this->collectionOf);
		$gui->setAttributes($this->collector);
		$gui->setCollectionOf($this->collectionOf);
		
		if(isset($collectionGUI["showAttributes"]) AND count($collectionGUI["showAttributes"]) > 0)
			$gui->setShowAttributes($collectionGUI["showAttributes"]);
		
		if(isset($collectionGUI["colWidth"]))
			foreach($collectionGUI["colWidth"] AS $k => $v)
				$gui->setColWidth($k, $v);
				
		if(isset($collectionGUI["rowStyle"]))
			foreach($collectionGUI["rowStyle"] AS $k => $v)
				$gui->addColStyle($k, $v);
		
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
	
}
?>