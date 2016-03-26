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

var pWebsocket = {
	socket: null,
	retryCounter: 500,
	server: null,
	queue: new Array(),
	setQueue: [],
	subQueue: [],
	onCloseCallbacks: [],
	isOpen: false,
	
	send: function(topic, data){
		/*for(var i = 0; i < pWebsocket.setQueue.length; i++)
			if(pWebsocket.setQueue[i][0] == plugin)
				return;*/
		
		//data.mode = "set";
		
		pWebsocket.setQueue.push([topic, data]);
		
		if(pWebsocket.isOpen)
			pWebsocket.sendQueue();
	},
			
	sendQueue: function(){
		if(!pWebsocket.isOpen)
			return;
		
		for(var i = 0; i < pWebsocket.subQueue.length; i++)
			pWebsocket.socket.subscribe(pWebsocket.subQueue[i][0], pWebsocket.subQueue[i][1]);

		for(var i = 0;i < pWebsocket.setQueue.length; i++)
			pWebsocket.socket.publish(pWebsocket.setQueue[i][0], pWebsocket.setQueue[i][1]);
		
	},
	
	onDisconnect: function(callback){
		pWebsocket.onCloseCallbacks.push(callback);
	},
	
	subscribe: function(topic, callback){
		pWebsocket.subQueue.push([topic, callback]);
		
		if(pWebsocket.isOpen)
			pWebsocket.sendQueue();
	},
	
	connection: function(){
		/*var conn = new ab.Session('ws://'+pWebsocket.server, function() {
				console.log('WebSocket connection established');
				pWebsocket.isOpen = true;
				pWebsocket.socket = this;
				
				conn.subscribe('pWebsocket', function(category, data) {
					pWebsocket.handleWSMessage(data);
				});
				
				pWebsocket.sendQueue();
				
				pWebsocket.retryCounter = 500;
				
			},
					
			function() { // When the connection is closed
				console.warn('WebSocket connection closed');
				
				pWebsocket.isOpen = false;
				for(var i = 0; i < pWebsocket.onCloseCallbacks.length; i++)
					pWebsocket.onCloseCallbacks[i]();
				
				pWebsocket.socket = null;
				if(pWebsocket.retryCounter > 0){
					window.setTimeout(pWebsocket.connection, Math.floor(Math.random() * (5000 - 2000 + 1)) + 2000);
					pWebsocket.retryCounter--;
				}
					
			},
					
			{// Additional parameters, we're ignoring the WAMP sub-protocol for older browsers
				'skipSubprotocolCheck': true
			}
		);
		
		return conn;*/
	},
	
	init: function(){
		contentManager.rmePCR("mWebsocket", "-1", "getServer", "", function(transport){
			if(transport.responseText == "nil")
				return;
			
			try {
				if(pWebsocket.socket != null)
					return true;
				
				pWebsocket.server = transport.responseText;
				
				pWebsocket.connection();


				var callbacks = Registry.list("pWebsocket");
				for (var i = 0; i < callbacks.length; i++)
					callbacks[i]();

				return true;
			} catch(ex) { 
				alert(ex);
			}
		});
		
		
	},
	
	/*parseMessage: function(msg) { 
		var ex = msg.data.split(" ");
		
		var message = msg.data.substr(ex[0].length + 1 + ex[1].length + 1);
		
		if(ex[0] == "/message" && window[ex[1]] && typeof window[ex[1]].handleWSMessage == "function")
			window[ex[1]].handleWSMessage(message);

		if(ex[0] == "/status" && window[ex[1]] && typeof window[ex[1]].handleWSStatus == "function")
			window[ex[1]].handleWSStatus(message);
	},*/
	
	handleWSMessage: function(data){
		//var data = $j.parseJSON(message);
		
		var show = "";
		for (var v in data) 
			show += "<p><b>"+v+"</b>: "+data[v]+"</p>";
			
		Popup.create("Websocket", "edit", "Websocket Nachrichten", {width: 800});
		Popup.show("Websocket", "edit");
		$j('#editDetailsContentWebsocket').html("<div style=\"max-height:400px;overflow:auto;\">"+show+"</div>");
		//Popup.update({responseText:show}, "Websocket", "edit");
	},
	
	close: function(){
		if(pWebsocket.socket == null)
			return;
		
		pWebsocket.socket.close();
		pWebsocket.socket = null;
	}
}

$j(function(){
	pWebsocket.init();
});


$j(window).unload(function() {
	pWebsocket.close();
});