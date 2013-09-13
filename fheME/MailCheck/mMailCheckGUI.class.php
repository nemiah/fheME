<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007, 2008, 2009, 2010, 2011, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mMailCheckGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mMailCheck");
		$gui->screenHeight();

		$gui->name("MailCheck");
		
		$gui->attributes(array("MailCheckName"));
		
		return $gui->getBrowserHTML($id);
	}

	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemGerichtGUI\"></span><p>MailCheck</p></div>
			<div style=\"padding:10px;overflow:auto;\">";

		
		$BU = new Button("", "./fheME/Gericht/update.png", "icon");
		$BU->style("float:right;");
		$BU->onclick("fheOverview.loadContent('mGerichtGUI::getOverviewContent');");
		
		$AC = anyC::get("MailCheck");
		
		while($MC = $AC->getNextEntry()){
			$B = new Button("Mails abholen", "mail", "iconicL");
			
			$html .= "
			<div class=\"touchButton\" onclick=\"Overlay.showDark();".OnEvent::popup("Mails abholen", "MailCheck", $MC->getID(), "check", "1", "", "{width:1000, top:20, left:20, hPosition:'center'}")."\">
				".$B."
				<div class=\"label\">".$MC->A("MailCheckName")."</div>
				<div style=\"clear:both;\"></div>
			</div>";
		}
		
		$html .= "</div>";
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mMailCheckGUI", "MailCheck", 0);
	}
}
?>