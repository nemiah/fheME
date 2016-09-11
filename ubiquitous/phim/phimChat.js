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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

var phimChat = {
	server: "localhost:4444",
	UserID: null,
	c: null,
	realm: "phim",
	groups: [],
	root: "",
	
	init: function(server, realm, UserID, UserName, authKey, groups, root){
		$j("#darkOverlay").css("height", $j(window).height()).css("width", $j(window).width());
		
		phimChat.root = root;
		phimChat.server = server;
		phimChat.UserID = UserID;
		phimChat.UserName = UserName;
		phimChat.realm = realm;
		phimChat.authKey = authKey;
		phimChat.groups = groups;
		
		phimChat.overlayShow();
		
		phimChat.connection();
		
		$j(window).on("beforeunload", function() {
			//$j.jStorage.set('phimX', window.screenX);
			//$j.jStorage.set('phimY', window.screenY);
			phimChat.c.close();
		});
		
		$j(window).on("unload", function() {
			phimChat.goOffline();
		});
		
		/*window.setInterval(function(){
			if(typeof bridge != "undefined")
				bridge.minimize();
			
		}, 10000);*/
		phimChat.tray();
		window.setInterval(function(){
			navigator.sendBeacon(contentManager.getRoot()+"interface/rme.php?rand="+Math.random()+"&class=phim&construct=-1&method=ping"+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""));
		}, 3 * 60 * 1000);
	},
	
	goOffline: function(){
		navigator.sendBeacon(contentManager.getRoot()+"interface/rme.php?rand="+Math.random()+"&class=phim&construct=-1&method=offline"+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""));
	},
	
	send: function(){
		
		if($j('#channel').val() > 0)
			$j('#chatText'+$j('#channel').val()).append("<div><span class=\"username\">"+phimChat.UserName+": </span>"+$j('[name=newMessage]').val()+"</div>");
		
		contentManager.rmePCR('phim', -1, 'sendMessage', [$j('#channel').val(), $j('[name=newMessage]').val()], function(){ $j('[name=newMessage]').val(''); }, '', 1);
				
		phimChat.scroll('chatText'+$j('#channel').val());
	},
	
	connection: function(){
		var connection = new autobahn.Connection({
			url: phimChat.server, 
			realm: phimChat.realm,
			authmethods: ["phimAuth_"+phimChat.realm],
			authid: "phimUser",
			onchallenge: function(session, method, extra){
				return phimChat.authKey;
			}
		});

		connection.onopen = function (session, details) {
			//console.log(details);
			//console.log("connected!");
			
			phimChat.overlayHide();
			
			contentManager.rmePCR('phim', -1, 'online', []);
			
			function onevent(args) {
				var data = jQuery.parseJSON(args[0]);

				if(data.method == "message")
					phimChat.newMessage(data);

				if(data.method == "read")
					phimChat.newRead(data);
			}
			
			function oneventG(args) {
				var data = jQuery.parseJSON(args[0]);

				if(data.method == "message")
					phimChat.newMessage(data);
			}
		   
			function onevent0(args) {
				var data = jQuery.parseJSON(args[0]);

				if(data.method == "online"){
					phimChat.online(data);
					return;
				}

				if(data.method == "offline"){
					phimChat.offline(data);
					return;
				}

				if(data.method == "discover"){
					session.publish("it.furtmeier.phim_0", ['{"method":"online", "user": '+phimChat.UserID+'}']);
					return;
				}

				$j('#chatText0').append("<div><span class=\"username\">"+data.fromUser+": </span>"+data.content+"</div>");
				if($j('#channel').val() != 0)
					$j('#user0').addClass('highlight');

				phimChat.playSound();
				phimChat.scroll('chatText0');
			}

			session.subscribe('it.furtmeier.phim_0', onevent0);
			session.subscribe('it.furtmeier.phim_'+phimChat.UserID, onevent);
		
			for(var i = 0; i < phimChat.groups.length; i++){
				session.subscribe('it.furtmeier.phim_g'+phimChat.groups[i], oneventG);
				//console.log("subscribing to it.furtmeier.phim_g"+phimChat.groups[i])
			}
		
			session.publish("it.furtmeier.phim_0", ['{"method":"discover"}']);
		};

		connection.onclose = function(reason){
			$j('#userList .online').hide();
			$j('#userList .offline').show();
			
			phimChat.overlayShow();
			
			console.warn('WebSocket connection closed: '+reason);	
		}

		connection.open();
		
		phimChat.c = connection;
	},
	
	overlayShow: function(){
		$j("#darkOverlay").css("height", $j(window).height()).css("width", $j(window).width());
		$j("#darkOverlay").fadeIn();

		$j('#darkOverlay').html("<div id='offlineMessage' style='display:inline-block;z-index:100000;color:white;font-size:40px;width:400px;position:absolute;'>Sie sind offline</div>");
		$j('#offlineMessage').css("top", ($j(window).height() - $j('#offlineMessage').outerHeight()) / 2);
		$j('#offlineMessage').css("left", ($j(window).width() - $j('#offlineMessage').outerWidth()) / 2);
	},
	
	overlayHide: function(){
		$j("#darkOverlay").fadeOut();
		$j('#offlineMessage').remove();
	},
	
	online: function(data){
		$j('#user'+data.user+" .online").show();
		$j('#user'+data.user+" .offline").hide();
	},
	
	offline: function(data){
		$j('#user'+data.user+" .online").hide();
		$j('#user'+data.user+" .offline").show();
	},
	
	newMessage: function(data){
		var target = data.from;
		if(data.group)
			target = "g"+data.group;
		
		$j('#chatText'+target).append("<div><span class=\"username\">"+data.fromUser+": </span>"+data.content+"</div>");

		if(data.group){
			if($j('#channel').val() != target)
				$j('#group'+target).addClass('highlight');
		} else {
			if($j('#channel').val() != data.from)
				$j('#user'+data.from).addClass('highlight');
			else
				contentManager.rmePCR('phim', -1, 'setRead', [data.from], function(){}, '', 1, function(){});
		}

		phimChat.tray();

		phimChat.playSound();
		phimChat.scroll('chatText'+target);
	},
	
	tray: function(){
		if(typeof bridge == "undefined")
			return;
			
		if($j(".highlight").length == 0)
			bridge.trayBlinkStop();
		
		if($j(".highlight").length > 0)
			bridge.trayBlinkStart();
	},
	
	newRead: function(data){
		$j('#user'+data.from).removeClass('highlight');
		
		phimChat.tray();
	},
	
	scroll: function(element){
		var wtf = $j('#'+element);
		wtf.scrollTop(wtf[0].scrollHeight);
	},
	
	playSound: function(){
		(new Audio(phimChat.root+"../ubiquitous/phim/NewIM.ogg")).play();
	}
};