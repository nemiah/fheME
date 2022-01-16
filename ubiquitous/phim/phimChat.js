/**
 *
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */

var phimChat = {
	server: null,
	UserID: null,
	//c: null,
	realm: "phim",
	groups: [],
	root: "",
	instance: null,
	session: null,
	zindex: 1000,
	sessions: [],
	authKey: null,
	
	writingTimeout: [],
	
    onlyEmoji: function(string) {
		return (string.match(/^\p{RI}\p{RI}|\p{Emoji}(\p{EMod}+|\u{FE0F}\u{20E3}?|[\u{E0020}-\u{E007E}]+\u{E007F})?(\u{200D}\p{Emoji}(\p{EMod}+|\u{FE0F}\u{20E3}?|[\u{E0020}-\u{E007E}]+\u{E007F})?)+|\p{EPres}(\p{EMod}+|\u{FE0F}\u{20E3}?|[\u{E0020}-\u{E007E}]+\u{E007F})?|\p{Emoji}(\p{EMod}+|\u{FE0F}\u{20E3}?|[\u{E0020}-\u{E007E}]+\u{E007F})$/gu) !== null && string.length == 2);
    },
	
	init: function(server, realm, instance, UserID, UserName, authKey, groups, showNotification){
		$j("#offlineOverlay").css("height", $j(window).height()).css("width", $j(window).width());
		
		phimChat.server = server;
		phimChat.UserID = UserID;
		phimChat.UserName = UserName;
		phimChat.realm = realm;
		phimChat.authKey = authKey;
		phimChat.groups = groups;
		phimChat.instance = instance;
		phimChat.showNotification = showNotification;
		
		phimChat.overlayShow();
		
		phimChat.connection();
	},
	
	time: function(){
		const options = {
			year: "numeric",
			month:"2-digit",
			day:"2-digit"
		};
		
		const optionsT = {
			hour:  "2-digit",
			minute: "2-digit"
		};
		
		return new Date().toLocaleDateString(Interface.locale.replace("_", "-"), options)+" "+new Date().toLocaleTimeString(Interface.locale.replace("_", "-"), optionsT);
	},
	
	writing: function(to){
		phimChat.session.publish("it.furtmeier."+phimChat.instance+".phim_"+to, ['{"method":"writing", "user": '+phimChat.UserID+', "fromUser": "'+phimChat.UserName+'"}']);
	},
	
	markRead: function(to) {
		$j('#chatText'+to+' .highlight').removeClass('highlight');
	},
	
	hideWindow: function(target, sync = true){
		if(!sync){
			$j('#chatWindow'+target).hide();
			return;
		}
		
		contentManager.rmePCR('mphim', -1, 'windowStatus', [target, 'hidden'], function(){ $j('#chatWindow'+target).hide(); });
		
		phimChat.session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"syncWindowHide", "user": '+phimChat.UserID+', "to": "'+target+'", "session": '+phimChat.session._id+'}']);
	},
	
	send: function(to, field){
		phimChat.markRead(to);
		contentManager.rmePCR('phim', -1, 'setRead', [to], function(){}, '', 1, function(){});
		phimChat.updateUnread();
		
		phimChat.session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"syncRead", "user": '+phimChat.UserID+', "to": "'+to+'", "session": '+phimChat.session._id+'}']);
		
		if(field.val().trim() === ""){
			field.val('');
			return;
		}
		
		//if(to > 0)
		//	$j('#chatText'+to).append("<div class=\"chatMessage\"><span class=\"time\">"+phimChat.time()+"</span><span class=\"username\">"+phimChat.UserName+": </span>"+field.val()+"</div>");
		
		contentManager.rmePCR('phim', -1, 'sendMessage', [to, field.val(), phimChat.session._id], function(t){ field.val(''); if(to > 0) $j('#chatText'+to).append(t.responseText); phimChat.scroll('chatText'+to); }, '', 1);
	},
	
	draggable: function(){
		$j(".chatWindow").draggable({ 
			handle: ".chatHeader",
			stop: function(event, ui){
				contentManager.rmePCR("mphim", -1, "windowPosition", [ui.helper[0].id.replace("chatWindow", ""), ui.position.left, ui.position.top]);
			},
			start: function(event, ui){
				$j(ui.helper[0]).css("z-index", phimChat.zindex++);
			},
			grid: [10, 10]
		}).on("click", function(event) { 
			$j(this).css("z-index", phimChat.zindex++);
		});
	},
	
	oneventG: function(args) {
		var data = jQuery.parseJSON(args[0]);

		if(data.method == "message")
			phimChat.newMessage(data);
	},
	
	connection: function(){
		var connection = new autobahn.Connection({
			url: phimChat.server, 
			realm: phimChat.realm,
			authmethods: ["wampcra"],
			authid: "client",
			onchallenge: function(session, method, extra){
				if (method !== 'wampcra') 
					return false;
				
				return autobahn.auth_cra.sign(phimChat.authKey, extra.challenge);
			}
		});

		connection.onopen = function (session, details) {
			phimChat.session = session;
			phimChat.overlayHide();
			
			session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"online", "user": '+phimChat.UserID+', "session": '+session._id+'}']);
			
			function onevent(args) {
				var data = jQuery.parseJSON(args[0]);

				if(data.method == "message")
					phimChat.newMessage(data);
				
				if(data.method == "read")
					$j('.isread_'+data.from).show();
				
				if(data.method == "writing"){
					if($j('#writing'+data.user).length)
						return;
					
					if(typeof phimChat.writingTimeout[data.user] != "undefined" && phimChat.writingTimeout[data.user] !== null)
						window.clearTimeout(phimChat.writingTimeout[data.user]);
					
					$j('#chatText'+data.user).append('<div id="writing'+data.user+'" class="chatMessage"><span class="time">&nbsp;</span><span class="username">'+data.fromUser+': </span>…</div>');
					phimChat.writingTimeout[data.user] = window.setTimeout(function(){
						window.clearTimeout(phimChat.writingTimeout[data.user]);
						$j('#writing'+data.user).remove();
					}, 4000);
					
					phimChat.scroll('chatText'+data.user);
				}
			}
			
		   
			function oneventWatchdog(args) {
				var data = jQuery.parseJSON(args[0]);
				
				if(data.method == "syncRead" && data.user == phimChat.UserID)
					phimChat.markRead(data.to);
				
				if(data.method == "syncWindowHide" && data.user == phimChat.UserID)
					phimChat.hideWindow(data.to, false);
				
				if(data.method == "syncWindowShow" && data.user == phimChat.UserID)
					phimChat.openWindow(data.to, false);
				
				
				if(data.method == "newMessage"){
					if(data.to.substring(0, 1) != "g") {//user windows already preloaded!
						if(data.from == phimChat.UserID && data.session != phimChat.session._id)
							phimChat.reloadWindow(data.to);
						
						return;
					}
					
					if($j('#chatWindow'+data.to).length > 0)
						return;
					
					phimChat.openWindow(data.to, false);
					//phimChat.playSound();
					return;
				}
				
				if(data.method == "online"){
					phimChat.online(data);
					return;
				}
				
				if(data.method == "discover"){
					session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"online", "user": '+phimChat.UserID+', "session": '+session._id+'}']);
					return;
				}
			}
		   
			/*function onevent0(args) {
				var data = jQuery.parseJSON(args[0]);
				
				$j('#chatText0').append("<div class=\"chatMessage highlight\"><span class=\"username\">"+data.fromUser+": </span>"+data.content+"</div>");
				if($j('#channel').val() != 0)
					$j('#user0').addClass('highlight');

				if(data.from != phimChat.UserID)
					phimChat.playSound();
				
				phimChat.scroll('chatText0');
			}
*/
			//session.subscribe('it.furtmeier.'+phimChat.instance+'.phim_0', onevent0);
			session.subscribe('it.furtmeier.'+phimChat.instance+'.phim_Watchdog', oneventWatchdog);
			session.subscribe('it.furtmeier.'+phimChat.instance+'.phim_'+phimChat.UserID, onevent);
		
			
			function updateL(args) {
				phimChat.offline(phimChat.sessions[args[0]]);
			}

			//session.subscribe('wamp.session.on_join', updateJ);
			session.subscribe('wamp.session.on_leave', updateL);
				
			for(var i = 0; i < phimChat.groups.length; i++)
				session.subscribe('it.furtmeier.'+phimChat.instance+'.phim_g'+phimChat.groups[i], phimChat.oneventG);
			
			session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"discover"}']);
		};

		connection.onclose = function(reason){
			$j('#userList .online').hide();
			$j('#userList .offline').show();
			
			phimChat.overlayShow();
			
			//console.warn('WebSocket connection closed: '+reason);	
		}

		connection.open();
	},
	
	reloadWindow: function(target){
		contentManager.rmePCR("mphim", -1, "chatPopup", [target, "1", "1"], function(t){
			
			$j('#chatWindow'+target).replaceWith(t.responseText);
			
			phimChat.draggable();
			phimChat.scroll('chatText'+target);
			emojiPicker.init('newMessageTA'+target);
		});
		
	},
	
	openWindow: function(target, sync = true){
		if($j('#chatWindow'+target).length > 0){
			contentManager.rmePCR("mphim", -1, "windowStatus", [target, "visible"], function(t){
				$j('#chatWindow'+target).show();
			});
			
			if(sync)
				phimChat.session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"syncWindowShow", "user": '+phimChat.UserID+', "to": "'+target+'", "session": '+phimChat.session._id+'}']);
			
			return;
		}
		
		contentManager.rmePCR("mphim", -1, "chatPopup", [target, "1", "1"], function(t){
			if($j('#chatWindow'+target).length > 0)
				return;
			
			$j(t.responseText).appendTo("#contentScreen");
			phimChat.session.subscribe('it.furtmeier.'+phimChat.instance+'.phim_'+target, phimChat.oneventG);
			phimChat.draggable();
			emojiPicker.init('newMessageTA'+target);
			
			phimChat.playSound();
		
			if(sync)
				phimChat.session.publish("it.furtmeier."+phimChat.instance+".phim_Watchdog", ['{"method":"syncWindowShow", "user": '+phimChat.UserID+', "to": "'+target+'", "session": '+phimChat.session._id+'}']);
		});
	},
	
	overlayShow: function(){
		$j("#offlineOverlay").css("height", $j(window).height()).css("width", $j(window).width());
		$j("#offlineOverlay").fadeIn();

		$j('#offlineOverlay').html("<div id='offlineMessage' style='display:inline-block;z-index:100000;color:white;font-size:40px;width:400px;position:absolute;'>Sie sind offline</div>");
		$j('#offlineMessage').css("top", ($j(window).height() - $j('#offlineMessage').outerHeight()) / 2);
		$j('#offlineMessage').css("left", ($j(window).width() - $j('#offlineMessage').outerWidth()) / 2);
	},
	
	overlayHide: function(){
		$j("#offlineOverlay").fadeOut();
		$j('#offlineMessage').remove();
	},
	
	
	online: function(data){
		phimChat.sessions[data.session] = data.user;
		
		$j(".online_"+data.user).show();
		$j(".offline_"+data.user).hide();
	},
	
	offline: function(user){
		$j(".online_"+user).hide();
		$j(".offline_"+user).show();
	},
	
	newMessage: function(data){
		var target = data.from;
		if(data.group)
			target = "g"+data.group;
		
		if($j('#chatWindow'+target).length == 0)
			phimChat.openWindow(target, false);
		
		var classes = "highlight";
		if(data.from == phimChat.UserID)
			classes = "";
		
		
		//if(typeof phimChat.writingTimeout[target] != "undefined" && phimChat.writingTimeout[target] !== null)
		//	window.clearTimeout(phimChat.writingTimeout[target]);
					
		$j('#writing'+target).remove();
		
		
		$j('#chatText'+target).append("<div class=\"chatMessage "+classes+"\"><span class=\"time\">"+phimChat.time()+"</span><span class=\"username\">"+data.fromUser+": </span><span class=\"content\">"+data.content+"</span></div>");
		$j('#chatWindow'+target).show();
		contentManager.rmePCR("mphim", "-1", "windowStatus", [target, "visible"]);

		if(data.from != phimChat.UserID)
			phimChat.playSound();
		
		phimChat.updateUnread();
		phimChat.scroll('chatText'+target);
		if(data.from != phimChat.UserID && phimChat.showNotification)
			Interface.notify('phim', data.fromUser+': '+data.content, 10000);
	},
	
	/*newRead: function(data){
		$j('#user'+data.from).removeClass('highlight');
	},*/
	
	scroll: function(element, to){
		$j('.content').each(function(k, v){
			var elem = $j(v);
			
			if(phimChat.onlyEmoji(elem.html().trim())){
				elem.css('display', "block").css("padding-left", "40px").css("padding-bottom", "20px").css("font-size", "40px");
			}

		});
		
		var wtf = $j('#'+element);
		if(!wtf.length)
			return;
		
		if(typeof to == "undefined")
			to = wtf[0].scrollHeight;

		wtf.scrollTop(to);
	},
	
	playSound: function(){
		//console.log('pling!');
		(new Audio("./ubiquitous/phim/NewIM2.mp3")).play();
	},
	
	favicoOrig: null,
	
	updateUnread: function(addCount){
		phimChat.unread += addCount;
		
		var link = document.querySelector("link[rel*='shortcut icon']");// || document.createElement(\'link\');
		if(phimChat.favicoOrig === null)
			phimChat.favicoOrig = link.href;
		
		if($j('.chatMessage.highlight').length > 0)
			link.href = './ubiquitous/phim/newMessage.ico';
		else
			link.href = phimChat.favicoOrig;
		

		document.getElementsByTagName('head')[0].appendChild(link);
	}
};
