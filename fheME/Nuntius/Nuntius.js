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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

var Nuntius = {
	handleWS: function(topic, data){
		//if(data.type === "BrickletTemperatureIR")
		//	Tinkerforge.updatePlot(data);
		Popup.load("Nachricht", "Nuntius", data.NuntiusID, "showMessageTimeout", [data.timeout], "", "edit", "{remember:true}");
	}

};

Registry.callback("pWebsocket", function(){pWebsocket.subscribe("nuntius", Nuntius.handleWS);});