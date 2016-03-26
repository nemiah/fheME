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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

/**var cookieManager = {
	getCookie: function(cookieName) {
		var cs = document.cookie.split("; ");
		for(var i = 0;i<cs.length;i++){
			var subcs = cs[i].split("=");
			if(subcs[0] == cookieName) return subcs[1];
		}
		
		return -1;
	}
}*/

Ajax.Responders.register({
	onCreate: function(){
		Interface.startLoading();
	},

	onFailure: function(transport) {
		//console.log(transport);
		showMessage("<b style=\"color:red\">Server nicht<br />erreichbar</b>");
		Interface.endLoading();
		$j('.loading').removeClass("loading");
		//alert("An error occured: "+transport);
	},
	
	onComplete: function(){
		Interface.endLoading();
		$j('.loading').removeClass("loading");
	}
});

function checkResponse(transport, hideError) {
	if(typeof hideError == "undefined") hideError = false;
	
	var response = transport.responseText;
	if(response.charAt(0) == "{" && response.charAt(response.length - 1) == "}"){
		var obj = jQuery.parseJSON(response);
		if(obj.type)
			response = obj.type+":'"+obj[obj.type]+"'";
		else
			return true;
	}

	if(response == "SESSION EXPIRED"){
		alert("Ihre Sitzung ist abgelaufen, bitte loggen Sie sich erneut ein.");
		Menu.onTimeout();
		return false;
	}
	
	if(response.search(/^redirect:/) > -1){
		eval(response.replace(/redirect:/,""));
		return false;
	}
	
	if(response.search(/^error:/) > -1){
		eval("var message = "+response.replace(/error:/,""));
		alert("Es ist ein Fehler aufgetreten:\n"+message);
		//alert("Es ist ein Fehler aufgetreten:\n"+response.replace(/error:/,""));
		return false;
	}
	if(response.search(/^alert:/) > -1){
		eval("var message = "+response.replace(/alert:/,""));
		alert(message);
		return false;
	}
	if(response.search(/^message:/) > -1){
		eval("var message = "+response.replace(/message:/,""));
		
		if(navigator && navigator.platform != "iPod" && navigator.platform != "iPhone") showMessage(message);
		else alert(message);
		return true;
	}
	if(response.search(/^\s*Fatal error/) > -1 || response.search(/^\s*Parse error/) > -1 || response.search(/^<br \/>\s*<b>Fatal error<\/b>/) > -1){
		if(!hideError) {
			//alert(response.replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, ''));
			
			Popup.load("Fehler", "Support", -1, "fatalError", [response+"", Ajax.lastRequest+""], "", "edit", "{width: 600, blackout: true, hPosition: 'center', top:30}");
			/*contentManager.rmePCR("Util", "-1", "fatalError", response, function(transport){
				Popup.load();
				Popup.create("error", "display", "Es ist ein Fehler aufgetreten", );
				Popup.update(r, "error", "display", false);
			});*/
			
			/*var r = {
				responseText: "<pre style=\"padding:5px;font-size:10px;max-size:800px;overflow:auto;\">"+response.replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, '')+"</pre>"
			};*/
			
		}
		return false;
	}
	if(response.search(/^\s*FPDF error:/) > -1){
		alert(response.replace("FPDF error:","").replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/<code>/g,"").replace(/<\/code>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, ''));
		return false;
	}
	
	return true;
}

/**
 * @deprecated
 **/
function rme(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps){
	//alert("JS function rme() deprecated, use contentManager.rmePCR instead!");
 	if(typeof targetMethodParameters != "string"){
 		for(var i=0;i<targetMethodParameters.length;i++)
 			targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
 
 	new Ajax.Request("./interface/rme.php?class="+targetClass+"&constructor="+targetClassId+"&method="+targetMethod+"&parameters="+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : "")+"&rand="+Math.random(), {
	method: 'get',
	onSuccess: function(transport) {
		if(onSuccessFunction) eval(onSuccessFunction);
	}});
}

/**
 * @deprecated
 **/
function rmeP(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps){
	//alert("JS function rmeP() deprecated, use contentManager.rmePCR instead!");
 	if(typeof targetMethodParameters != "string"){
 		for(var i = 0; i < targetMethodParameters.length; i++)
 			targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
 
 	new Ajax.Request("./interface/rme.php?rand="+Math.random(), {
	method: 'post',
	parameters: "class="+targetClass+"&construct="+targetClassId+"&method="+targetMethod+"&parameters="+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""),
	onSuccess: function(transport) {
		if(onSuccessFunction) eval(onSuccessFunction);
	}});
 }
 

function windowWithRmeP(targetClass, targetClassId, targetMethod, targetMethodParameters, bps, target){
	if(typeof target == "undefined")
		target = "window";

	var win = window.open("",'Druckansicht','height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes');
	
 	if(typeof targetMethodParameters != "string"){
 		for(var i=0;i<targetMethodParameters.length;i++)
 			targetMethodParameters[i] = "'"+targetMethodParameters[i]+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
	
	var form = document.createElement("form");
	form.action = contentManager.getRoot()+'interface/rme.php';
	form.method = "POST";
	form.target = "Druckansicht";
	
	var input = document.createElement("input");
	input.name = "class";
	input.value = targetClass;
	form.appendChild(input);
	
	input = document.createElement("input");
	input.name = "constructor";
	input.value = targetClassId;
	form.appendChild(input);
	
	input = document.createElement("input");
	input.name = "method";
	input.value = targetMethod;
	form.appendChild(input);
		
	input = document.createElement("textarea");
	input.name = "parameters";
	input.value = targetMethodParameters;
	form.appendChild(input);
	
	if(bps != "" && typeof bps != "undefined"){
		input = document.createElement("input");
		input.name = "bps";
		input.value = targetMethodParameters;
		form.appendChild(input);
	}
	
	if(Ajax.physion != "default"){
		input = document.createElement("input");
		input.name = "physion";
		input.value = Ajax.physion;
		form.appendChild(input);
	}
	
	form.style.display = 'none';
	document.body.appendChild(form);
	form.submit();
	document.body.removeChild(form);
	win.focus();
}

function windowWithRme(targetClass, targetClassId, targetMethod, targetMethodParameters, bps, target, windowOptions){
	var height = 650;
	var width = 875;
	var left = 20;
	var top = 20;
	var name = 'Druckansicht';
	var scroll = true;
	if(typeof windowOptions != "undefined"){
		if(windowOptions.height)
			height = windowOptions.height;
		
		if(windowOptions.width)
			width = windowOptions.width;
		
		if(windowOptions.left)
			left = windowOptions.left;
		
		if(windowOptions.top)
			top = windowOptions.top;
		
		if(windowOptions.name)
			name = windowOptions.name;
		
		if(typeof windowOptions.scroll !== "undefined")
			scroll = windowOptions.scroll;
	}
	
	var options = 'height='+height+',width='+width+',left='+left+',top='+top+',scrollbars='+(scroll ? "yes" : "no")+',resizable=yes';
	
	if(typeof target == "undefined")
		target = "window";

 	if(typeof targetMethodParameters != "string"){
 		for(var i=0;i<targetMethodParameters.length;i++)
 			targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
	
 	if(target == "window"){
		var win = window.open(contentManager.getRoot()+'interface/rme.php?class='+targetClass+'&constructor='+encodeURIComponent(targetClassId)+'&method='+targetMethod+'&parameters='+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : "")+"&r="+Math.random()+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""),name,options);
		win.focus();
		
		return win;
	}
	
	if(target == "tab")
		window.open(contentManager.getRoot()+'interface/rme.php?class='+targetClass+'&constructor='+encodeURIComponent(targetClassId)+'&method='+targetMethod+'&parameters='+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : "")+"&r="+Math.random()+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""));
}


function saveClass(className, id, onSuccessFunction, formName, callback){
	var formID = "AjaxForm";
	if(formName) 
		formID = formName;
	
	var check = formID;
	if(typeof formID != "string")
		check = formID[0];
	
	if(!$(check))
		alert("Kein Formular gefunden!");
	
	if($(check).elements.length == 0) 
		alert("Keine Daten zum Speichern gefunden!");
	
	var dots = ".";
	if(document.location.pathname.search(/interface/) > -1) dots = "..";
	
	setString = dots+"/interface/set.php?random="+Math.random();

	new Ajax.Request(setString, {
	method: 'post',
	parameters: "class="+className+joinFormFields(formID)+"&id="+id,
	onSuccess: function(transport) {
		if(checkResponse(transport)) {
			//showMessage(transport.responseText);
			if(typeof formID == "string")
				$j('#'+formID+" .recentlyChanged").removeClass("recentlyChanged");
			else {
				for(var i = 0; i < formID.length; i++)
					$j('#'+formID[i]+" .recentlyChanged").removeClass("recentlyChanged");
			}
			
			if(typeof onSuccessFunction == "function")
				onSuccessFunction(transport);
			
			if(typeof callback == "function")
				callback(id);
		}
	}});
}

function joinFormFields(formIDs){
	setString = "";
	if(typeof formIDs == "string")
		formIDs = [formIDs];
	
	for(var j = 0; j < formIDs.length; j++){
		formID = formIDs[j];
		for(var i = 0;i < $(formID).elements.length;i++) {
			if($(formID).elements[i].type == "button") continue;
			//if($(formID).elements[i].type == "password" && $(formID).elements[i].value == "") continue;

			if($(formID).elements[i].type == "radio"){
				if($(formID).elements[i].checked) setString += "&"+$(formID).elements[i].name+"="+encodeURIComponent($(formID).elements[i].value);
			} else if($(formID).elements[i].type == "checkbox"){
				if($(formID).elements[i].checked) setString += "&"+$(formID).elements[i].name+"=1";
				else setString += "&"+$(formID).elements[i].name+"=0";
			} else if($(formID).elements[i].type == "select-multiple"){
				setString += "&"+$(formID).elements[i].name+"=";
				subString = "";
				for(j = 0; j < $(formID).elements[i].length; j++)
					if($(formID).elements[i].options[j].selected) subString += (subString != "" ? ";:;" : "")+$(formID).elements[i].options[j].value;

				setString += subString;

			} else setString += "&"+$(formID).elements[i].name+"="+encodeURIComponent($(formID).elements[i].value);
		}
	}
	return setString;
}

function joinFormFieldsToString(formID){
	var get = joinFormFields(formID);
	
	get = get.replace(/&/g,";-u-;").replace(/=/g,";-i-;").replace(/#/g,";-r-;").replace(/\?/g,";-f-;").replace(/%/g,";-p-;");
	
	return get;
}

/**
 * @deprecated
 **/
function loadLeftFrameV2(plugin, withId, onSuccessFunction){
	//alert("JS function loadLeftFrameV2() deprecated, use contentManager.reloadFrame instead!");

	contentManager.loadFrame("contentLeft", plugin, withId, 0, "bps", onSuccessFunction);
}



function deleteClass(className, id, onSuccessFunction, question){
	Check = confirm(question);
	if (Check == false) return;

	contentManager.rmePCR(className, id, "deleteMe", "", onSuccessFunction);
}


function saveSelection(classe, classId, saveFunction, idToSave, targetFrame, targetClass, targetId){
	new Ajax.Request("./interface/rme.php", {
	method: 'post',
	parameters: "class="+classe+"&construct="+classId+"&method="+saveFunction+"&parameters='"+idToSave+"'",
	onSuccess: function(transport) {
		if(checkResponse(transport)){
			if(transport.responseText.search(/^message:/) == -1)showMessage(transport.responseText);

			if(targetId != -1) contentManager.loadFrame(targetFrame, targetClass, targetId);
		}
	}});

}

function saveMultiEditInput(classe, eid, feld, onsuccessFunction){
	oldValue = $(feld+'ID'+eid).value;
	var field = $(feld+'ID'+eid);
	
	var value = field.value;
	if(field.type == "checkbox")
		value = field.checked ? "1" : "0";
	
	new Ajax.Request("./interface/rme.php?class="+classe+"&constructor="+eid+"&method=saveMultiEditField&parameters="+encodeURIComponent("'"+feld+"','"+value+"'"), {
	method: 'get',
	onSuccess: function(transport) {
		if(checkResponse(transport)){
			//if(transport.responseText.search(/^message:/) == -1) showMessage(transport.responseText);

			if(transport.responseText.charAt(0) == "{" && transport.responseText.charAt(transport.responseText.length - 1) == "}")
				transport.responseData = jQuery.parseJSON(transport.responseText);
			
	
			if(typeof onsuccessFunction != "undefined" && onsuccessFunction != "")
				onsuccessFunction(transport);
		}
	}});
}