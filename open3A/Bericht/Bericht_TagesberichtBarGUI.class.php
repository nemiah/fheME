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
class Bericht_TagesberichtBar extends Bericht_default {
 	public function getLabel(){
		return "Tagesbericht Barrechnungen";
 	}
	
}

class Bericht_TagesberichtBarGUI extends Bericht_RechnungsAusgangsbuchGUI implements iBerichtDescriptor {
 	
	
	function __construct() {

		if($_SESSION["applications"]->getActiveApplication() != "open3A"
			AND $_SESSION["applications"]->getActiveApplication() != "openFiBu")
			return;
		
 		if(!Session::isPluginLoaded("Auftraege") OR !Session::isPluginLoaded("mZahlungsart"))
			return;
 		
		$this->useVariables(array("useRADay"));
		
 		parent::__construct();
 		if(!isset($this->userdata["useRADay"]) OR $this->userdata["useRADay"] == ""){
			$D = new Datum();
			$D->normalize();
			$this->collection->addAssocV3("datum","=", $D->time(),"AND","3");
		}
		
		$this->collection->addAssocV3("GRLBMpayedVia","=", "cash","AND","3");
 	}
 	
 	public function getLabel(){
		if($_SESSION["applications"]->getActiveApplication() != "open3A"
			AND $_SESSION["applications"]->getActiveApplication() != "openFiBu")
			return null;
		
 		if(Session::isPluginLoaded("Auftraege") AND Session::isPluginLoaded("mZahlungsart"))
			return "Tagesbericht Barrechnungen";
 		
		return null;
 	}
	
 	public function getHTML($id){
		$B = new Bericht_TagesberichtBar();
 		$phtml = $B->getHTML($id);

		$F = new HTMLForm("BC", $this->variables, "Zeitraum");
		$F->getTable()->setColWidth(1, 120);
		
		$F->setType("useRADay", "date", $this->userdata["useRADay"]);
		$F->setDescriptionField("useRADay", "Lassen Sie das Feld leer für den aktuellen Tag");
		
		$F->setLabel("useRADay", "Tag");
		
		$F->setSaveBericht($this);
		$F->useRecentlyChanged();
		
 		return $phtml.$F;
 	}

	public function getPDF($save = false) {
		if(!isset($this->userdata["useRADay"]) OR $this->userdata["useRADay"] == "")
			$this->userdata["useRADay"] = Util::CLDateParser(time());
			
		$this->RAHeader = "Tagesbericht Barrechnungen ".$this->userdata["useRADay"];
		
		parent::getPDF($save);
	}

 } 
 ?>