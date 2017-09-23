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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class GerichtGUI extends Gericht implements iGUIHTML2 {
	function getHTML($id){
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUIX($this);
		$gui->name("Gericht");

		$gui->attributes(array(
			"GerichtName",
			"GerichtBemerkung",
			#"GerichtRezeptBuch",
			#"GerichtRezeptBuchSeite",
			"GerichtZutaten"
		));
		
		$gui->label("GerichtName","Name");
		$gui->label("GerichtRezeptBuch","Buch");
		$gui->label("GerichtRezeptBuchSeite","Seite");
		
		#$gui->space("GerichtRezept", "Rezept");
		
		#$gui->setLabel("GerichtBemerkung","Bemerkung");
		//$gui->setLabel("GerichtZuletztAm","gekocht am");
		$gui->type("GerichtZuletztAm","hidden");
		$gui->type("GerichtRezept","textarea");
		$gui->type("GerichtBemerkung","textarea");
		$gui->type("GerichtZutaten","textarea");
		$gui->inputStyle("GerichtZutaten", "height:200px;");
		#$gui->setStandardSaveButton($this);
		if(BPS::popProperty("GerichtGUI", "mode", "default") == "popup")
			$gui->displayMode ("popup");
		
		return $gui->getEditHTML();
	}
	
	public function showDetailsPopup(){
		$BD = new Button("Gericht löschen", "trash", "icon");
		$BD->style("float:right;margin-right:10px;");
		$BD->onclick("deleteClass('Gericht','".$this->getID()."', function() { ".OnEvent::closePopup("Gericht")." fheOverview.loadContent('mGerichtGUI::getOverviewContent'); },'Eintrag wirklich löschen?');");
		
		$BC = new Button("Popup schließen", "stop", "icon");
		$BC->style("float:right;margin-right:10px;");
		$BC->onclick(OnEvent::closePopup("Gericht"));
		
		$BE = new Button("Gericht bearbeiten", "edit", "icon");
		$BE->style("float:right;margin-right:10px;");
		$BE->editInPopup("Gericht", $this->getID(), "Gericht bearbeiten", "GerichtGUI;mode:popup");
		
		$html = "<h1>$BC$BD$BE".$this->A("GerichtName")."</h1><p>";
		
		
		if($this->A("GerichtRezeptBuch") != ""){
			$html .= "Buch: ".$this->A("GerichtRezeptBuch")."<br />";
			$html .= "Seite: ".$this->A("GerichtRezeptBuchSeite")."<br />";
		}
		
		$html .= nl2br($this->A("GerichtRezept"));
		
		$html .= "</p>";
		
		echo $html;
	}
}
?>