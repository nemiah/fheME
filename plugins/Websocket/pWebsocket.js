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

var pWebsocket = {
	socket: null,
	retryCounter: 1,
	
	queue: new Array(),
	setQueue: [],
	subQueue: [],
	onCloseCallbacks: [],
	isOpen: false,
	/*register: function(plugin){
		for(var i = 0; i < pWebsocket.queue.length; i++)
			if(pWebsocket.queue[i] == plugin)
				return;
		
		pWebsocket.queue.push(plugin);
	},*/
	
	/*send: function(action, plugin, value){
		pWebsocket.socket.send("/"+action+" "+plugin+" "+$j.toJSON(value));
	},*/
	
	settings: function(topic, data){
		/*for(var i = 0; i < pWebsocket.setQueue.length; i++)
			if(pWebsocket.setQueue[i][0] == plugin)
				return;*/
		
		data.mode = "set";
		
		pWebsocket.setQueue.push([topic, data]);
		
		if(pWebsocket.isOpen)
			pWebsocket.sendQueue();
	},
			
	sendQueue: function(){
		if(!pWebsocket.isOpen)
			return;
		
		var e;
		while(e = pWebsocket.subQueue.pop())
			pWebsocket.socket.subscribe(e[0], e[1]);

		while(e = pWebsocket.setQueue.pop())
			pWebsocket.socket.publish(e[0], e[1]);
		
	},
	
	/*sendInitData: function(){
		pWebsocket.register("pWebsocket");
		
		pWebsocket.socket.send("/register "+pWebsocket.queue.join(" "));

		for(var i = 0; i < pWebsocket.setQueue.length; i++){
			var j = i;
			setTimeout(function(){
				pWebsocket.socket.send("/settings "+pWebsocket.setQueue[j][0]+" "+$j.toJSON(pWebsocket.setQueue[j][1]));
			}, 500 * (i + 1));
			
		}
	},*/
	
	onDisconnect: function(callback){
		pWebsocket.onCloseCallbacks.push(callback);
	},
	
	subscribe: function(topic, callback){
		pWebsocket.subQueue.push([topic, callback]);
		
		if(pWebsocket.isOpen)
			pWebsocket.sendQueue();
	},
	
	connection: function(serverURL){
		var conn = new ab.Session('ws://'+serverURL, function() {
				pWebsocket.isOpen = true;
				conn.subscribe('pWebsocket', function(category, data) {
					// This is where you would add the new article to the DOM (beyond the scope of this tutorial)
					pWebsocket.handleWSMessage(data);
					//console.log(data);
				});
				pWebsocket.sendQueue();
			},
					
			function() { // When the connection is closed
				pWebsocket.isOpen = false;
				for(var i = 0; i < pWebsocket.onCloseCallbacks.length; i++)
					pWebsocket.onCloseCallbacks[i]();
				//console.warn('WebSocket connection closed');
			},
					
			{// Additional parameters, we're ignoring the WAMP sub-protocol for older browsers
				'skipSubprotocolCheck': true
			}
		);
		
		return conn;
			
		/*var socket = null;
		
		serverURL = "ws://"+serverURL+"/phpwebsockets/server.php";
		
		if('WebSocket' in window) {
			socket = new WebSocket(serverURL, "phynx");
		} else if('MozWebSocket' in window) {
			socket = new MozWebSocket(serverURL, "phynx");
		} else {
			alert('WebSockets not supported by this browser');
			return false;
		}
		
		return socket;*/
	},
	
	init: function(){
		contentManager.rmePCR("mWebsocket", "-1", "getServer", "", function(transport){
			//var host = "ws://192.168.7.77:8088/phpwebsockets/server.php";
			if(transport.responseText == "nil")
				return;
			
			try {
				if(pWebsocket.socket != null)
					return true;

				pWebsocket.socket = pWebsocket.connection(transport.responseText);


				/*pWebsocket.socket.onopen = function(msg) {
					pWebsocket.sendInitData();

					pWebsocket.retryCounter = 1;
				};

				pWebsocket.socket.onclose = function(msg) {
					for(var i = 0; i < pWebsocket.onCloseCallbacks.length; i++)
						pWebsocket.onCloseCallbacks[i]();
					
					
					pWebsocket.socket = null;
					if(pWebsocket.retryCounter > 0){
						window.setTimeout(pWebsocket.init, Math.floor(Math.random() * (5000 - 2000 + 1)) + 2000);
						pWebsocket.retryCounter--;
					}
				};

				pWebsocket.socket.onmessage = pWebsocket.parseMessage;*/

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
	},/*
	
	test: function(serverURL){
		var socket = pWebsocket.connection(serverURL);
		
		socket.onerror = function(){
			alert("Die Verbindung ist fehlgeschlagen");
			//socket.close();
			socket = null;
		}
		
		socket.onopen = function(msg) {
			socket.send("/register pWebsocket");
			
			setTimeout(function(){
				socket.send("/message pWebsocket "+$j.toJSON({"from" : "Websocket", "message" : "Der Server ist erreichbar"}));
				
				setTimeout(function(){
					socket.onerror = null;
					socket.close();
				}, 1000);
			}, 500);
		};
		
		socket.onmessage = pWebsocket.parseMessage;
		
	},*/
	
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