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
		
		$gui->type("StromanbieterUsage", "textarea");
		$gui->type("StromanbieterIsDefault", "checkbox");
		
		return $gui->getEditHTML();
	}
	
	public function pricesShow(){
		$noDischarge = json_decode(mUserdata::getGlobalSettingValue("NoDischargeTimes", "{}"));
		
		$markings = "";
		foreach($noDischarge AS $day => $times)
			foreach($times AS $hour => $time)
				if($time == "2222")
					$markings .= ",
					{ color: '#ff9a9a', yaxis: {from: 0, to: 12}, xaxis: { from: ".(($day + $hour * 3600) * 1000).", to: ".(($day + ($hour + 1) * 3600) * 1000)." } }";
		
		
		#echo "<pre>";
		#echo $markings;
		#echo "</pre>";
		
		[$data, $minD1, $maxD1, $minD1Time, $minD2, $maxD2, $minD2Time] = $this->pricesGetProcessed();
		#echo "<pre style=\"font-size:8px;\">";
		#print_r($data);
		#echo '</pre>';
		$oData = new stdClass();
		$oData->data = $data;
		$oData->label = "Preis";
		$oData->color = "#df9900";
		$oData->threshold = new stdClass();
		$oData->threshold->below = $this->A("StromanbieterBuyBelowCent");
		$oData->threshold->color = "#457c00";
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
				min: 10,
				max: 40
			},
			series: {
				lines: { show: true, steps: true },
				points: { show: false }
			},
			grid: {
				borderWidth: 1,
				borderColor: '#AAAAAA',
				markings: [
					{ color: '#efefef', yaxis: { from: 0, to: ".$this->A("StromanbieterBuyBelowCent")." } },
					{ color: '#cccccc', yaxis: { from: 0, to: ".$this->A("StromanbieterChargeBelowCent")." } }$markings,
					{ color: '#555555', xaxis: { from: ".(time() * 1000).", to: ".(time() * 1000)." } }
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
		
		$html .= "<div id=\"costPlot\" style=\"width:790px;height:300px;\"></div>";
		
		$data = [];
		
		foreach($this->usageProcess($this->A("StromanbieterUsage")) AS $month => $monthData)
			$data[] = [mktime(0, 0, 0, substr($month, 4), 1, substr($month, 0, 4)) * 1000, $monthData[0]];
		#print_r($data);
		$oData = new stdClass();
		$oData->data = $data;
		$oData->label = "Kosten";
		$oData->color = "#df9900";
		
		$html .= OnEvent::script("
			var options = {
			xaxis: {
				mode: 'time',
				timezone: 'browser',
				timeformat: '%m.%Y',
				tickSize: [1, 'month']
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
				markings: []
			}
			};
			\$j.plot(\$j('#costPlot'), [".json_encode($oData)."], options);");
		
		echo $html;
	}
}
?>