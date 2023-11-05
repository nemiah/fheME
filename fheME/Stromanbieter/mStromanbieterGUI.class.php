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

class mStromanbieterGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mStromanbieter");
		$gui->screenHeight();

		$gui->name("Stromanbieter");
		
		$gui->attributes(array("StromanbieterName"));
		
		return $gui->getBrowserHTML($id);
	}

	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemStromanbieterGUI\"></span><p>Stromanbieter</p></div>
			<div style=\"padding:10px;overflow:auto;\">";

		$AC = anyC::get("Stromanbieter");
		
		while($S = $AC->n()){
			# [$data, $minD1, $maxD1, $minD1Time, $minD2, $maxD2, $minD2Time] = $S->pricesGet();
			
			$B = new Button("Lädt", "./fheME/Zweirad/zap.svg", "icon");
			$B->style("float:left;margin-right:5px;width:32px;");
			
			$content = "";
			$usage = $S->usageProcess($S->A("StromanbieterUsage"));
			$usage = array_slice($usage, -2, 2, true);
			foreach($usage AS $month => $monthData)
				$content .= mb_substr(Util::CLMonthName(substr($month, 4)), 0, 3).": ".Util::CLFormatCurrency(Util::kRound($monthData[0]), true).", ".Util::CLFormatNumber($monthData[1])." kWh = ".Util::CLFormatCurrency($monthData[0] / $monthData[1], true)."/kWh<br>";
			
			$html .= "
			<div onclick=\"".OnEvent::popup("Preise", "Stromanbieter", $S->getID(), "pricesShow", "", "", "{width:800, top: 20}")."\" class=\"touchButton\">
				".$B."
				<div class=\"label\" style=\"padding-top:0;\">
					".$S->A("StromanbieterName")."<br>
					<small style=\"color:grey;\">$content</small>
				</div>
				<div style=\"clear:both;\"></div>
			</div>";
			
		}
		
		if($AC->numLoaded() == 0){
			echo "";
			return;
		}
		
		$html .= "</div>";
		
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mStromanbieterGUI", "Stromanbieter", 0);
		$P->updateInterval(3600 * 3);
		
		return $P;
	}

}
?>