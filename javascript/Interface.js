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

var Interface = {
	isDesktop: false,
	TabBarLast: null,
	TabBarLastTab: null,
	isLoading: false,

	init: function(){
		if($('wrapperHandler')){
			Interface.isDesktop = true;
			Interface.resizeWrapper();
			Event.observe(window, 'resize', function() {
				Interface.resizeWrapper();
			});
		}
	},
	
	startWrapperDrag: function(){
		$('wrapperTable').style.display = 'none';
	},
	
	stopWrapperDrag: function(){
		$('wrapperTable').style.display = '';
	},
	
	resizeWrapper: function() {
		size = Overlay.getPageSize(true);
		$('wrapper').style.height = (size[1] - 150)+'px';
	},
	
	translateStatusMessage: function(message, writeToFieldID){
		message = message.replace(/message:/,"");
		message = message.replace(/error:/,"");
		message = message.replace(/alert:/,"");

		eval("var mes = "+message+";");
		
		$(writeToFieldID).update(mes);
	},


	TabBarActivate: function(tab, boxID, targetClass){
		if(boxID == "id_null") return;
		
		if(Interface.TabBarLast != null && $(Interface.TabBarLast)){
			if($(Interface.TabBarLast).style.display == '')
				$(Interface.TabBarLast).style.display = 'none';
		}

		if($(boxID).style.display == 'none')
			$(boxID).style.display = '';

		if(Interface.TabBarLastTab != null)
			Interface.TabBarLastTab.className = '';
		tab.className = 'navBackgroundColor';

		Interface.TabBarLast = boxID;
		Interface.TabBarLastTab = tab;

	  //contentManager.rmePCR(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps)
		contentManager.rmePCR('mUserdata', '', 'setUserdata', ['TabBarLastTab'+targetClass,boxID]);
	},

	startLoading: function(){
		if(Overlay.dark) return;
		
		if(Interface.isLoading) return;

		Interface.isLoading = true;
		Interface.showLoading();
	},

	endLoading: function(){
		Interface.isLoading = false;
	},

	showLoading: function(){
		if(!$('busyBox') || !Interface.isLoading) return;

		//if(P2J)
		//	$j('busyBox').fadeTo(300, 0.3);//.delay(100).fadeTo(300, 1);
		else {
			Effect.Fade('busyBox', {duration: 0.3, from: 1, to: 0.3});
			Effect.Fade('busyBox', {duration: 0.3, from: 0.3, to: 1, delay: 0.1});
		}
		
		window.setTimeout("Interface.showLoading()", 800);
	}

}

function checkVirtualBox(image, targetFieldId, setValue){
	var s = image.src.search(/notok/);
	
	if(typeof setValue == "undefined") setValue = "null";

	if((s != -1 && setValue == "null") || setValue == "true"){
		image.src = image.src.replace(/notok/,"ok");
		$(targetFieldId).value = "1";
		return;
	} 
	if(s == -1 || setValue == "false"){
		image.src = image.src.replace(/ok/,"notok").replace(/notnot/,"not");
		$(targetFieldId).value = "0";
	}
}

/**
 * @deprecated since 27.11.2012
 */
function showHideTBody(what, image){
	alert("showHideTBody is DEPRECATED");
	
	while(what.tagName != "TABLE") what = what.parentNode;
	what = what.firstChild;
	while(what.tagName != "TBODY") what = what.nextSibling;
	
	if(what.style.display == "none") {
		what.style.display = "";
		//image.src = image.src.replace(/more/,"less");
	}
	else {
		what.style.display = "none";
		//image.src.replace(/less/,"more");
	}
}

function showMessage(message){
	if(!$("messenger")) {
		alert(message);
		return;
	}
	//createGrowl(message);
	$j('#messenger').html(message);
	$j('#messenger').fadeIn(100, function(){ $j(this).delay(1000).fadeOut(300); });
	/*new Effect.Move("messenger",{x:0, y:0, mode: 'absolute', duration: 0.2});
	new Effect.Move("messenger",{x:-210, y:0, mode: 'absolute', delay:2, duration: 0.2});*/
	
}

function focusMe(elem){
	$j(elem).addClass("hasFocus");
}

function blurMe(elem){
	$j(elem).removeClass("hasFocus");
}