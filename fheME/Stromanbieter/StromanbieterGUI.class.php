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
class StromanbieterGUI extends Stromanbieter implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Stromanbieter");
	
		$B = $gui->addSideButton("Preise", "new");
		$B->popup("", "Preise", "Stromanbieter", $this->getID(), "pricesShow", "", "", "{width:800}");
		
		return $gui->getEditHTML();
	}
	
	public function pricesShow(){
		[$data, $minD1, $maxD1, $minD1Time, $minD2, $maxD2, $minD2Time] = $this->pricesGet();
		#echo "<pre style=\"font-size:8px;\">";
		#print_r($data);
		#echo '</pre>';
		$oData = new stdClass();
		$oData->data = $data;
		$oData->label = "Preis";
		#continue;
		$html = "<div id=\"pricePlot\" style=\"width:790px;height:300px;\"></div>";

		$html .= OnEvent::script("

			var options = {
			xaxis: {
				mode: 'time',
				timezone: 'browser',
				timeformat: '%H',
				tickSize: [2, 'hour']
			},
			yaxis: {
			},
			series: {
				lines: { show: true },
				points: { show: false }
			},

			grid: {
				borderWidth: 1,
				borderColor: '#AAAAAA',
				markings: [
					{ color: '#ff9a9a', yaxis: { from: ". max($maxD1, $maxD2).", to: ". max($maxD1, $maxD2)." } },
					{ color: '#85af7b', yaxis: { from: ". min($minD1, $minD2).", to: ". min($minD1, $minD2)." } }
				]
			}
			};

			/*window.setTimeout(function(){ 
				console.log(\$j('.touchHeader').width());
				\$j('#pricePlot').css('width', \$j('.touchHeader').width()+'px');
			}, 100);*/
			\$j.plot(\$j('#pricePlot'), [".json_encode($oData)."], options);



			function showTooltip(x, y, contents) {
				\$j('<div id=\"tooltip\"></div>').css({
					position: 'absolute',
					top: y + 3,
					left: x - 5
				}).appendTo('body').qtip(\$j.extend({}, qTipSharedYellow, {
					content: {text: contents}
				}));
			};");

		$html .= "<p>Heute: Min $minD1 (".date("H:i", $minD1Time)."), Max $maxD1, Δ ".($maxD1 - $minD1).", ".round(100 - (100 / $maxD1 * $minD1))."%";
		if($maxD2 > 0)
			$html .= "<br>Morgen: Min $minD2 (".date("H:i", $minD2Time)."), Max $maxD2, Δ ".($maxD2 - $minD2).", ".round(100 - (100 / $maxD2 * $minD2))."%";


		foreach($this->usageGet() AS $month => $monthData){
			$html .= "<br>".Util::CLMonthName(substr($month, 4))." ".substr($month, 0, 4).": ".Util::CLFormatCurrency(Util::kRound($monthData[0]), true).", $monthData[1] kWh";
		}
		$html .= "</p>";
		
		echo $html;
	}
}
?>