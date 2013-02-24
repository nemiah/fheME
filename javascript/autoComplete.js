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
 
function htmlReplaces(string) {
	return string.replace("\#",";-;;raute;;-;").replace("'","_").replace("&",";-;;und;;-;").replace("%",";-;;prozent;;-;").replace("=",";-;;istgleich;;-;").replace("?",";-;;frage;;-;");
}
 
var ACDiv = null;
var selectedTR = null;
var ACMouseInBox = false;
var ACInputHasFocus = false;

var AC = {
	timer: null,

	makeFreeWindow: function(checkbox, random, field){
		if(checkbox.checked){
			if($(field)) $(field).value = "";
			$("ACDiv").id = "ACDiv"+random;
			checkbox.id = "cb_ACDiv"+random;
			selectedTR = null;
			$('ACHandler_'+random).style.cursor = "pointer";
			new Draggable($('ACDiv'+random), {handler: $('ACHandler_'+random)});
			$('ACTranslator').id = "ACTranslator"+random;
			ACInputHasFocus = false;
			ACMouseInBox = false;
		} else {
			$(checkbox.id.replace(/cb\_/g,"")).style.display = "none";
		}
	},
	
	selectByMouse: function(rowID){
		if(selectedTR != null) 
			selectedTR.className = "";
			
		selectedTR = $(rowID);
		
		AC.SetMouseIn();
	},
	
	SetMouseIn: function(){
		ACMouseInBox = true;
	},
	
	SetMouseOut: function(forField){
		ACMouseInBox = false;
		if(!ACInputHasFocus) AC.end(forField, true);
	},

	reloadChecker: function(transport){
		if(ACDiv.style.display == "none") Effect.Appear(ACDiv,{duration:0.4});
		$('ACDiv').update(transport.responseText);
		if($('AutoCompleteNumRows') && $('AutoCompleteNumRows').value == "1")
			AC.update(40);	
	},

	update: function(keyCode, forField, targetClass, mode){
		if(keyCode == 27) //ESCAPE
			AC.end(forField);
			
		if(keyCode == 16) return;
		if(keyCode == 20) return;
		
		if(keyCode == 40){
			if(!$('ACTranslator') || !$('autoCompleteTRId1_'+$('ACTranslator').value)) return;
			
			if(selectedTR == null) 
				selectedTR = $('autoCompleteTRId1_'+$('ACTranslator').value);
				
			else {
				if(selectedTR.nextSibling) {
					selectedTR.className = "";
					selectedTR = selectedTR.nextSibling;
				}
				else {
					selectedTR.className = "";
					selectedTR = $('autoCompleteTRId1_'+$('ACTranslator').value);
				}
			}
			selectedTR.className = "backgroundColor2";
			return;
		}
		
		if(keyCode == 38){
			if(!$('ACTranslator') || !$('autoCompleteTRId1_'+$('ACTranslator').value)) return;
			
			if(selectedTR == null) {
				selectedTR = $('autoCompleteTRId1_'+$('ACTranslator').value);
				while(selectedTR.nextSibling)
					selectedTR = selectedTR.nextSibling;
			}
			else {
				if(selectedTR.previousSibling) {
					selectedTR.className = "";
					selectedTR = selectedTR.previousSibling;
				}
				else {
					selectedTR.className = "";
					selectedTR = $('autoCompleteTRId1_'+$('ACTranslator').value);
					while(selectedTR.nextSibling)
						selectedTR = selectedTR.nextSibling;
				}
			}
			selectedTR.className = "backgroundColor2";
			return;
		}
		
		if(keyCode == 13){
			if(selectedTR == null) return;
			if(targetClass*1 != targetClass) targetClass = $('ACTranslator').value;
			sId = selectedTR.id.replace(/autoCompleteTRId/,"");
			fs = $("AutoCompleteFields_"+targetClass).value.split(", ");
			if($("doACJS"+fs[0]+"Id"+sId)){
				eval($("doACJS"+fs[0]+"Id"+sId).value);
				//selectedTR = null;  //Auskommentiert weil mehrmals klicken nicht gewirkt hat
				if($("cb_ACDiv"+targetClass) && $("cb_ACDiv"+targetClass).checked) return;
				ACDiv.style.display = "none";
				if(forField) forField.value = "";
				return;
			}
			
			for(i=0;i<fs.length;i++)
				$(fs[i]).value = $("autoComplete"+fs[i]+"Id"+sId).value;
	
			selectedTR = null;
			ACDiv.style.display="none";
			return;
		}
	
		if(forField.value == "") {
			ACDiv.style.display = "none";
			return;
		}
		
		if(AC.timer) clearTimeout(AC.timer);

		AC.timer = setTimeout(function(){
			AC.doRequest(forField, targetClass);
		},400);

		selectedTR = null;
	},

	doRequest: function(forField, targetClass){
		if(forField.value != "") 
			contentManager.rmePCR(targetClass, '', "getACHTML", [forField.id, htmlReplaces(forField.value)], AC.reloadChecker);
			/*new Ajax.Request('./interface/rme.php', {
			
			method:"get", 
			parameters:'class='+targetClass+"&method=getACHTML&constructor=''&parameters='"+forField.id+"','"+htmlReplaces(forField.value)+"'", 
			onSuccess: AC.reloadChecker});*/
	},
	
	start: function(forField){
		if($('ACDiv')) return;
		
		ACDiv = document.createElement("div");
		ACDiv.className = "AutoComplete backgroundColor0 borderColor1";
		ACDiv.id = "ACDiv";
		ACDiv.style.display="none";
		//if (navigator.appName.indexOf("Explorer") != -1) ACDiv.style.marginLeft="-327px";
		//if (navigator.appName.indexOf("Explorer") != -1) ACDiv.style.marginTop="21px";
		forField.parentNode.appendChild(ACDiv);
	},
	
	
	end: function(forField, force){
		if(typeof force == "undefined") force = false;
		if(ACMouseInBox && !force) return;
		
		ACMouseInBox = false;
		if($('ACDiv') && forField) forField.parentNode.removeChild(ACDiv);
	}
}

