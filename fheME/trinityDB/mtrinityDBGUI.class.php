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
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

class mtrinityDBGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mtrinityDB");

		$gui->name("trinityDB");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mtrinityDBGUI", "trinityDB", 170);
		$P->updateInterval(30 * 60);
		
		return $P;
	}

	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemtrinityDBGUI\"></span><p>trinityDB</p></div>
			<div style=\"padding:10px;\">";

			$AC = anyC::get("trinityDB");
			while($T = $AC->getNextEntry()){
				#$B = new Button("Neue Folgen", "star", "touch");
				#$B->onclick("trinityDB.show(".$T->getID().", 'newEpisodes');");
				#$html .= "$B";

				$B = new Button("Serienbrowser", "aperture", "touch");
				$B->onclick("trinityDB.show(".$T->getID().", 'browser');");
				$html .= "$B";
			}
		$html .= "</div>";
		echo $html;
	}

	public function load($trinityDBID, $mode){
		$T = new trinityDB($trinityDBID);
		
		echo "
		<div style=\"width:100%;margin-bottom:20px;position:fixed;top:0;left:0;\" id=\"trinityDBSelection\">
			<div style=\"float:right;margin-right:20px;\">
				<div onclick=\"trinityDB.hide();\" style=\"cursor:pointer;float:left;font-family:Roboto;font-size:30px;padding:10px;\">
					<span style=\"margin-left:10px;float:right;margin-top:5px;color:#bbb;\" class=\"iconic iconicL x\"></span> <span>Schließen</span>
				</div>
			</div>
			
			
			<div style=\"clear:both;\">
			</div>
			
		</div>
		
		
		<iframe id=\"trinityDBBrowser\" style=\"width:100%;border:0px;margin:0px;\" src=\"".$T->A("trinityDBURL")."/ubiquitous/CustomerPage/?D=trinityDB/Serien&mode=$mode\"></iframe>
		".OnEvent::script("\$j('#trinityDBBrowser').css('height', \$j(window).height() - 5)");
	}
	
}
?>