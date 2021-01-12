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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
 
var Overlay = {
	isInit: false,
	white: false,
	loginBox: false,
	initWhite: false,
	dark: false,
	
	init: function(){
		$j(window).resize(Overlay.fit);
		
		Overlay.isInit = true;
		Overlay.fit();
		//new Effect.Appear($('lightOverlay'), {duration: 0.5});
		$j('#lightOverlay').fadeIn(500);
		Overlay.initWhite = true;
		
		setTimeout("$('container').style.display = 'block'",500);
	},
	
	show: function(){
		if(!Overlay.loginBox) {
			$j('#boxInOverlay').fadeIn(200);
			//new Effect.Appear("boxInOverlay", {duration: 0.2});
			Overlay.loginBox = true;
			
			setTimeout("$('loginUsername').focus()",300);
			var data = $j.jStorage.get('phynxUserData', null);
			//var data = cookieManager.getCookie('userLoginData');
			if(data != null) {
				//data = data.split(":");
				$j('#loginUsername').val(data.username);
				$j('#loginPassword').val(";;cookieData;;");
				$j('#loginSHAPassword').val(data.password);
				$j('#saveLoginData').prop("checked", true);
				
				if(typeof Util.querySt('application') == 'undefined')
					$j('#anwendung').val(data.application);
				
				$j('#doAutoLogin').prop("checked", data.autologin);
				
				$j('#doAutoLoginContainer').fadeIn();
				
				if(data.autologin)
					userControl.autoLogin();
			} else
				$j('#doAutoLoginContainer').fadeOut();
			
			if(!$j.jStorage.storageAvailable())
				$j('#saveLoginDataContainer').hide();
		}
		if(!Overlay.white && !Overlay.initWhite) {
			$j('#lightOverlay').fadeIn(500);
			//new Effect.Appear($('lightOverlay'), {duration: 0.5});
			Overlay.white = true;
			
			setTimeout("$('contentLeft').update('')",600);
			setTimeout("$('contentRight').update('')",600);
			
			
		}
	},

	showDark: function(duration, opacityTo){
		if(Overlay.dark) return;
		
		if(typeof duration == "undefined")
			duration = 0.1;
		if(typeof opacityTo == "undefined")
			opacityTo = 0.8;
		
		$j('#darkOverlay').fadeTo(duration * 1000 , opacityTo);
		//new Effect.Appear($('darkOverlay'), {duration: duration, to: opacityTo});
		Overlay.dark = true;
	},

	hideDark: function(duration){
		if(!Overlay.dark) return;

		if(typeof duration == "undefined")
			duration = 0.1;

		$j('#darkOverlay').fadeOut();
		Overlay.dark = false;
	},

	hide: function(){

		if(Overlay.initWhite) {
			setTimeout(function(){
				$j('#lightOverlay').fadeOut();
			},600);
			Overlay.initWhite = false;
		}

		if(Overlay.white) {
			$j('#lightOverlay').fadeOut();
			Overlay.white = false;
		}
		if(Overlay.loginBox) {
			$j('#boxInOverlay').fadeOut();
			Overlay.loginBox = false;
		}
	},
	
	fit: function(){
	
		var size = Overlay.getPageSize();
		$('lightOverlay').style.width = size[0]+'px';
		$('lightOverlay').style.height = size[1]+'px';

		$('darkOverlay').style.width = size[0]+'px';
		$('darkOverlay').style.height = size[1]+'px';

		$('boxInOverlay').style.top = "20%";
		$('boxInOverlay').style.left = (size[0]/2 - 200)+"px";
	},
	
    getPageSize: function(windowSize) {
	        
	     var xScroll, yScroll;
		
		if (window.innerHeight && window.scrollMaxY) {	
			xScroll = window.innerWidth + window.scrollMaxX;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}
		
		var windowWidth, windowHeight;
		
		if (self.innerHeight) {	// all except Explorer
			if(document.documentElement.clientWidth){
				windowWidth = document.documentElement.clientWidth; 
			} else {
				windowWidth = self.innerWidth;
			}
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}	
		
		if(typeof windowSize != "undefined" && windowSize) return [windowWidth,windowHeight];
		
		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else { 
			pageHeight = yScroll;
		}
	
		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){	
			pageWidth = xScroll;		
		} else {
			pageWidth = windowWidth;
		}

		return [pageWidth,pageHeight];
	}
}