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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class mWeckerGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		T::load(dirname(__FILE__), "Wecker");
		$this->setParser("WeckerTime", "Util::CLTimeParser");
		
		$this->addJoinV3("Device", "WeckerDeviceID", "=", "DeviceID");
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mWecker");
		$gui->screenHeight();

		$gui->name("Wecker");
		
		$gui->attributes(array("DeviceName", "WeckerTime"));
		$gui->colStyle("WeckerTime", "text-align:right;");
		
		#$B = $gui->addSideButton("Alex,\nsing!", "new");
		#$B->onclick("alex.sing('http://gffstream.ic.llnwd.net/stream/gffstream_w14a', 0, 100)");
		
		#$B = $gui->addSideButton("Alex,\nstop!", "new");
		#$B->onclick("alex.beQuiet()");
		
		return $gui->getBrowserHTML($id);
	}
	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemWeckerGUI\"></span><p>Wecker</p></div>
			<div style=\"padding:10px;\">";

		
			$B = new Button("Wecker anzeigen", "moon_stroke", "iconicL");
			#Overlay.showDark();
			$html .= "
			<div class=\"touchButton\" onclick=\"Wecker.loadThemAll(function(){ Wecker.show(); });\">
				".$B."
				<div class=\"label\">Wecker anzeigen</div>
				<div style=\"clear:both;\"></div>
			</div>";
		
		$html .= "</div>".OnEvent::script("if(\$j.jStorage.get('phynxWeckerActive', false) === true) Wecker.loadThemAll(function(){ Wecker.show(); });");
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mWeckerGUI", "Wecker", 100);
	}


	public function loadThemAll($DeviceID){
		$AC = anyC::get("Wecker", "WeckerDeviceID", $DeviceID);
		$AC->addAssocV3("WeckerIsActive", "=", "1");
		
		die($AC->asJSON());
	}
	
	public function checkTermine(){
		$today = Util::parseDate("de_DE", date("d.m.Y"), "store");
		
		$Kalender = mxCalGUI::getCalendarData($today, $today + 3600 * 24 * 1, Session::currentUser()->getID());
		
		$found = false;
		for($i = 0; $i < 2; $i++){
			if($found !== false)
				break;
			
			$events = $Kalender->getEventsOnDay(date("dmY", $today + 3600 * 24 * $i));
			foreach($events AS $v)
				foreach($v AS $s){
					if($s->timestamp() > time() AND $found === false){
						$found = $s;
						break;
					}
				}
		}
		
		if(!$found)
			die("false");
		
		$O = new stdClass();
		$O->name = $found->title();
		$O->start = $found->timestamp();
		
		echo json_encode(array($O));
		
	}
}
?>