/**
 *
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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

var Tinkerforge = {
	handleWS: function(topic, data){
		if(data.type === "BrickletTemperatureIR")
			Tinkerforge.updatePlot(data);
	},
			
	data: {},
	plot: {},
	updatePlot: function(data){
		var d = new Date()
		var n = d.getTimezoneOffset();

		var newTime = data.time * 1000 - n * 60 * 1000;
		var newTemp = data.value;
		
		
		if($j('#brickletPlot'+data.bricklet).length === 0 || !Tinkerforge.plot["BID"+data.bricklet])
			return;
		
		if(!Tinkerforge.data["BID"+data.bricklet])
			Tinkerforge.data["BID"+data.bricklet] = [];

		if(Tinkerforge.data["BID"+data.bricklet].length > 180)
			Tinkerforge.data["BID"+data.bricklet].shift();

		Tinkerforge.data["BID"+data.bricklet].push([newTime, newTemp]);
		
		
		Tinkerforge.plot["BID"+data.bricklet].setData([{ data: Tinkerforge.data["BID"+data.bricklet], color: 'rgb(149, 0, 0)', shadowSize: 0, threshold: { below: 80, color: 'rgb(0, 0, 149)'} }]);
		Tinkerforge.plot["BID"+data.bricklet].setupGrid();
		Tinkerforge.plot["BID"+data.bricklet].draw();
	}
	
};

Registry.callback("pWebsocket", function(){pWebsocket.subscribe("tinkerforge", Tinkerforge.handleWS);});
