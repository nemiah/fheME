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
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mWecker");

		$gui->name("Wecker");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}
	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemWeckerGUI\"></span><p>Wecker</p></div>
			<div style=\"padding:10px;\">";

		
			$B = new Button("Wecker anzeigen", "moon_stroke", "iconicL");
			#Overlay.showDark();
			$html .= "
			<div class=\"touchButton\" onclick=\"Wecker.show();\">
				".$B."
				<div class=\"label\">Wecker anzeigen</div>
				<div style=\"clear:both;\"></div>
			</div>";
		
		$html .= "</div><!--<audio src=\"http://gffstream.ic.llnwd.net/stream/gffstream_w14a\"  controls autoplay>
<p>Your browser does not support the audio element.</p>
</audio>-->";
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mWeckerGUI", "Wecker", 100);
	}


	public function loadThemAll($DeviceID){
		$AC = anyC::get("Wecker", "WeckerDeviceID", $DeviceID);
		
		die($AC->asJSON());
	}
}
?>