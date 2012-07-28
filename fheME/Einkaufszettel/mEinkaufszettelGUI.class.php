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
	
	public function getOverviewListEntry($name, $time){
		return "<div style=\"padding:3px;\"><small style=\"float:right;color:grey;\">".Util::CLDateParser($time)."</small>".($name != "" ? $name : "Artikel $EAN nicht gefunden!")."</div>";
	}

	public function getOverviewContent(){
		$html = "<div class=\"Tab backgroundColor1\"><span class=\"lastUpdate\" id=\"lastUpdatemEinkaufszettelGUI\">asd</span><p><b>Einkaufen</b></p></div>
			<div style=\"padding:10px;\">";

		$B = new Button("Aktuelle Liste anzeigen", "./fheME/Einkaufszettel/Einkaufszettel.png", "icon");
		$B->popup("", "Einkaufsliste", "mEinkaufszettel", "-1", "showCurrentList", "", "", "{top:20, hPosition:'right'}");
		$B->style("float:right;margin-left:10px;");
		#<div style=\"border-bottom-width:1px;border-bottom-style:dotted;margin-right:45px;height:20px;margin-bottom:5px;\" id=\"EinkaufszettelInput\" class=\"borderColor1\"></div>
		$html .= "$B";#<small style=\"color:grey;\">Zuletzt hinzugefügt:</small>
		$html .= "<div id=\"EinkaufszettelLastAdded\"><div class=\"emptyElement\" style=\"padding:3px;\">";
		
		$AC = anyC::get("Einkaufszettel", "EinkaufszettelBought", "0");
		$AC->addOrderV3("EinkaufszettelTime", "DESC");
		$AC->setLimitV3("3");
		while($E = $AC->getNextEntry())
			$html .= $this->getOverviewListEntry($E->A("EinkaufszettelName"), $E->A("EinkaufszettelTime"));
		
		if($AC->numLoaded() == 0)
			$html .= "<span style=\"color:grey;\">Der Einkaufszettel ist leer</span></div>";
		$html .= "</div></div>";
		echo $html;
	}
	
	public function showCurrentList(){
		$T = new HTMLTable(1, "Einkaufsliste");
		$T->maxHeight(400);
		
		$B = new Button("Liste schließen", "stop", "icon");
		$B->onclick(OnEvent::closePopup("mEinkaufszettel"));
		$B->style("float:right;margin:10px;");
		
		$BM = new Button("Handy-Version anzeigen", "./fheME/Einkaufszettel/mobile.png", "icon");
		$BM->style("float:right;margin:10px;");
		$BM->onclick("window.open('".str_replace("/interface/rme.php", "/ubiquitous/CustomerPage/?CC=Shopping&key=".substr(Util::eK(), 0, 5), $_SERVER["SCRIPT_NAME"])."');");
		
		$AC = anyC::get("Einkaufszettel", "EinkaufszettelBought", "0");
		
		while($E = $AC->getNextEntry())
			$T->addRow(array($E->A("EinkaufszettelName")));
		
		if($AC->numLoaded() == 0)
			$T->addRow (array("Die Einkaufsliste enthält keine Einträge."));
		
		echo $B.$BM."<div style=\"clear:both;\"></div>".$T;
	}
}
?>