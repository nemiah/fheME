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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mLogitechMediaServerGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mLogitechMediaServer");

		$gui->name("Logitech Media Server");
		
		$gui->attributes(array("LogitechMediaServerName"));
		
		return $gui->getBrowserHTML($id);
	}


	public function getOverviewContent($echo = true){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemLogitechMediaServerGUI\"></span><p>Logitech Media Server</p></div><div style=\"padding:10px;\">";

		$AC = anyC::get("LogitechMediaServer");
		while($LMS = $AC->getNextEntry()){
			#$html .= $LMS->playerControls();
			#$I->style("margin:10px;");

			$BP = new Button("Play", "play", "iconicL");
			#$BP->style("margin-top:10px;width:100px;background-position:65px 50%;");
			#$BP->rmePCR("LogitechMediaServer", $LMS->getID(), "play", array("$('LMSPlayerID".$LMS->getID()."').value"));

			$BS = new Button("Stop", "stop", "iconicL");
			#$BS->style("margin-right:10px;margin-top:10px;width:100px;background-position:65px 50%;");
			#$BS->rmePCR("LogitechMediaServer", $this->$LMS(), "stop", array("$('LMSPlayerID".$LMS->getID()."').value"));
		
			#$B = new Button("Mails abholen", "mail", "iconicL");
			
			$html .= "
			<div class=\"touchButton\">
				<div onclick=\"".OnEvent::rme($LMS, "play", array("$('LMSPlayerID".$LMS->getID()."').value"))."\">".$BP."
					<div class=\"label\">Play</div>
					<div style=\"clear:both;\"></div>
				</div>
			</div>";
			
			$html .= "
			<div class=\"touchButton\">
				<div onclick=\"".OnEvent::rme($LMS, "stop", array("$('LMSPlayerID".$LMS->getID()."').value"))."\">".$BS."
					<div class=\"label\">Stop</div>
					<div style=\"clear:both;\"></div>
				</div>
			</div>";
			
			
			$I = new HTMLInput("LMSPlayerID", "select", "0", $LMS->players());
			$I->id("LMSPlayerID".$LMS->getID());
			
			$html .= $I;
		}
		
		$html .= "</div>";
		
		if($echo)
			echo $html;
		
		return $html;
	}
}
?>