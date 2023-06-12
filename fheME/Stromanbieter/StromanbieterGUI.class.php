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
		$data = $this->pricesGet();
		#print_r($data);
		#echo '</pre>';
		$oData = new stdClass();
		$oData->data = $data;
		$oData->label = "Preis";
		
		echo "<div id=\"pricePlot\" style=\"height:400px;\"></div>";
		echo OnEvent::script("
			var options = {
			xaxis: {
				mode: 'time',
				timezone: 'browser',
				timeformat: '%H',
				tickSize: [1, 'hour']
			},
			/*yaxis: {
				min: 0
			},*/
			series: {
				lines: { show: true },
				points: { show: false }
			},
			legend: {
				show:true,
				position: 'nw'
			},

			grid: {
				hoverable: true,
				clickable: true,
				borderWidth: 1,
				borderColor: '#AAAAAA'
			}
			};
			
			\$j.plot(\$j('#pricePlot'), [".json_encode($oData)."], options);
			
			function showTooltip(x, y, contents) {
				\$j('<div id=\"tooltip\"></div>').css({
					position: 'absolute',
					top: y + 3,
					left: x - 5
				}).appendTo('body').qtip(\$j.extend({}, qTipSharedYellow, {
					content: {text: contents}
				}));
			};
			
			\$j('#pricePlot').bind('plothover', function (event, pos, item) {
				if (item) {
					if (previousPoint != item.dataIndex) {
						previousPoint = item.dataIndex;

						\$j('#tooltip').remove
						var ts = new Date(item.datapoint[0]);
						showTooltip(item.pageX, item.pageY, item.datapoint[1].toFixed(3));
					}
				}
				else {
					\$j('#tooltip').remove();
					previousPoint = null;            
				}
			});");
	}
}
?>