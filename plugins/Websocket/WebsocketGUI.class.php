<?php
/**
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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class WebsocketGUI extends Websocket implements iGUIHTML2 {
	
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Websocket");
	
		#$B = $gui->addSideButton("Verbindung\ntesten", "empty");
		#$B->onclick("pWebsocket.test(\$j('#editWebsocketGUI input[name=WebsocketServer]').val()+':'+\$j('#editWebsocketGUI input[name=WebsocketServerPort]').val());");
		
		$gui->attributes(array(
			"WebsocketUseFor",
			"WebsocketServer",
			"WebsocketServerPort",
			"WebsocketSecure",
			"WebsocketRealm",
			"WebsocketToken"
		));
		
		$gui->label("WebsocketSecure", "Verschlüsselt?");
		$gui->label("WebsocketUseFor", "Verwenden für");
		$gui->label("WebsocketServerPort", "Port");
		
		$gui->type("WebsocketSecure", "checkbox");
		$gui->type("WebsocketUseFor", "select", array("phim" => "phim/Hotline", "fheME" => "fheME"));
		
		$gui->space("WebsocketServer");
		$gui->space("WebsocketRealm");
		
		return $gui->getEditHTML();
	}
}
?>