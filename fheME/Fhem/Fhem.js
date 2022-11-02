/*
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
var Fhem = {
	controls: new Array(),
	controlsRGB: new Array(),
	isUpdate: false,
	lastValue: new Array(),
	lastValueRGB: new Array(),
	sliderFullValues: [0, 6, 12, 18, 25, 31, 37, 43, 50, 56, 62, 68, 75, 81, 87, 93, 100],
	sliderOnOffValues: [0, 100],
	sliderUpDownValues: ["off", "none", "on"],
	updater: null,
	lastStates: null,
	
	doAutoUpdate: true,
	
	wsData: null,
	
	connection: function(){
		if(typeof autobahn == "undefined")
			return;
		
		contentManager.rmePCR("Fhem", "-1", "getWSData", ["fheME"], function(t){
			Fhem.wsData = t.responseData[0];
			
			var connection = new autobahn.Connection({
				url: "wss://"+Fhem.wsData.WebsocketServer+":"+Fhem.wsData.WebsocketServerPort, 
				realm: Fhem.wsData.WebsocketRealm,
				authmethods: ["phimAuth_"+Fhem.wsData.WebsocketRealm],
				authid: "phimUser",
				onchallenge: function(session, method, extra){
					return Fhem.wsData.WebsocketToken;
				}
			});

			connection.onopen = function (session, details) {
				Fhem.doAutoUpdate = false;
				
				function onevent(args) {
					var data = jQuery.parseJSON(args[0]);
					Fhem.requestUpdate(data.id);
				}

				session.subscribe('it.furtmeier.fheme', onevent);

			};

			connection.onclose = function(reason){

			}

			connection.open();
		});
		
		
		
	},
				
	initUpdater: function(){
		if(Fhem.updater != null)
			window.clearInterval(Fhem.updater);

		Fhem.updater = window.setInterval(function(){
			Fhem.refreshControls();
		}, 20 * 1000);
	},

	refreshControls: function(){
		if(!Fhem.doAutoUpdate){
			window.clearInterval(Fhem.updater);
			return;
		}
		
		if(!$('mFhemMenuEntry')) return;
		if(lastLoadedLeftPlugin != "FhemControl") {
			if(Fhem.updater != null){
				window.clearInterval(Fhem.updater);
				Fhem.updater = null;
			}
			return;
		}

		if(!Fhem.isUpdate)
			Fhem.requestUpdate();

	},

	requestUpdate: function(id){
		if(typeof Fhem.timeout != 'undefined' && Fhem.timeout != null)
			window.clearTimeout(Fhem.timeout);
		
		Fhem.timeout = window.setTimeout(function(){
			contentManager.rmePCR('FhemControl','','updateGUI',typeof id != "undefined" ? id : "",'Fhem.updateControls(transport);', "", false);
		}, 1500);
	},

	updateControls: function(transport){
		var json = jQuery.parseJSON(transport.responseText);
		Fhem.lastStates = json;

		Fhem.isUpdate = true;
		
		for (var e in json) {
			if($j("#FhemID_"+e).length == 0)
				continue;

			$j("#FhemID_"+e).html(json[e].state);
		}

		Fhem.isUpdate = false;
	},

	startSlider: function(cID/*, startValue*/){
		$j('#track'+cID).slider({
			orientation: "vertical",
			range: "min",
			min: 0,
			max: 16,
			value: 0,
			change: function(event, ui) {
				Fhem.onChangeSlider(cID, Fhem.sliderFullValues[ui.value]);
			}
		});
		Fhem.controls[cID] = "full";
	},

	startRGBSlider: function(cID, startValue){

	},

	startSliderUpDown: function(cID){

		$j('#track'+cID).slider({
			orientation: "vertical",
			range: "min",
			min: 0,
			max: 2,
			value: 1,
			change: function(event, ui) {
				Fhem.onChangeUpDownSlider(cID, Fhem.sliderUpDownValues[ui.value]);
			}
		});

		Fhem.controls[cID] = "upDown";
	},

	onChangeSlider: function(cID, value){
		if(!Fhem.lastValue[cID]) Fhem.lastValue[cID] = value;
		else {
			if(Fhem.lastValue[cID] == value) return;
			else Fhem.lastValue[cID] = value;
		}
		if(Fhem.isUpdate) return;
		if(value < 10) value = "0"+value;

		if(value == 0) contentManager.rmePCR('FhemControl','','setDevice', Array(cID,'off'),' ');
		else contentManager.rmePCR('FhemControl','','setDevice', Array(cID,'dim'+value+'%'),' ');
	},

	onChangeUpDownSlider: function(cID, value){
		if(Fhem.isUpdate) return;

		contentManager.rmePCR('FhemControl','','setDevice', Array(cID, value), " ");
		Fhem.isUpdate = true;
		$j('#track'+cID).slider("option", "value", 1);
		Fhem.isUpdate = false;
	},

	onChangeRGBSlider: function(cID, value){
		if(!Fhem.lastValueRGB[cID]) Fhem.lastValueRGB[cID] = value;
		else {
			if(Fhem.lastValueRGB[cID] == value) return;
			else Fhem.lastValueRGB[cID] = value;
		}
		if(Fhem.isUpdate) return;
		contentManager.rmePCR('FhemControl','','setDevice', Array(cID, value),' ');
	},

	startSliderOnOff: function(cID, startValue, returnOff){
		if(typeof returnOff == "undefined") returnOff = false;


		$j('#track'+cID).slider({
			orientation: "vertical",
			range: "min",
			min: 0,
			max: 1,
			value: 0,
			change: function(event, ui) {
				Fhem.onChangeSlider(cID, Fhem.sliderOnOffValues[ui.value]);
			}
		});

		Fhem.controls[cID] = "onOff";
	},

	onChangeSliderOnOff: function(cID, value, returnOff){
		if(typeof returnOff == "undefined") returnOff = false;

		if(!Fhem.lastValue[cID]) Fhem.lastValue[cID] = value;
		else {
			if(Fhem.lastValue[cID] == value) return;
			else Fhem.lastValue[cID] = value;
		}
		if(Fhem.isUpdate) return;
		//if(value < 10) value = "0"+value;

		if(value == 0) contentManager.rmePCR('FhemControl','','setDevice', Array(cID, 'off'),' ');
		else contentManager.rmePCR('FhemControl','','setDevice', Array(cID, 'on'),' '+(returnOff ? "Fhem.controls["+cID+"].setValue(0);" : ""));
	}
}

Fhem.initUpdater();
window.setTimeout(function(){
	Fhem.connection();
}, 3000);

//Registry.callback("pWebsocket", function(){pWebsocket.subscribe("fhem", Fhem.handleWS);});