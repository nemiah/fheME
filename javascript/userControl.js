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
 
 
var userControl = {
	certAutoLoginInterval: null,
	certAutoLoginCounter: 0,
	
	autoLoginInterval: null,
	autoLoginCounter: 0,
	usePWEncryption: true,
	
	changePassword: function(){
		if($j('#loginPassword').val() != ";;cookieData;;")
			$j('#loginSHAPassword').val(SHA1($j.trim($j('#loginPassword').val())));
		
		contentManager.rmePCR('Users', -1, 'changePassword', [
			$j('#loginUsername').val(), 
			$j('#loginSHAPassword').val(), 
			SHA1($j.trim($j('#newPassword1').val())), 
			SHA1($j.trim($j('#newPassword2').val()))
		],
		function(){ 
			$j('#newPassword1').val('');
			$j('#newPassword2').val('');
			$j('#loginPassword').val('');
			$j('.changePassword').hide();
		});
	},
	
	doLogin: function(){
		//userControl.abortAutoCertificateLogin();
		userControl.abortAutoLogin();
		//"+$('loginUsername').value+","+$('loginPassword').value+","+$('anwendung').value+"
		if($('loginPassword').value != ";;cookieData;;")
			$('loginSHAPassword').value = SHA1($j.trim($('loginPassword').value));
		
		if(!userControl.usePWEncryption)
			$('loginSHAPassword').value = $j.trim($('loginPassword').value);
		
		$('loginPassword').value = "";
		contentManager.rmePCR("Users", "", "doLogin", joinFormFieldsToString('loginForm'), function(transport) {
			if(!checkResponse(transport))
				return;
			
			if(transport.responseText == "") {
				alert("Fehler: Der Server antwortet nicht!");
				return;
			}
			
			if(transport.responseText == -2) {
				alert("Bitte verwenden Sie 'Admin' als Benutzer und Passwort!\nDiese Anwendung wurde noch nicht eingerichtet.");
				return;
			}
			
			if(transport.responseText == 0) {
				alert("Benutzername/Passwort falsch!\nBitte beachten Sie beim Passwort Groß-/Kleinschreibung.");
				return;
			}
			
			if(transport.responseText != 1 && transport.responseText != -2)
				alert(transport.responseText.replace(/<br \/>/ig,"\n").replace(/<b>/ig,"").replace(/<\/b>/ig,"").replace(/&gt;/ig,">"));

			

			//var a = new Date();
			//a = new Date(a.getTime() +1000*60*60*24*365);
			/*if($('saveLoginData').checked)
				document.cookie = 'userLoginData='+$('loginUsername').value+':'+$('loginSHAPassword').value+'; expires='+a.toGMTString()+';';
			else 
				document.cookie = 'userLoginData=--; expires=Thu, 01-Jan-70 00:00:01 GMT;';
			*/

			if($j('#saveLoginData').prop("checked"))
				$j.jStorage.set('phynxUserData', {
					"username": $j('#loginUsername').val(),
					"password": $j('#loginSHAPassword').val(),
					"application" : $j('#anwendung').val(),
					"autologin": $j('#doAutoLogin').prop("checked")});
			else
				$j.jStorage.deleteKey('phynxUserData');

			if(Interface.BroadcastChannel !== null)
				Interface.BroadcastChannel.postMessage("login");
			userControl.loadApplication();
		});
	},
	
	loadApplication: function(){
		contentManager.emptyFrame("contentScreen");
		Menu.loadMenu();
		contentManager.clearHistory();
	},
	
	saveCertificate: function(){
		if($('loginNewCertificate').value == ""){
			alert('Bitte geben Sie ein Zertifikat ein');
			return;
		}

		$j.jStorage.set('phynxUserCert', $('loginNewCertificate').value);
		location.reload();
	},
	
	doCertificateLogin: function(){
		var cert = $j.jStorage.get('phynxUserCert', null);
		
		if(cert == null){
			$j('#loginCertOptions').toggle();
			return;
		}
		contentManager.rmePCR("Users", "-1", "doCertificateLogin", [$('anwendung').value, $('loginSprache').value, cert], function(transport){
			if(transport.responseText == 0) {
				alert("Das Zertifikat ist ungültig.");
				$j('#loginCertOptions').toggle();
				return;
			}
			
			Menu.loadMenu();
			//DesktopLink.loadContent();
		}, "", true, function(){
			$j('#loginCertOptions').toggle();
		});
	},
	
	doWebAuthLogin: function(){
		WebAuth.checkregistration($('anwendung').value, function(transport){
			if(transport.responseText == 0) {
				alert("Anmeldung fehlgeschlagen!");
				return;
			}
			
			Menu.loadMenu();
		});
		
		/*contentManager.rmePCR("Users", "-1", "doCertificateLogin", [$('anwendung').value, $('loginSprache').value, cert], function(transport){
			if(transport.responseText == 0) {
				alert("Das Zertifikat ist ungültig.");
				$j('#loginCertOptions').toggle();
				return;
			}
			
			Menu.loadMenu();
			//DesktopLink.loadContent();
		}, "", true, function(){
			//$j('#loginCertOptions').toggle();
		});*/
	},
	
	abortAutoCertificateLogin: function(){
		if(userControl.certAutoLoginInterval != null)
			window.clearInterval(userControl.certAutoLoginInterval);
		
		userControl.certAutoLoginInterval = null;
		if($('countdownCertificateLogin'))
			$('countdownCertificateLogin').update("");
	},
	
	abortAutoLogin: function(){
		if(userControl.autoLoginInterval != null)
			window.clearInterval(userControl.autoLoginInterval);
		
		userControl.autoLoginInterval = null;
		if($('countdownCertificateLogin'))
			$('countdownCertificateLogin').update("");
	},
	
	autoLogin: function(){
		userControl.autoLoginCounter = 3;
		
		userControl.autoLoginInterval = window.setInterval(function(){
			if(userControl.autoLoginCounter == 0){
				window.clearInterval(userControl.autoLoginInterval);
				userControl.autoLoginInterval = null;
				
				userControl.doLogin();
				$('countdownCertificateLogin').update("");
				
				return;
			}
			
			$('countdownCertificateLogin').update(userControl.autoLoginCounter);
			userControl.autoLoginCounter--;
		}, 1000);
	},
	
	autoCertificateLogin: function(){
		userControl.certAutoLoginCounter = 3;
		return; //disabled 11.1.2012 because it will re-login the current user
		
		/*userControl.certAutoLoginInterval = window.setInterval(function(){
			if(userControl.certAutoLoginCounter == 0){
				window.clearInterval(userControl.certAutoLoginInterval);
				userControl.certAutoLoginInterval = null;
				
				userControl.doCertificateLogin();
				$('countdownCertificateLogin').update("");
				
				return;
			}
			
			$('countdownCertificateLogin').update(userControl.certAutoLoginCounter);
			userControl.certAutoLoginCounter--;
		}, 1000);*/
	},
	
	doTestLogin: function(){
		$j("#messageSetup").hide();
		contentManager.rmePCR("Users", "", "doLogin", "%3B-%3B%3Bund%3B%3B-%3BloginUsername%3B-%3B%3Bistgleich%3B%3B-%3B000000000000001%3B-%3B%3Bund%3B%3B-%3BloginSHAPassword%3B-%3B%3Bistgleich%3B%3B-%3B0%3B-%3B%3Bund%3B%3B-%3Banwendung%3B-%3B%3Bistgleich%3B%3B-%3B0%3B-%3B%3Bund%3B%3B-%3BsaveLoginData%3B-%3B%3Bistgleich%3B%3B-%3B0", function(transport) {
			if(transport.responseText == "") {
				alert("Fehler: Server antwortet nicht!");
				return;
			}
			if(transport.responseText == -2) {
				//$j('#loginPassword').val("Admin");
				$j('#loginUsername').val("Admin");
				
				//alert("Bitte verwenden Sie 'Admin' als Benutzer und Passwort!\nDiese Anwendung wurde noch nicht eingerichtet.");
				$j("#messageSetup").show();
				//userControl.doLogin();
			}
		});
	},
	
	doLogout: function(redirect, propagate){
		if(typeof propagate == "undefined")
			propagate = true;
			
		contentManager.rmePCR("Users", "", "doLogout", "", function() {
			Popup.closeNonPersistent();
			Popup.closePersistent();
			contentManager.clearHistory();
			Menu.loadMenu();
			contentManager.contentBelow("");
			if(typeof redirect != "undefined" && redirect != "") document.location.href= redirect;
			
			if(propagate && Interface.BroadcastChannel !== null)
				Interface.BroadcastChannel.postMessage("logout");
		});
	}
}