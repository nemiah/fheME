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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class TinkerforgeBricklet extends PersistentObject {
	public static $types = array("BrickletTemperatureIR" => "Temperature IR", "BrickletDualRelay" => "Dual Relay");
	
	public function getControl(){
		
		return "<div id=\"brickletPlot".$this->A("TinkerforgeBrickletUID")."\" style=\"width:280px;height:150px;\"></div>".OnEvent::script("
\$j(function () {
    Tinkerforge.plot.BID".$this->A("TinkerforgeBrickletUID")." = \$j.plot(\$j('#brickletPlot".$this->A("TinkerforgeBrickletUID")."'), [], {
		xaxis: { 
			mode: 'time',
			timezone: 'browser',
			timeformat: '%H:%M',
			tickLength: 0,
			color:'#BBBBBB',
			tickColor:'#BBBBBB',
			tickLength: 0
		},
		
		yaxis: {
			color:'#BBBBBB',
			tickColor:'#BBBBBB',
			tickLength: 0
		},
		
		grid: {
			borderWidth: 0,
			borderColor: '#AAAAAA'
		}
		});
});");
	}
	
	public function getData(){
		require_once(__DIR__.'/lib/IPConnection.php');
		require_once(__DIR__.'/lib/BrickletTemperatureIR.php');

		$a = new stdClass();
			
		try {
			$S = new Tinkerforge($this->A("TinkerforgeBrickletTinkerforgeID"));


			$connection = new Tinkerforge\IPConnection();
			$connection->connect($S->A("TinkerforgeServerIP"), $S->A("TinkerforgeServerPort"));

			$Type = "Tinkerforge\\".$this->A("TinkerforgeBrickletType");
			$Bricklet = new $Type($this->A("TinkerforgeBrickletUID"), $connection);
		
		
			$a->value1 = floor($Bricklet->getObjectTemperature() / 10.0);
			$a->value2 = floor($Bricklet->getAmbientTemperature() / 10.0);
		} catch (Exception $e){
			$a->value1 = 0;
			$a->value2 = 0;
			
		}	
			$a->time = time();
			
		return $a;
	}
}
?>