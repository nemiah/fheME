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

class CategorizerGUI extends UnpersistentClass {
	private $E030 = "Das Interface iCategorizer wird von der Klasse nicht implementiert.";
	private $E031 = "Sie müssen gleiche Typen verwenden";
	private $E032 = "Der zweite Eintrag muss eine höhere Nummer besitzen als der Erste.";
	private $E033 = "Bitte wählen Sie eine Kategorie";
			
	public function getWindowHTML(){
		$bps = $this->getMyBPSData();
		
		#print_r($bps);
		$v = false;
		$b = false;
		
		$von = "Bitte ziehen Sie ein <img src=\"./images/i2/add.png\" style=\"margin-bottom:-5px;\" />-Symbol auf dieses Feld.";
		$bis = "Bitte ziehen Sie ein <img src=\"./images/i2/add.png\" style=\"margin-bottom:-5px;\" />-Symbol auf dieses Feld.";
	
		if($bps != -1 AND isset($bps["VonClass"])){
			$VonClass = $bps["VonClass"];
			$VonClass = new $VonClass($bps["VonID"]);
			
			$von = $VonClass->getCategorizerLabel();
			$v = true;
		}
		
		if($bps != -1 AND isset($bps["BisClass"])){
			$BisClass = $bps["BisClass"];
			$BisClass = new $BisClass($bps["BisID"]);
			
			$bis = $BisClass->getCategorizerLabel();
			$b = true;
		}
		
		$t = new HTMLTable(2);
		$t->addColStyle(1, "width:120px;");
		$t->addRow(array("<label>Von:</label>",$von));
		$t->addCellID(2, "droppableVon");
		$t->addCellStyle(2, "height:30px;");
		
		$t->addRow(array("<label>Bis:</label>",$bis));
		$t->addCellID(2, "droppableBis");
		$t->addCellStyle(2, "height:30px;");
		
		
		if($v AND $b){
			$K = $BisClass->getCategorizerOptions();
			$o = HTMLGUI::getOptions(array_keys($K), array_values($K));
			
			$t->addRow(array("<label>Kategorie:</label>","<select id=\"categorizeWithCategory\">$o</select>"));
			
			$t->addRow(array("<input
			type=\"button\"
			class=\"bigButton backgroundColor3\"
			value=\"Kategorisieren\"
			style=\"background-image:url(./images/navi/okCatch.png);float:right;\"
			onclick=\"rme('Categorizer','','categorize',Array($('categorizeWithCategory').value),'if(checkResponse(transport)) { Popup.close(\'Categorizer\', 0); contentManager.reloadFrameRight(); }');\" />"));
			$t->addRowColspan(1,2);
		}
		
		$html = "
		<script type=\"text/javascript\">
			Droppables.add('droppableVon', {hoverclass: 'backgroundColor1', accept: 'draggable', onDrop: function(element){ rme('Categorizer','','saveVon',Array(element.id),'Popup.update(transport, \'Categorizer\', 0);'); }});
			Droppables.add('droppableBis', {hoverclass: 'backgroundColor1', accept: 'draggable', onDrop: function(element){ rme('Categorizer','','saveBis',Array(element.id),'Popup.update(transport, \'Categorizer\', 0);'); }});
		</script>";
		
		echo $t->getHTML().$html;
	}
	
	public function saveVon($element_id){
		$bps = $this->getMyBPSData();
		
		$s = split("_",$element_id);
		
		if(strpos($s[0], "GUI") === false)
			$s[0] .= "GUI";
		
		if(!PMReflector::implementsInterface($s[0],"iCategorizer"))
			Red::errorD($this->E030);
		
		if($bps != -1 AND isset($bps["BisClass"]) AND $bps["BisClass"] != $s[0])
			Red::errorD($this->E031);
			
		if($bps != -1 AND isset($bps["BisID"]) AND $bps["BisID"] < $s[1])
			Red::errorD($this->E032);
			
		$_SESSION["BPS"]->setACProperty("VonClass",$s[0]);
		$_SESSION["BPS"]->setACProperty("VonID",$s[1]);
		
		$this->getWindowHTML();
	}
	
	public function saveBis($element_id){
		$bps = $this->getMyBPSData();
		
		$s = split("_",$element_id);
		
		if(strpos($s[0], "GUI") === false)
			$s[0] .= "GUI";
		
		if(!PMReflector::implementsInterface($s[0],"iCategorizer"))
			Red::errorD($this->E030);
		
		if($bps != -1 AND isset($bps["VonClass"]) AND $bps["VonClass"] != $s[0])
			Red::errorD($this->E031);
		
		if($bps != -1 AND isset($bps["VonID"]) AND $bps["VonID"] > $s[1])
			Red::errorD($this->E032);
			
		$_SESSION["BPS"]->setACProperty("BisClass",$s[0]);
		$_SESSION["BPS"]->setACProperty("BisID",$s[1]);
		
		$this->getWindowHTML();
	}
	
	public function categorize($KategorieID){
		$bps = $this->getMyBPSData();
		
		$_SESSION["BPS"]->unregisterClass("CategorizerGUI");
		
		if($KategorieID == 0)
			Red::errorD($this->E033);
		
		$c = "m".$bps["VonClass"];
		$c = new $c();
		
		$id = str_replace("GUI","", $bps["VonClass"]);
		
		$c->addAssocV3($id."ID",">=",$bps["VonID"]);
		$c->addAssocV3($id."ID","<=",$bps["BisID"]);
		
		$k = $bps["VonClass"];
		$k = new $k(-1);
		$k = $k->getCategorizerFieldName();
		
		$c->lCV3();
		while($t = $c->getNextEntry()) {
			$t->changeA($k, $KategorieID);
			$t->saveMe();
		}
		
		#echo "error:'test: ".$c->numLoaded()."'";
	}
}
?>