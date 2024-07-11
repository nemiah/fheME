<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;

class Bericht_GerichteGUI extends Bericht_default implements iBerichtDescriptor {

 	function __construct() {
 		parent::__construct();
 		
 		if(!$_SESSION["S"]->checkForPlugin("mGericht")) return;
 		
 		$anyC = new anyC();
 		$anyC->setCollectionOf("Gericht");
		$anyC->addOrderV3("GerichtName","ASC");
 		$this->collection = $anyC;
 		
 	}
 	
 	public function getLabel(){
 		if($_SESSION["S"]->checkForPlugin("mGericht")) return "Gerichte-Liste";
 		else return null;
 	}
 	
 	public function getHTML($id){
 		
 		$phtml = parent::getHTML($id);
 		
 		return $phtml;
 	}
 	
 	public function getPDF($save = false){
 		
 		$this->fieldsToShow = array("GerichtName","S1","GerichtBemerkung");
		$this->setDefaultCellHeight(5);
		$this->setColWidth("S1",60);
		$this->setColWidth("GerichtName",60);
		$this->setColWidth("GerichtBemerkung",70);
 		$this->setHeader("Gerichte-Liste vom ".date("d.m.Y"));
		$this->setType("GerichtBemerkung", "MultiCell");
		$this->setType("GerichtName", "MultiCell");
		$this->setColBorderL("S1");
		$this->setColBorderL("GerichtBemerkung");
		$this->setLabel("S1", "");
		$this->setLabel("GerichtName", "Name");
		$this->setLabel("GerichtBemerkung", "Bemerkung");
 		return parent::getPDF($save);
 	}
 } 
 ?>