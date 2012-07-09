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

		$gui->name("MailCheck");
		
		$gui->attributes(array("MailCheckName"));
		
		return $gui->getBrowserHTML($id);
	}

	public function getOverviewContent(){
		$html = "<div class=\"Tab backgroundColor1\"><span class=\"lastUpdate\" id=\"lastUpdatemGerichtGUI\"></span><p>MailCheck</p></div>
			<div style=\"padding:10px;overflow:auto;\">";

		
		$BU = new Button("", "./fheME/Gericht/update.png", "icon");
		$BU->style("float:right;");
		$BU->onclick("fheOverview.loadContent('mGerichtGUI::getOverviewContent');");
		
		$AC = anyC::get("MailCheck");
		
		while($MC = $AC->getNextEntry()){
			$B = new Button("Mails abholen", "./fheME/MailCheck/MailCheck.png", "icon");
			$B->popup("", "Mails abholen", "MailCheck", $MC->getID(), "check", "1");
			
			$html .= "<div style=\"display:inline-block;width:80px;text-align:center;\">".$B."<br /><small>".$MC->A("MailCheckName")."</small></div>";
		}
		
		$html .= "</div>";
		echo $html;
	}
}
?>