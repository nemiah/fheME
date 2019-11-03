<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

class mWochenplanGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$html = "";
		for($i = 1; $i <= 7; $i++)
			$html .= $this->getGiorno($i);
	
		
		$html .= "<div class=\"giorno\" style=\"display:inline-block;width:calc(100% / 8);vertical-align:top;box-sizing:border-box;\">";
		
		$B = new Button("Neuer Eintrag", "./images/i2/new.png", "icon");
		$B->style("float:right;margin-top:7px;");
		$B->editInPopup("WochenplanProgramm", -1, "Eintrag erstellen", "WochenplanProgrammGUI;categoria:1");
		
		$html .= "<div class=\"mezzaGiornata\" style=\"width:100%;display:inline-block;vertical-align:top;box-sizing:border-box;\">";
		$html .= "<h2 class=\"prettySubtitle backgroundColor3\" style=\"padding-top:10px;margin-top:0px;\">{$B}Vormittag</h2>";
		
		$html .= $this->getProgramm("1");
		
		/*$L = new HTMLList();
		$L->noDots();
		$AC = anyC::get("WochenplanProgramm", "WochenplanProgrammKategorie", "1");
		while($W = $AC->n()){
			$L->addItem($W->A("WochenplanProgrammTitel"));
			$L->addItemEvent("onclick", "contentManager.editInPopup('WochenplanProgramm', '".$W->getID()."', 'Eintrag bearbeiten');");
		}*/
		$html .= "</div>";
		
		$B = new Button("Neuer Eintrag", "./images/i2/new.png", "icon");
		$B->style("float:right;margin-top:7px;");
		$B->editInPopup("WochenplanProgramm", -1, "Eintrag erstellen", "WochenplanProgrammGUI;categoria:2");
		
		$html .= "<div class=\"mezzaGiornata\" style=\"width:100%;display:inline-block;vertical-align:top;box-sizing:border-box;\">";
		$html .= "<h2 class=\"prettySubtitle backgroundColor3\" style=\"padding-top:10px;margin-top:0px;\">{$B}Abend</h2>";
		
		$html .= $this->getProgramm("2");
		
		$html .= "</div>";
		
		$html .= "</div>";
		
		return $html.OnEvent::script("\$j('.giorno').css('height', contentManager.maxHeight());\$j('.mezzaGiornata').css('height', contentManager.maxHeight() / 2);");
	}
	
	private function getProgramm($categoria, $config = true){
		$L = new HTMLList();
		$L->noDots();
		$L->addListStyle("margin-top:0px;padding-top:5px;margin-bottom:0px;padding-bottom:5px;");
		$AC = anyC::get("WochenplanProgramm", "WochenplanProgrammKategorie", $categoria);
		while($W = $AC->n()){
			$L->addItem($W->A("WochenplanProgrammTitel"));
			if($config)
				$L->addItemEvent("onclick", "contentManager.editInPopup('WochenplanProgramm', '".$W->getID()."', 'Eintrag bearbeiten');");
			$L->addItemStyle("margin-left:5px;margin-bottom:5px;");
		}
		return $L;
	}

	private function getSegmento($giorno, $segmento, $edit){
		$L = new HTMLList();
			$L->noDots();
			$L->addListStyle("margin-top:0px;padding-top:5px;margin-bottom:0px;padding-bottom:5px;");
			$AC = anyC::get("Wochenplan", "WochenplanTag", $giorno);
			$AC->addAssocV3("WochenplanAbschnitt", "=", $segmento);
			while($W = $AC->n()){
				$L->addItem($W->A("WochenplanTitel"));
				if($edit)
					$L->addItemEvent("onclick", "contentManager.editInPopup('Wochenplan', '".$W->getID()."', 'Eintrag bearbeiten');");
				$L->addItemStyle("margin-left:5px;margin-bottom:5px;");
			}
			
			return $L;
	}
	
	private function getGiorno($i, $style = "width:calc(100% / 8);border-right-style:solid;border-right-width:1px;", $config = true){
		$j = $i;
		if($i == 7)
			$j = 0;


		$html = "<div class=\"giorno borderColor1\" style=\"display:inline-block;{$style}vertical-align:top;box-sizing:border-box;\">";

		$B = new Button("Neuer Eintrag", "./images/i2/new.png", "icon");
		$B->style("float:right;margin-top:7px;");
		$B->editInPopup("Wochenplan", -1, "Eintrag erstellen", "WochenplanGUI;giorno:$i");
		
		if($config)
			$html .= "<h2 class=\"prettySubtitle backgroundColor4\" style=\"padding-top:10px;margin-top:0px;margin-bottom:0;\">$B".Util::CLWeekdayName($j)."</h2>";
		
		if($config)
			$html .= "<div style=\"padding-left:5px;padding-top:5px;padding-bottom:5px;\" class=\"backgroundColor3\">Vormittagsprogramm</div>";
		else {
			$html .= "<div id=\"giorno_VPT_$i\" onclick=\"\$j('#giorno_VP_$i').toggle(); \$j('#giorno_VPT_$i').toggle();\" style=\"padding-left:5px;padding-top:5px;padding-bottom:5px;\" class=\"backgroundColor3\">Vormittagsprogramm…</div>";
			$html .= "<div id=\"giorno_VP_$i\" onclick=\"\$j('#giorno_VP_$i').toggle(); \$j('#giorno_VPT_$i').toggle();\" style=\"display:none;\" class=\"backgroundColor3\">".$this->getProgramm("1", $config)."</div>";
		}
		
		$html .= "<div style=\"height:100px;\">".$this->getSegmento($j, "mattina", $config)."</div>";
		$html .= "<div style=\"\" class=\"backgroundColor4\">".$this->getSegmento($j, "mezzogiorno", $config)."</div>";
		$html .= "<div style=\"height:100px;\">".$this->getSegmento($j, "pomeriggio", $config)."</div>";
		$html .= "<div style=\"padding-left:5px;padding-top:5px;padding-bottom:5px;\" class=\"backgroundColor1\"></div>";
		if($config)
			$html .= "<div style=\"padding-left:5px;padding-top:5px;padding-bottom:5px;\" class=\"backgroundColor3\">Abendprogramm</div>";
		else {
			$html .= "<div id=\"giorno_APT_$i\" onclick=\"\$j('#giorno_AP_$i').toggle(); \$j('#giorno_APT_$i').toggle();\" style=\"padding-left:5px;padding-top:5px;padding-bottom:5px;\" class=\"backgroundColor3\">Abendprogramm…</div>";
			$html .= "<div id=\"giorno_AP_$i\" onclick=\"\$j('#giorno_AP_$i').toggle(); \$j('#giorno_APT_$i').toggle();\" style=\"display:none;\" class=\"backgroundColor3\">".$this->getProgramm("2", $config)."</div>";
		}
		
		$html .= "<div style=\"height:100px;\">".$this->getSegmento($j, "sera", $config)."</div>";

		$html .= "</div>";
		
		return $html;
	}
	
	public function getOverviewContent($echo = true){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemWochenplanGUI\"></span><p>Wochenplan: ".Util::CLWeekdayName(date("w"))."</p></div>
			<div style=\"padding:10px;\">";
		
		
		$html .= $this->getGiorno(date("w"), "width:100%;", false);
		
		
		$html .= "</div>";
		
		if($echo)
			echo $html;
		
		return $html;
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mWochenplanGUI", "Wochenplan", 360);
		$P->updateInterval(3600);
		#$P->updateFunction("function(){ if(!Fhem.doAutoUpdate) return; ".OnEvent::rme(new FhemControlGUI(-1), "updateGUI", "", "function(transport){ fheOverview.updateTime('mFhemGUI'); Fhem.updateControls(transport); }")."}");
		
		return $P;
	}

}
?>