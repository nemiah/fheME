<?php
/**
 *  This file is part of Demo.

 *  Demo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  Demo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
use Tinkerforge\IPConnection;
use Tinkerforge\BrickletTemperatureIR;

class TinkerforgeGUI extends Tinkerforge implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Tinkerforge");
	
		#$B = $gui->addSideButton("Master", "new");
		#$B->popup("", "Master", "Tinkerforge", $this->getID(), "readMaster", "", "", "{width:820}");
		
		$B = $gui->addSideButton("Bricklets", "new");
		$B->popup("", "Bricklets", "Tinkerforge", $this->getID(), "bricklets");
		
		
		
		return $gui->getEditHTML();
	}
	
	public function bricklets(){
		$BA = new Button("Bricklet\nhinzufügen", "new");
		$BA->doBefore("\$j('#editAP').fadeOut(400, function(){ \$j('#editDetailsTinkerforge').animate({'width':'400px'}, 200, 'swing', function(){ %AFTER }); });");
		$BA->rmePCR("Tinkerforge", $this->getID(), "createNew","", OnEvent::reloadPopup("Tinkerforge"));
		$BA->style("margin:10px;");

		$TE = new HTMLTable(4, "Bricklets");
		$TE->setColWidth(1, 20);
		$TE->setColWidth(2, 35);
		$TE->setColWidth(4, 20);
		$TE->useForSelection(false);
		$TE->maxHeight(400);
		$TE->weight("light");
		
		$BE = new Button("Eintrag bearbeiten", "arrow_right", "iconic");
		
		$autoLoad = false;
		$AC = anyC::get("TinkerforgeBricklet", "TinkerforgeBrickletTinkerforgeID", $this->getID());
		while($A = $AC->getNextEntry()){
			
			$B = new Button("Master", "bars", "iconicL");
			$B->popup("", "Plot", "TinkerforgeBricklet", $A->getID(), "getControls", "", "", "{width:820}");
			$B->style("float:right;");
			
			$BD = new Button("Eintrag löschen", "trash_stroke", "iconic");
			$BD->doBefore("\$j('#editAP').fadeOut(400, function(){ \$j('#editDetailsTinkerforge').animate({'width':'400px'}, 200, 'swing', function(){ %AFTER }); });");
			$BD->onclick("deleteClass('TinkerforgeBricklet','".$A->getID()."', function() { Popup.refresh('Tinkerforge'); },'Eintrag wirklich löschen?');");

			if($A->A("TinkerforgeBrickletUID") == "")
				$autoLoad = $A->getID();
			
			
			$div = "<span id=\"TinkerforgeBrickletUID".$A->getID()."\">".($A->A("TinkerforgeBrickletUID") != "" ? $A->A("TinkerforgeBrickletUID") : "Neues Bricklet")."</span>&nbsp;<br />
					<small style=\"color:grey;\" id=\"TinkerforgeBrickletType".$A->getID()."\">".($A->A("TinkerforgeBrickletType") != "" ? TinkerforgeBricklet::$types[$A->A("TinkerforgeBrickletType")] : "")."</small>";
			
			$TE->addRow(array($BD, $B, $div, $BE));
			$TE->addCellEvent(3, "click", "contentManager.selectRow(this); \$j('#editDetailsTinkerforge').animate({'width':'800px'}, 200, 'swing', function(){ ".OnEvent::frame("editAP", "TinkerforgeBricklet", $A->getID(), "0", "function(){ \$j('#editAP').fadeIn(); }")." });");

		}
		
		if($AC->numLoaded() == 0){
			$TE->addRow("Keine Bricklets eingetragen");
			$TE->addRowColspan(1, 3);
		}
		
		echo "$BA
			<div style=\"float:right;width:400px;height:500px;display:none;\" id=\"editAP\"></div>
			<div id=\"listAP\" style=\"width:400px;height:440px;overflow:auto;\">$TE</div>
			<div style=\"clear:both;\"></div>
			".($autoLoad ? OnEvent::script("\$j('#TinkerforgeBrickletUID".$autoLoad."').parent().trigger(Touch.trigger);") : "");
		
	}

	public function createNew(){
		$F = new Factory("TinkerforgeBricklet");

		$F->sA("TinkerforgeBrickletTinkerforgeID", $this->getID());

		$F->store();

		echo $this->bricklets();
	}
	
	public function readTemperature(){
		require_once(__DIR__.'/lib/IPConnection.php');
		require_once(__DIR__.'/lib/BrickletTemperatureIR.php');


		$host = $this->A("TinkerforgeServerIP");
		$port = 4223;

		$ipcon = new IPConnection($host, $port);
		
		$t = new BrickletTemperatureIR("9nA");

		$ipcon->addDevice($t);
		echo json_encode(array(array(time() * 1000, $t->getAmbientTemperature() / 10.0), array(time() * 1000, $t->getObjectTemperature() / 10.0)));
		
		$ipcon->destroy();
	}
}
?>