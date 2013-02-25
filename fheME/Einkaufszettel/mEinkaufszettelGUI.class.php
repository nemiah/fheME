<?php
/**
 *  This file is part of wasGibtsMorgen.

 *  wasGibtsMorgen is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  wasGibtsMorgen is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007, 2008, 2009, 2010, 2011, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mEinkaufszettelGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->addOrderV3("EinkaufszettelTime", "DESC");
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mEinkaufszettel");
		$gui->colWidth("EinkaufszettelBought", 20);
		
		$gui->name("Einkaufszettel");
		$gui->parser("EinkaufszettelBought", "Util::catchParser");
		$gui->attributes(array("EinkaufszettelBought", "EinkaufszettelName"));
		
		$B = $gui->addSideButton("EAN\nprüfen", "lieferschein");
		$B->popup("", "EAN prüfen", "mEinkaufszettel", "-1", "checkEANPopup");
		
		return $gui->getBrowserHTML($id);
	}
	
	public function checkEANPopup(){
		$F = new HTMLInput("EAN", "text", "");
		$F->onEnter(OnEvent::rme($this, "checkEAN", array("this.value"), "function(transport){ \$j('#EANResult').html(transport.responseText); }"));
		echo $F."<pre id=\"EANResult\"></pre>";
	}
	
	public function checkEAN($EAN){
		$OEAN = new mopenEANGUI();
		$artikel = $OEAN->startSeach($EAN);
		
		print_r($artikel);
	}
	
	public function addEAN($EAN, $echo = true){
		$OEAN = new mopenEANGUI();
		$artikel = $OEAN->startSeach($EAN);
		
		$fullname = "";
		$name = "";
		if(isset($artikel["fullname"]))
			$fullname = $name = $artikel["fullname"];
		
		if(isset($artikel["name"]) AND $artikel["name"] != "")
			$name = $artikel["name"];
		
		if($name != ""){
			$F = new Factory("Einkaufszettel");
			$F->sA("EinkaufszettelName", $name);
			$F->sA("EinkaufszettelBought", "0");
			if(!$F->exists()){
				$F->sA("EinkaufszettelEAN", $EAN);
				$F->sA("EinkaufszettelTime", time());
				if($fullname != $name)
					$F->sA("EinkaufszettelNameDetails", $fullname);
				$F->store();
			}
		}
		
		if($echo)
			echo $this->getOverviewListEntry($name, time());
	}
	
	public function addItem($name){
		$F = new Factory("Einkaufszettel");
		$F->sA("EinkaufszettelName", $name);
		$F->sA("EinkaufszettelTime", time());
		$F->store();
		
		echo $this->getListTable();
	}
	
	public function getOverviewListEntry($name, $time){
		return "<div style=\"padding:3px;\"><small style=\"float:right;color:grey;\">".Util::CLDateParser($time)."</small>".($name != "" ? $name : "Artikel $EAN nicht gefunden!")."</div>";
	}

	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemEinkaufszettelGUI\">asd</span><p>Einkaufen</p></div>
			<div style=\"padding:10px;\">";

		$B = new Button("Aktuelle Liste anzeigen", "./fheME/Einkaufszettel/Einkaufszettel.png", "icon");
		$B->popup("", "Einkaufsliste", "mEinkaufszettel", "-1", "showCurrentList", "", "", "{top:20, hPosition:'right'}");
		$B->style("float:right;margin-left:10px;");
		#<div style=\"border-bottom-width:1px;border-bottom-style:dotted;margin-right:45px;height:20px;margin-bottom:5px;\" id=\"EinkaufszettelInput\" class=\"borderColor1\"></div>
		$html .= "$B";#<small style=\"color:grey;\">Zuletzt hinzugefügt:</small>
		$html .= "<div id=\"EinkaufszettelLastAdded\" style=\"margin-right:40px;\"><div class=\"emptyElement\" style=\"padding:3px;\">";
		
		$AC = anyC::get("Einkaufszettel", "EinkaufszettelBought", "0");
		$AC->addOrderV3("EinkaufszettelTime", "DESC");
		$AC->setLimitV3("5");
		while($E = $AC->getNextEntry())
			$html .= $this->getOverviewListEntry($E->A("EinkaufszettelName"), $E->A("EinkaufszettelTime"));
		
		if($AC->numLoaded() == 0)
			$html .= "<span style=\"color:grey;\">Der Einkaufszettel ist leer</span></div>";
		$html .= "</div></div>";
		echo $html;
	}
	
	public function showCurrentList(){
		
		$B = new Button("Liste schließen", "stop", "icon");
		$B->onclick(OnEvent::closePopup("mEinkaufszettel"));
		$B->style("float:right;margin:10px;");
		
		$BM = new Button("Handy-Version anzeigen", "./fheME/Einkaufszettel/mobile.png", "icon");
		$BM->style("float:right;margin:10px;");
		$BM->onclick("window.open('".str_replace("/interface/rme.php", "/ubiquitous/CustomerPage/?CC=Shopping&key=".substr(Util::eK(), 0, 5), $_SERVER["SCRIPT_NAME"])."');");
		
		
		#$BH = new Button("Hinzufügen", "bestaetigung", "icon");
		#$BH->style("margin-left:10px;margin-top:10px;float:left;margin-top:-28px;");
		#$BH->onclick("if(\$j('input[name=EinkaufslisteNewEntry]').val() != 'Neuer Eintrag') ".OnEvent::rme($this, "addItem", array("\$j('input[name=EinkaufslisteNewEntry]').val()"), OnEvent::reloadPopup("mEinkaufszettel")));
		
		
		$I = new HTMLInput("EinkaufslisteNewEntry", "textarea", "Neuer Eintrag");
		$I->style("width:250px;padding:5px;margin-left:5px;font-size:20px;color:grey;float:left;font-family:monospace;max-width:250px;height:25px;max-height:25px;margin-top:-35px;");
		$I->onfocus("if(this.value == 'Neuer Eintrag') { \$j(this).val('').css('color', 'black'); }");
		$I->onblur("if(this.value == '') { \$j(this).val('Neuer Eintrag').css('color', 'grey'); }");
		$I->onkeyup("var currentContent = \$j(this).val(); ".OnEvent::rme($this, "getACData", array("this.value"), "function(transport){ var json = jQuery.parseJSON(transport.responseText); if(json.length >= 1) \$j('#EinkaufslisteNewEntryAC').html(json[0].EinkaufszettelName.replace(currentContent, '<span style=\'color:white;\'>'+currentContent+'</span>')); else \$j('#EinkaufslisteNewEntryAC').html(''); }"));
		$I->onEnter(OnEvent::rme($this, "addItem", array("this.value"), "function(transport){ \$j('#currentList').html(transport.responseText); }")." \$j(this).val('');");
		
		
		echo $B.$BM."<div id=\"EinkaufslisteNewEntryAC\" style=\"width:250px;height:25px;padding:5px;font-size:20px;margin-left:6px;margin-top:3px;font-family:monospace;color:grey;float:left;\"></div>".$I."<div style=\"clear:both;\"></div><div id=\"currentList\">".$this->getListTable()."</div>".OnEvent::script("setTimeout(function(){ \$j('input[name=EinkaufslisteNewEntry]').focus(); }, 200);");
	}
	
	public function getACData($query){
		if($query == "")
			die("[]");
		
		$AC = anyC::get("Einkaufszettel");
		$AC->addAssocV3("EinkaufszettelName", "LIKE", "$query%");
		$AC->setLimitV3(1);
		
		echo $AC->asJSON();
	}
	
	public function getListTable(){
		$T = new HTMLTable(2, "Einkaufsliste");
		$T->maxHeight(400);
		$T->setColWidth(2, 30);
		
		$AC = anyC::get("Einkaufszettel", "EinkaufszettelBought", "0");
		$AC->addOrderV3("EinkaufszettelTime", "DESC");
		
		while($E = $AC->getNextEntry()){
			$BT = new Button("Löschen", "trash", "icon");
			$BT->onclick(OnEvent::rme($E, "deleteMe", "", OnEvent::reloadPopup("mEinkaufszettel")));
			$T->addRow(array($E->A("EinkaufszettelName"), $BT));
			$T->addRowStyle("font-size:20px;");
		}
		
		if($AC->numLoaded() == 0){
			$T->addRow (array("Die Einkaufsliste enthält keine Einträge."));
			$T->addRowColspan(1, 2);
		}
		
		return $T;
	}
}
?>