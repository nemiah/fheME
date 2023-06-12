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
			<div style=\"\">";

		$AC = anyC::get("Stromanbieter");
		
		while($S = $AC->n()){
			
			$data = $S->pricesGet();
			#print_r($data);
			#echo '</pre>';
			$oData = new stdClass();
			$oData->data = $data;
			#$oData->label = "Preis";
			
			$html .= "<div id=\"pricePlot\" style=\"width:100%;height:200px;\"></div>";
			
			$html .= OnEvent::script("
				
				var options = {
				xaxis: {
					mode: 'time',
					timezone: 'browser',
					timeformat: '%H',
					tickSize: [4, 'hour']
				},
				/*yaxis: {
					min: 0
				},*/
				series: {
					lines: { show: true },
					points: { show: false }
				},

				grid: {
					/*hoverable: true,
					clickable: true,*/
					borderWidth: 1,
					borderColor: '#AAAAAA'
				}
				};

				window.setTimeout(function(){ 
					console.log(\$j('.touchHeader').width());
					\$j('#pricePlot').css('width', \$j('.touchHeader').width()+'px');
					\$j.plot(\$j('#pricePlot'), [".json_encode($oData)."], options);
				}, 100);
				
				

				function showTooltip(x, y, contents) {
					\$j('<div id=\"tooltip\"></div>').css({
						position: 'absolute',
						top: y + 3,
						left: x - 5
					}).appendTo('body').qtip(\$j.extend({}, qTipSharedYellow, {
						content: {text: contents}
					}));
				};");
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