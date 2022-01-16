/**
 *
 *  This file is part of plugins.

 *  plugins is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  plugins is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */

var pWebsocket = {
	data: {},
	/*server: null,
	realm: "phim",
	instance: null,
	authKey: null,*/
	session: null,
	connection: null,
	
	init: function(){
		contentManager.rmePCR("Websocket", -1, "getServerData", [], function(t){
			
			pWebsocket.data = t.responseData;
			
			if(pWebsocket.data.server == "none")
				return;
			
			pWebsocket.connect();
		})
		
	},
	
	close: function(){
		if(pWebsocket.connection == null)
			return;
		
		pWebsocket.connection.close();
		pWebsocket.connection = null;
	},
	
	onopenF: [],
	onopen: function(callback){
		pWebsocket.onopenF.push(callback);
		
		if(pWebsocket.session != null)
			callback(pWebsocket.session);
	},
	
	connect: function(){
		
		var connection = new autobahn.Connection({
			url: pWebsocket.data.server, 
			realm: pWebsocket.data.realm,
			authmethods: ["wampcra"],
			authid: "client",
			
			onchallenge: function(session, method, extra){
				if (method !== 'wampcra') 
					return false;
				
				return autobahn.auth_cra.sign(pWebsocket.data.token, extra.challenge);
			}
		});

		connection.onopen = function (session, details) {
			pWebsocket.session = session;
			
			for(var i = 0; i < pWebsocket.onopenF.length; i++)
				pWebsocket.onopenF[i](session);
		};

		connection.onclose = function(reason){
			pWebsocket.session = null;
		}

		connection.open();
		pWebsocket.connection = connection;
	}
}

pWebsocket.init();