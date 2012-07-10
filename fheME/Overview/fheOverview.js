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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

var fheOverview = {
	updater: null,
	targets: null,
	counter: 0,
	delays: null,
	replaceUpdateMethods: null,
	months: new Array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"),
	days: new Array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"),
	
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
		
		for (var i = 0; i < classes.length; i++)
			fheOverview.loadContent(classes[i]);
		
		fheOverview.updater = window.setInterval(function(){
			if(!$('onfheOverviewPage')){
				window.clearInterval(fheOverview.updater);
				fheOverview.updater = null;
				return;
			}
			
			var jetzt = new Date();
			$('fheOverviewClock').update("<span>"+fheOverview.days[jetzt.getDay()]+",<br /><b>"+jetzt.getDate()+". "+fheOverview.months[jetzt.getMonth()]+" "+jetzt.getFullYear()+"</b></span><b>"+jetzt.getHours()+":"+(jetzt.getMinutes() < 10 ? "0" : "")+jetzt.getMinutes()+"</b>");
			
			for(var j = 0; j < fheOverview.delays.length; j++){
				if(fheOverview.delays[j] == 0)
					continue;
				
				if(fheOverview.counter > 0 && fheOverview.counter % fheOverview.delays[j] == 0)
					if(fheOverview.replaceUpdateMethods[j] == null)
						fheOverview.loadContent(fheOverview.targets[j]);
					else
						fheOverview.replaceUpdateMethods[j]();
			}
			
			fheOverview.counter++;
			if(fheOverview.counter > 1000000)
				fheOverview.counter = 1;
		}, 1000);
	},
	
	loadContent: function(target){
		var method = target.split("::");
		target = target.replace("::", "_");
		contentManager.rmePCR(method[0].replace("GUI", ""), -1, method[1], "1", function(transport){
			if(!$('fheOverviewContent'+target))
				return;//console.log('fheOverviewContent'+target+" is NULL!");
			$('fheOverviewContent'+target).update(transport.responseText);
			//console.log(fheOverview.counter+":"+target);
			fheOverview.updateTime(method[0]);
		});
	},
	
	updateTime: function(target){
		if($('lastUpdate'+target)){
			var jetzt = new Date();

			$('lastUpdate'+target).update(jetzt.getHours()+":"+(jetzt.getMinutes() < 10 ? "0" : "")+jetzt.getMinutes());
		}
	}
}

contentManager.loadPlugin("contentScreen", "mfheOverview");

$j(window).ready(function(){
	$j('#footer').hide();
});