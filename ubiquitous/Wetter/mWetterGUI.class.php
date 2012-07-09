<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mWetterGUI extends mWetter implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mWetter");

		$gui->name("Wetter");
		
		$gui->attributes(array());
		
		$B = $gui->addSideButton("Mond", "./ubiquitous/Wetter/icons32/weather-clear-night.png");
		$B->popup("", "Mond", "mWetter", "-1", "mond");
		
		return $gui->getBrowserHTML($id);
	}
	
	public function mond(){
		echo "<pre>";
		print_r(Mond::phase());
		echo "</pre>";
	}

	public function getOverviewContent($echo = true){
		$html = "<div class=\"Tab backgroundColor1\"><span class=\"lastUpdate\" id=\"lastUpdatemWetterGUI\"></span><p>Wetter</p></div><div style=\"padding:10px;height:300px;overflow:auto;\">";
		while($W = $this->getNextEntry()){
			$data = $W->getData();
			
			$icon = Wetter::getWeatherIcon($data->item->condition->code);
			$B = new Button("", "./ubiquitous/Wetter/icons48/".$icon[0].".png", "icon");
			$B->style("float:left;");
			
			$html .= $B."<div style=\"margin-left:60px;\">
				<b style=\"font-size:15px;font-weight:bold;\">".$data->item->condition->temp." °".$data->units->temperature."<br /><span style=\"color:grey;\">".Wetter::getWeatherCondition($data->item->condition->code)."</span><br />
				</b>
				<small style=\"color:grey;\">
					Luftfeuchtigkeit: ".$data->atmosphere->humidity." %<br />
					<!--Luftdruck: ".$data->atmosphere->pressure." ".$data->units->pressure."<br />-->
					Windgeschw.: ".$data->wind->speed." ".$data->units->speed."
				</small></div>";
			
			$html .= "<div style=\"clear:both;\">";
			for($i = 0; $i < 2; $i++){
				$Date = date_parse($data->item->forecast[$i]->date);
				
				$time = mktime(0, 0, 1, $Date["month"], $Date["day"], $Date["year"]);
				
				$icon = Wetter::getWeatherIcon($data->item->forecast[$i]->code);
				$B = new Button("", "./ubiquitous/Wetter/icons48/".$icon[0].".png", "icon");
				#$B->style("float:left;margin-top:30px;");

				$html .= "<div style=\"float:left;width:49%;margin-top:20px;\"><small style=\"color:grey;\">".($i == 0 ? "Heute" : "Morgen")." (".Datum::getGerWeekArray(date("w", $time)).")</small><br />
					<b style=\"font-size:15px;font-weight:bold;\">".$data->item->forecast[$i]->low." - ".$data->item->forecast[$i]->high." °".$data->units->temperature."<br />".$B."
					</b>
					</div>";
			}
			
			$html .= "</div>";
		}
		
		$M = Mond::phase();
		
		$B = new Button("", "./ubiquitous/Wetter/icons48/".$M->image, "icon");
		$B->style("float:left;margin-top:30px;");

		$html .= "<div style=\"clear:both;\"></div>".$B."<div style=\"margin-top:30px;margin-left:60px;\">
			<small style=\"color:grey;\">$M->zodiac, $M->days ".($M->days == 1 ? "Tag" : "Tage")."</small><br />
			<b style=\"font-size:15px;font-weight:bold;\">".$M->phase."</b>
			</div>";
		
		$html .= "</div>";
		
		
		if($echo)
			echo $html;
		return $html;
	}

}
?>