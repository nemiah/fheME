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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

var phim = {
	currentUser: null,
	ids2users: new Object(),
	unread: {},
	
	init: function(){
		$j('#container').css("margin-right", "20px");
		$j("#phim").css("display", "block");
		phim.resize();
		if($j('#navigation').css("position") == "fixed")
			$j("#navigation").css("margin-right", "20px");
		
		contentManager.rmePCR("mphim", "-1", "getInit", "", function(transport){
			$j("#phim").html(transport.responseText);
			if(typeof pWebsocket == "undefined")
				return;
			
			pWebsocket.onDisconnect(phim.disconnected);
			pWebsocket.subscribe("phim", phim.handleWS);
			pWebsocket.settings("phim", {"acceptFor" : phim.currentUser, "DeviceID": $j.jStorage.get('phynxDeviceID', 0)});
		});
		
		$j(window).resize(function() {
		  phim.resize();
		});
	},
	
	handleWS: function(topic, data){
		console.log(topic);
		console.log(data);
	},
	
	/*disconnect: function(){
		pWebsocket.settings("phim", {"disconnect" : phim.currentUser, "DeviceID": $j.jStorage.get('phynxDeviceID', 0)});
	},*/
	
	disconnected: function(){
		$j('.phimUserStatus').attr("src", "./ubiquitous/phim/userOffline.png")
	},
	
	resize: function(){
		$j("#phim").css("height", $j(window).height()+"px")
	},
	
	id2user: function(id){
		/*if(!phim.ids2users[id]){
			//contentManager.rmePCR("mphim", "-1", "id2user", id, function(transport.responseText){ a(); });
			console.log(id+" unbekannt");
			return id;
		} else*/
		return phim.ids2users[id+""];
	},
	
	send: function(message, from, phimTargetUserID){
		if(message == "")
			return;
		
		var MO = {"from" : from, "to" : phimTargetUserID, "timeSent" : Math.round(+(new Date) / 1000), "message" : message};
		pWebsocket.send("message", "phim", MO);
		
		//$j('#phimMessages'+phimTargetUserID).append(phim.formatMessage(MO));
		//$('phimMessages'+phimTargetUserID).scrollTop = $('phimMessages'+phimTargetUserID).scrollHeight;
		
		//pWebsocket.send("status", "phim", {"readAll" : phimTargetUserID});
	},
	
	formatMessage: function(data){
		var d = new Date(data.timeSent * 1000);
		return "<p style=\"padding:3px;line-height:1.5;\"><span style=\"color:grey;\">("+(d.getDate() < 10 ? "0" : "")+d.getDate()+"."+(d.getMonth() + 1 < 10 ? "0" : "")+(d.getMonth() + 1)+"."+d.getFullYear()+" "+(d.getHours() < 10 ? "0" : "")+d.getHours()+":"+(d.getMinutes() < 10 ? "0" : "")+d.getMinutes()+")</span> <b>"+phim.id2user(data.from)+":</b> <span"+(data.to == phim.currentUser ? " class=\"phimUnreadMessage"+data.from+"\" style=\"color:orange;\"" : "")+">"+data.message+"</span></p>";//("+data.timeSent+") 
	},
	
	getChatWindow: function(phimTargetUserID, username){
		contentManager.rmePCR("mphim", -1, "getChatWindow", [phimTargetUserID], function(transport){
			Popup.displayNamed("phimTargetUserID", "Chat mit "+username, transport, phimTargetUserID, {'persistent': true});
			
			//pWebsocket.send("status", "phim", {"readAll" : phimTargetUserID});
		});
		
	},
	
	unreadDisplay: function(){
		$j.each(phim.unread, function(from, unread){
			$j('#phimUserUnread'+from).html(unread > 0 ? "("+unread+")" : "");
		});
		
	},
	
	playSound: function(){
		(new Audio("./ubiquitous/phim/NewIM.ogg")).play();
	},
	
	unreadSet: function(from, count){
		if(!phim.unread[from])
			phim.unread[from] = 0;
		
		phim.unread[from] = count;
		
		phim.unreadDisplay();
	},
	
	handleWSMessage: function(message){
		var data = $j.parseJSON(message);
		
		//console.log(data);
		
		var targetID = (data.returnToSender ? data.to : data.from);
		
		if($j('#phimMessages'+targetID).length > 0) {
			$j('#phimMessages'+targetID).append(phim.formatMessage(data));
			$('phimMessages'+targetID).scrollTop = $('phimMessages'+targetID).scrollHeight;
			phim.playSound();
			phim.unreadSet(targetID, 0);
			if(data.returnToSender)
				$j('.phimUnreadMessage'+targetID).css("color", "");
		} else {
			phim.playSound();
			phim.unreadSet(targetID, data.unread);
		}

		//phim.unreadDisplay();

	},
	
	handleWSStatus: function(status){
		var s = status.split(" ");
		
		//var data = $j.parseJSON(message);
		//console.log(s);
		
		if(s[0] == "unread"){
			var data = status.substr(s[0].length + 1);
			data = $j.parseJSON(data);
			phim.unreadSet(data.from, data.count);
			
			$j('.phimUnreadMessage'+data.from).css("color", "");
		}
		
		if(s[0] == "online")
			for(var i = 1; i < s.length; i++)
				$j('#phimUserStatus'+s[i]).attr("src", "./ubiquitous/phim/userOnline.png")
		
		if(s[0] == "offline")
			for(var i = 1; i < s.length; i++)
				$j('#phimUserStatus'+s[i]).attr("src", "./ubiquitous/phim/userOffline.png")
			
		
	}
}

$j(function(){
	phim.init();
});
