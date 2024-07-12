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

var fheOverview = {
	updater: null,
	targets: null,
	counter: 0,
	delays: null,
	replaceUpdateMethods: null,
	months: new Array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"),
	days: new Array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"),
	noresize: false,
	noreload: [],
	isInit:false,
	
	init: function(){
		if(fheOverview.isInit)
			return;
		
		/*$j('body').hammer().on("swipeleft", function(){
			//contentManager.loadPlugin('contentScreen', 'mfheOverview', 'mfheOverviewGUI;-');
			Interface.frameRestore();
		});*/
		
		fheOverview.isInit = true;
	},
	
	initUpdate: function(classes, delays, replaceUpdateMethods){
		//console.log(classes);
		//console.log(delays);
		//console.log(replaceUpdateMethods);
		
		if(fheOverview.updater != null)
			window.clearInterval(fheOverview.updater);

		fheOverview.updater = null;
		fheOverview.targets = classes.slice();
		fheOverview.delays = delays.slice();
		fheOverview.replaceUpdateMethods = replaceUpdateMethods.slice();
		fheOverview.counter = 0;
		
		//for (var i = 0; i < classes.length; i++)
		//	fheOverview.loadContent(classes[i]);
		
		fheOverview.updater = window.setInterval(function(){
			if(!$('onfheOverviewPage')){
				window.clearInterval(fheOverview.updater);
				fheOverview.updater = null;
				return;
			}
			
			
			for(var j = 0; j < fheOverview.delays.length; j++){
				if(fheOverview.delays[j] == 0)
					continue;
				
				if($j.inArray(fheOverview.targets[j], fheOverview.noreload) >= 0)
					continue;
				
				if(fheOverview.counter > 0 && fheOverview.counter % fheOverview.delays[j] == 0){
					if(fheOverview.replaceUpdateMethods[j] == null)
						fheOverview.loadContent(fheOverview.targets[j]);
					else
						fheOverview.replaceUpdateMethods[j]();
				}
			}
			
			fheOverview.counter++;
			if(fheOverview.counter > 1000000)
				fheOverview.counter = 1;
		}, 1000);
	},
	
	loadContent: function(target){
		var method = target.split("::");
		target = target.replace("::", "_");
		contentManager.rmePCR(method[0].replace("GUI", ""), -1, method[1], [$j.jStorage.get('phynxDeviceID', -1)], function(transport){
			//console.log('fheOverviewContent'+target);
			if($j('#fheOverviewContent'+target).length == 0)
				return;//console.log('fheOverviewContent'+target+" is NULL!");
			$j('#fheOverviewContent'+target).html(transport.responseText);
			//console.log(fheOverview.counter+":"+target);
			fheOverview.updateTime(method[0]);
		});
	},
	
	updateTime: function(target){
		if($('lastUpdate'+target)){
			var jetzt = new Date();

			$('lastUpdate'+target).update(jetzt.getHours()+":"+(jetzt.getMinutes() < 10 ? "0" : "")+jetzt.getMinutes());
		}
	},
			
	draggableStart: function(DeviceID){
		$j('.desktopDraggable').addClass('desktopMove').draggable({
			grid: [ 20,20 ],
			handle: '.handleMove',
			containment: '#OverviewDesktop',
			scroll: false,
			stop: function() {
				contentManager.rmePCR("mfheOverview", "-1", "pluginSave", [DeviceID, $j(this).data('plugin'), $j(this).css('top').replace('px', ''), $j(this).css('left').replace('px', '')]);
			}
		});
	},
			
	draggableStop: function(){
		$j('.desktopDraggable').removeClass('desktopMove').draggable("destroy");
	}
}

contentManager.rmePCR("mfheOverview", "-1", "checkAdmin", "", function(transport){
	if(transport.responseText == "0")
		contentManager.loadPlugin("contentScreen", "mfheOverview");
});

$j(window).ready(function(){
	fheOverview.init();
});

const requestWakeLock = async () => {
	try {
		fheOverview.wakeLock = await navigator.wakeLock.request();
		fheOverview.wakeLock.addEventListener('release', () => {
			console.log('Screen Wake Lock released:', fheOverview.wakeLock.released);
		});
		console.log('Screen Wake Lock released:', fheOverview.wakeLock.released);
	} catch (err) {
		console.error(`${err.name}, ${err.message}`);
	}
};

requestWakeLock();
document.addEventListener("visibilitychange", async () => {
  if (fheOverview.wakeLock !== null && document.visibilityState === "visible") 
    fheOverview.wakeLock = await navigator.wakeLock.request();
  
});