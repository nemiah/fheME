/*
 *
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
/*
function loadWithHighlighter(folder){
	alert("hi");
	loadFrame('contentLeft','CIs','',folder);
	initHighlight($('contentLeft'));
}*/

/*function installTable(cl){
	new Ajax.Request("./interface/rme.php?class="+cl+"&construct=&method=createMyTable&parameters=''", {
	method: 'get',
	onSuccess: function(transport) {
    	$('contentLeft').update(transport.responseText);
    	
		contentManager.reloadFrame('contentRight');
	}});
}

function checkFields(plugin){
	new Ajax.Request("./interface/rme.php?class="+plugin+"&constructor=&method=checkMyTables&parameters=''", {
	method: 'get',
	onSuccess: function(transport) {
		if(transport.responseText == "-2") {
			showMessage("Plugin besitzt keine Tabelle.");
			return;
		}
		if(transport.responseText == "-1") showMessage("Es ist ein Fehler aufgetreten");
		if(transport.responseText == "0") {
			showMessage("kein Update notwendig.");
			return;
		}
		if(transport.responseText != "-1") showMessage(transport.responseText+" Feld"+(transport.responseText != "1" ? "er" : "")+" aktualisiert.");
	}});
}*/

var Installation = {
	reloadApp: function() {
		contentManager.rmePCR("Util", "-1", "reloadApplication", "", function() {
			location.reload(true);
		});
	}
}