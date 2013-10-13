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
class TinkerforgeBrickletGUI extends TinkerforgeBricklet implements iGUIHTML2 {
	
	function getHTML($id){
		$BC = new Button("Abbrechen", "stop");
		$BC->style("margin:10px;float:right;");
		$BC->onclick("\$j('#listAP .lastSelected').removeClass('lastSelected'); \$j('#editAP').fadeOut(400, function(){ \$j('#editDetailsTinkerforge').animate({'width':'400px'}, 200, 'swing'); });");
		
		$gui = new HTMLGUIX($this);
		$gui->name("Bricklet");
		
		$gui->type("TinkerforgeBrickletTinkerforgeID", "hidden");
		$gui->type("TinkerforgeBrickletType", "select", self::$types);
		
		$gui->addToEvent("onSave", "\$j('#listAP .lastSelected').removeClass('lastSelected'); \$j('#editAP').fadeOut(400, function(){ \$j('#editDetailsTinkerforge').animate({'width':'400px'}, 200, 'swing', function(){ ".OnEvent::reloadPopup("Tinkerforge")." }); }); ");
		
		return $BC."<div style=\"clear:both;\"></div>".$gui->getEditHTML();
	}
	
	public function showPlot(){
		
		echo "<div id=\"brickletPlot".$this->A("TinkerforgeBrickletUID")."\" style=\"width:800px;height:300px;\"></div>".OnEvent::script("
\$j(function () {
    Tinkerforge.plot.BID".$this->A("TinkerforgeBrickletUID")." = \$j.plot(\$j('#brickletPlot".$this->A("TinkerforgeBrickletUID")."'), [], {
		xaxis: { 
			mode: 'time',
			timezone: 'browser',
			timeformat: '%H:%M',
			tickLength: 0
		},
		grid: {
			borderWidth: 1,
			borderColor: '#AAAAAA'
		}
		});
});");
		/*
		threshold: { 
			below: 120,
			color: 'rgb(200, 20, 30)'
		}
		 */
	}
}
?>