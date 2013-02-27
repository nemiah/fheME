<?php
/*
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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mGerichtGUI extends anyC implements iGUIHTMLMP2 {
	public function getHTML($id, $page){
		
		$this->addOrderV3("GerichtName");
		$this->loadMultiPageMode($id, $page, 20);
		$gui = new HTMLGUIX($this);
		$gui->name("Gericht");
		$gui->attributes(array("GerichtName"));
		
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemGerichtGUI\"></span><p>Essen</p></div>
			<div style=\"padding:10px;height:100px;overflow:auto;\">";

		
		$BU = new Button("", "./fheME/Gericht/update.png", "icon");
		$BU->style("float:right;");
		$BU->onclick("fheOverview.loadContent('mGerichtGUI::getOverviewContent');");
		
		$AC = anyC::get("Gericht");
		$AC->addOrderV3("RAND()");
		$AC->setLimitV3("1");
		
		$G = $AC->getNextEntry();
		
		if($G != null){

			$B = new Button("", "./fheME/Gericht/Gericht.png", "icon");
			$B->style("float:left;margin-right:10px;margin-bottom:20px;");
			$B->popup("", "Rezept", "Gericht", $G->getID(), "showDetailsPopup");

			$html .= $BU.$B."<b>".$G->A("GerichtName")."</b>";

			if($G->A("GerichtRezeptBuch") != ""){
				$html .= "<br /><small style=\"color:grey;\">Buch: ".$G->A("GerichtRezeptBuch")."<br />";
				$html .= "Seite: ".$G->A("GerichtRezeptBuchSeite")."</small>";
			}
		}
		
		$html .= "</div>";
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mGerichtGUI", "Essen", 100);
	}
}
?>
