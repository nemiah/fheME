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

class mHeizungGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mHeizung");
		$gui->screenHeight();

		$gui->name("Heizung");
		
		$gui->attributes(array("HeizungName"));
		
		return $gui->getBrowserHTML($id);
	}


	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemHeizungGUI\"></span><p>Heizung</p></div>
			<div style=\"padding:10px;overflow:auto;\">";

		
		$AC = anyC::get("Heizung");
		
		while($H = $AC->n()){
			$C = $H->connect();
			$C->setPrompt("</FHZINFO>");
			$answer = $C->fireAndGet("xmllist ".$H->A("HeizungFhemName"))."</FHZINFO>";
			$states = [];
			$xml = new SimpleXMLElement($answer);
			foreach($xml->THZ_LIST->THZ[0]->STATE AS $STATE){
				$states[$STATE["key"].""] = $STATE["value"]."";
			}
			
			preg_match_all("/([a-zA-Z]+): ([0-9\-\.]+) /", $states["sGlobal"], $matches);
			$parsed = [];
			foreach($matches[1] AS $k => $v)
				$parsed[$v] = $matches[2][$k];

			$states["pXXFanstage0AirflowInlet"] = "0 m3/h";
			
			$airP = [0 => "pXXFanstage0AirflowInlet", 1 => "p37Fanstage1AirflowInlet", 2 => "p38Fanstage2AirflowInlet", 3 => "p39Fanstage3AirflowInlet"];
			
			#$html .= "Außentemperatur: ".Util::formatNumber("de_DE", (float) $parsed["outsideTemp"], 1)."<br>";

			
			$B = new Button("Lüftung", "./fheME/Heizung/wind.svg", "icon");
			$B->style("float:left;margin-right:5px;width:32px;");
			
			$html .= "
			<div class=\"touchButton\">
				".$B."
				<div class=\"label\" style=\"padding-top:0;\">
					".str_replace("--", " - ", $states["programFan_Mo-So_0"])."<br>
					<small style=\"color:grey;\">Tag: ".$states["p07FanStageDay"]." (".str_replace("m3/h", "m³/h", $states[$airP[$states["p07FanStageDay"]]])."), Nacht: ".$states["p08FanStageNight"]." (".str_replace("m3/h", "m³/h", $states[$airP[$states["p08FanStageNight"]]]).")</small>
				</div>
				<div style=\"clear:both;\"></div>
			</div>";
			
			$B = new Button("Wasser", "./fheME/Heizung/water.svg", "icon");
			$B->style("float:left;margin-right:5px;width:32px;");
			
			$html .= "
			<div class=\"touchButton\">
				".$B."
				<div class=\"label\" style=\"padding-top:0;\">
					".str_replace("--", " - ", $states["programDHW_Mo-So_0"]).", Aktuell: ".Util::formatNumber("de_DE", (float) $parsed["dhwTemp"], 1)." °C<br>
					<small style=\"color:grey;\">Tag: ".$states["p04DHWsetDayTemp"].", Nacht: ".$states["p05DHWsetNightTemp"]."</small>
				</div>
				<div style=\"clear:both;\"></div>
			</div>";
			
			$B = new Button("Heizung", "./fheME/Heizung/heat.svg", "icon");
			$B->style("float:left;margin-right:5px;width:32px;");
			
			$html .= "
			<div class=\"touchButton\">
				".$B."
				<div class=\"label\" style=\"padding-top:0;\">
					".str_replace("--", " - ", $states["programHC1_Mo-So_0"])."<br>
					<small style=\"color:grey;\">Tag: ".$states["p01RoomTempDayHC1"].", Nacht: ".$states["p02RoomTempNightHC1"]."</small>
				</div>
				<div style=\"clear:both;\"></div>
			</div>";
			
			$H->disconnect();
		}
		
		$html .= "</div>";
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mHeizungGUI", "Heizung", 0);
		$P->updateInterval(15 * 60);
		
		return $P;
	}

}
?>