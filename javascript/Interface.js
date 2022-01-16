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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */

var Interface = {
	isDesktop: false,
	TabBarLast: null,
	TabBarLastTab: null,
	isLoading: false,
	mobileMaxWidth: 1000,
	BroadcastChannel: null,
	locale: 'de_DE',
	application: null,
	applicationLabel: null,
	
	mobile: function(){
		return $j(window).width() <= Interface.mobileMaxWidth;
	},
	
	init: function(){
		Interface.resizeWrapper();
		$j(window).on('resize', function() {
			Interface.resizeWrapper();
		});
		
		$j(window).on("online", function(){
			Interface.online();
		});
		
		$j(window).on("offline", function(){
			Interface.offline();
		});
	},
	
	offline: function(){
		Overlay.showDark();
		$j('body').append("<div id='offlineMessage' style='z-index:100000;color:white;font-size:40px;width:400px;position:absolute;'>Sie sind offline</div>");
		$j('#offlineMessage').css("top", ($j(window).height() - $j('#offlineMessage').outerHeight()) / 2);
		$j('#offlineMessage').css("left", ($j(window).width() - 400) / 2);
	},
	
	online: function(){
		Overlay.hideDark();
		$j('#offlineMessage').remove();
	},
	
	setup: function(callback){
		contentManager.rmePCR("Colors", "-1", "getInterface", "",  function(transport){
			contentManager.layout = transport.responseData.layout;
			$j('#interfaceLayout').prop('href', "./styles/standard/"+transport.responseData.layout+".css");
			$j('#interfaceColors').prop('href', "./styles/"+transport.responseData.colors+"/colors.css");
			$j('#interfaceHighContrast').prop('href', transport.responseData.highContrast);
			
			Interface.resizeWrapper();
			
			if(typeof callback != "undefined")
				callback();
			
			if(!contentManager.updateTitle)
				return;

			if(contentManager.layout != "desktop") 
				document.title = transport.responseData.title;
			else
				$j("#wrapperHandler").html(transport.responseData.title);
    	});
	},
	
	/**
	 * @deprecated text
	 */
	startWrapperDrag: function(){
		alert("Interface.startWrapperDrag is deprecated!")
		//$('wrapperTable').style.display = 'none';
	},
	
	/**
	 * @deprecated text
	 */
	stopWrapperDrag: function(){
		alert("Interface.stopWrapperDrag is deprecated!")
		//$('wrapperTable').style.display = '';
	},
	
	resizeWrapper: function() {
		if(contentManager.layout != "desktop"){
			$j('#wrapper').css("height", "auto").css("width", "auto");
			return;
		}
		
		size = Overlay.getPageSize(true);
		$j('#wrapper').css("height", ($j(window).height() - 230)+'px').css("width", (contentManager.maxWidth(true) - 250 - 50)+'px');
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
		/*if(Overlay.dark) return;
		
		if(Interface.isLoading) return;

		Interface.isLoading = true;
		Interface.showLoading();*/
	},

	endLoading: function(){
		//Interface.isLoading = false;
	},

	showLoading: function(){
		
	},
	
	notifyPermission: function(){
		if(typeof webkitNotifications == "object"){
			var perm = webkitNotifications.checkPermission();
			if(perm == 0)
				return "granted";
			
			if(perm == 1 || perm == 2)
				return "denied";
		}
		
		return Notification.permission;
	},
	
	notifyRequest: function(callback){
		Notification.requestPermission(function(perm){
			if(typeof callback == "function")
				callback(perm);
		});

	},
	
	notify: function(title, message, timeout){
		if(typeof Notification != "function")
			return;
		
		if(Interface.notifyPermission() != "granted")
			return;
		
		Interface.notifySend(title, message, timeout);
	},
			
	notifySend: function(title, message, timeout){
		if(typeof timeout == 'undefined')
			timeout = 5000;
		
		var N = new Notification(title, {
			body: message
		});
		
		if(typeof N.close == "function")
			setTimeout(function(){
				N.close();
			}, timeout);

	},
			
	frameStash: function(frame){
		$j("#"+frame).after("<div id=\""+frame+"New\"></div>");
		$j('#'+frame).data("frame", frame).data("plugin", contentManager.currentPlugin);
		$j('#stash').append($j('#'+frame).hide().attr("id", ""));
		$j("#"+frame+"New").attr("id", frame);
	},
			
	frameRestore: function(){
		if(!$j('#stash').children().length)
			return;

		contentManager.emptyFrame('contentLeft');
		contentManager.emptyFrame('contentRight');
		contentManager.emptyFrame('contentScreen');
		contentManager.emptyFrame('contentBelow');
				
		var frame = $j('#stash').children().last().data("frame");
		var plugin = $j('#stash').children().last().data("plugin");
		$j('#'+frame).replaceWith($j('#stash').children().last().attr("id", frame).show());
		
		contentManager.currentPlugin = plugin;
		
		if($(plugin+'MenuEntry'))
			Menu.setHighLight($(plugin+'MenuEntry'));
	},
	
	history: function(plugin, id = null){
		return; //disabled automatic generation for now because dashboard is not shown
		
		if(plugin === null && id !== null){
			var t = window.location.search.substring(2).split(",");
			t[2] = id;
			history.replaceState(null, null, './?:'+t.join(","));
			return;
		}
		
		if(id === null) {
			history.replaceState(null, null, './?:'+Interface.applicationLabel+','+plugin);
			return;
		}
		
		history.replaceState(null, null, './?:'+Interface.applicationLabel+','+plugin+','+id);
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
/*function showHideTBody(what, image){
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
}*/

function showMessage(message){
	if(!$("messenger")) {
		alert(message);
		return;
	}
	//createGrowl(message);
	$j('#messenger').html(message);
	$j('#messenger').fadeIn(100, function(){ $j(this).delay(1000).fadeOut(300); });
	
}

function focusMe(elem){
	$j(elem).addClass("hasFocus");
}

function blurMe(elem){
	$j(elem).removeClass("hasFocus");
}