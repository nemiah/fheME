<?php
/**
 *  This file is part of Demo.

 *  Demo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  Demo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
use Tinkerforge\IPConnection;
use Tinkerforge\BrickletTemperatureIR;

class TinkerforgeGUI extends Tinkerforge implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Tinkerforge");
	
		$B = $gui->addSideButton("Master", "new");
		$B->popup("", "Master", "Tinkerforge", $this->getID(), "readMaster", "", "", "{width:820}");
		
		return $gui->getEditHTML();
	}
	
	public function readMaster(){
		
		$i = rand(1, 9999999);
		
		echo "<div id=\"placeholder\" style=\"width:800px;height:300px;\"></div>".OnEvent::script("
\$j(function () {
    var d1 = [];
    var d2 = [];
    
    var plot = \$j.plot(\$j('#placeholder'), [], {
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
		});//:%S
	
	function updateTemp() {
		".OnEvent::rme($this, "readTemperature", "", "function(transport){
			if(\$j('#placeholder').length == 0){
				clearInterval(interval$i);
				return;
			}
			
			if(d1.length > 180)
				d1.shift();
				
			if(d2.length > 180)
				d2.shift();
			

			d1.push(\$j.parseJSON(transport.responseText)[0]);
			d2.push(\$j.parseJSON(transport.responseText)[1]);
			plot.setData([{data: d1, color: 'rgb(100, 100, 100)'}, { data: d2, color: 'rgb(149, 0, 0)', threshold: { below: 80, color: 'rgb(0, 0, 149)'} }]);
			plot.setupGrid();
			plot.draw();
			
		}")."
	}
	
	updateTemp();
	var interval$i = setInterval(updateTemp, 60000);
});");
		/*
		threshold: { 
			below: 120,
			color: 'rgb(200, 20, 30)'
		}
		 */
	}
	
	public function readTemperature(){
		require_once(__DIR__.'/lib/IPConnection.php');
		require_once(__DIR__.'/lib/BrickletTemperatureIR.php');


		$host = $this->A("TinkerforgeServerIP");
		$port = 4223;

		$ipcon = new IPConnection($host, $port);
		
		$t = new BrickletTemperatureIR("9nA");

		$ipcon->addDevice($t);
		echo json_encode(array(array(time() * 1000, $t->getAmbientTemperature() / 10.0), array(time() * 1000, $t->getObjectTemperature() / 10.0)));
		
		$ipcon->destroy();
	}
}
?>