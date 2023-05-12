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
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */

class mWetterstationGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mWetterstation");
		$gui->screenHeight();

		$gui->name("Wetterstation");
		
		$gui->attributes(array("WetterstationName"));
		
		return $gui->getBrowserHTML($id);
	}
	public function getOverviewContent($echo = true){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemWetterstationGUI\"></span><p>Wetter</p></div><div style=\"padding:10px;height:249px;overflow:auto;\">";
		while($W = $this->n()){
			#$W->download();
			
			$html .= "<div style=\"display:flex;\">
				<b style=\"flex:1;font-size:30px;font-weight:bold;color:#555;display:block;\">".Util::formatNumber("de_DE", (float) $W->A("WetterstationOutdoorTemp"), 1)." °C</b>
				<b style=\"flex:1;font-size:30px;font-weight:bold;color:#555;display:block;text-align:center;\">".round($W->A("WetterstationOutdoorRainTotal") - $W->A("WetterstationOutdoorRainTotalYesterday"))." L</b>
				</div>";
			
			$sunInfo = date_sun_info(time(), $W->A("WetterstationLat"), $W->A("WetterstationLon"));
			
			$BSR = new Button("Sonnenaufgang", "./fheME/Wetterstation/sunrise.svg", "icon");
			$BSR->style("height:15px;margin-right:3px;vertical-align:bottom;");
			
			$BSS = new Button("Sonnenuntergang", "./fheME/Wetterstation/sunset.svg", "icon");
			$BSS->style("height:15px;margin-right:3px;vertical-align:bottom;");
			
			$BSU = new Button("UVI", "./fheME/Wetterstation/sun.svg", "icon");
			$BSU->style("height:15px;margin-right:3px;vertical-align:bottom;");
			
			$html .= "<div style=\"margin-top:15px;\">$BSR ".date("H:i", $sunInfo["sunrise"])." $BSS ".date("H:i", $sunInfo["sunset"])." $BSU ".$W->A("WetterstationOutdoorUVI")." <span style=\"color:grey;\">(".$W->A("WetterstationKey").")</span></div>";
			
			$MRS = new MoonRiseSet();
			$moonInfo = $MRS->calculateMoonTimes(date("m"), date("d"), date("Y"), $W->A("WetterstationLat"), $W->A("WetterstationLon"));
			
			$BMR = new Button("Mondaufgang", "./fheME/Wetterstation/moonrise.svg", "icon");
			$BMR->style("height:15px;margin-right:3px;vertical-align:bottom;");
			
			$BMS = new Button("Monduntergang", "./fheME/Wetterstation/moonset.svg", "icon");
			$BMS->style("height:15px;margin-right:3px;vertical-align:bottom;");
			
			
			$MP = new MoonPhase();
			
			$html .= "
				<div style=\"margin-top:15px;\">
					<div style=\"float:left;margin-right:10px;font-size:3em;margin-right:15px;margin-left:10px;\">".$MP->getPhaseEmoji()."</div>
					<div style=\"\">".$MP->getPhaseName()." <span style=\"color:grey;\">".round($MP->getIllumination() * 100)."%</span><br><small style=\"color:grey;\">🌕 ".Util::CLDateParser($MP->getNextFullMoon())."<br>🌑 ".Util::CLDateParser($MP->getNextNewMoon())."</small></div>
				</div>";
			
			$html .= "<div style=\"margin-top:15px;\">$BMR ".date("H:i", $moonInfo->moonrise)." $BMS ".date("H:i", $moonInfo->moonset)."</div>";
			
		}
		
		
		
		$html .= "</div>";
		
		
		if($echo)
			echo $html;
		return $html;
	}

	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mWetterstationGUI", "Wetterstation", 210);
		$P->updateInterval(1800);
		
		return $P;
	}

}
?>