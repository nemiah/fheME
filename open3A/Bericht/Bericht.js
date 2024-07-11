/*
 *
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */


var Bericht = {
	
	save: function(klasse, formularID, formularID2){
		if(typeof formularID != "undefined") 
			form = $(formularID);
		else {
			formularID = "Bericht";
			form = $(formularID);
		}

		/*for(var i = 0;i<form.elements.length;i++) {
			if(form.elements[i].type != "hidden") 
				continue;

			if(form.elements[i].value <= 0) 
				form.elements[i].value = (i + 1) * -1;
			else 
				form.elements[i].value = i+1;
		}*/
		if(typeof formularID2 != "undefined") 
			formularID = [formularID, formularID2];

		saveClass(klasse, '', '', formularID);
	}
};

function saveBericht(klasse, formularID, formularID2){
	if(typeof formularID != "undefined") 
		form = $(formularID);
	else {
		formularID = "Bericht";
		form = $(formularID);
	}
	
	for(var i = 0;i<form.elements.length;i++) {
		if(form.elements[i].type != "hidden") 
			continue;
		
		if(form.elements[i].value <= 0) 
			form.elements[i].value = (i + 1) * -1;
		else 
			form.elements[i].value = i+1;
	}
	if(typeof formularID2 != "undefined") 
		formularID = [formularID, formularID2];
	
	saveClass(klasse, '', '', formularID);
}

function markAllBerichtK(){
	for(var i = 0;i<$('Bericht').elements.length;i++) {
		if($('Bericht').elements[i].type != "hidden") continue;
		checkVirtualBox($("image"+$('Bericht').elements[i].id), $('Bericht').elements[i].id, "true");
	}
}

function showHideBerichtOptions(show){
	var boxes = Array();
	boxes[0] = "Monat";
	boxes[1] = "Quartal";
	boxes[2] = "Jahr";
	
	if($(show).style.display == 'none') 
		new Effect.BlindDown(show);
	else
		new Effect.BlindUp(show);
		
	for(i=0;i<3;i++){
		if(show == boxes[i]) continue;
		new Effect.BlindUp(boxes[i]);
	}
}

function unmarkAllBerichtK(){
	for(var i = 0;i<$('Bericht').elements.length;i++) {
		if($('Bericht').elements[i].type != "hidden") continue;
		
		checkVirtualBox($("image"+$('Bericht').elements[i].id), $('Bericht').elements[i].id, "false");
	}
}