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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
		
class phimGUI extends phim implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("phim");
	
		$gui->optionsEdit(false, false);
		
		return $gui->getEditHTML();
	}
	
	function ping(){ //dummy for keeping the session alive
		
	}
	
	function offline(){
		$message = new stdClass();
		$message->method = "offline";
		$message->user = Session::currentUser()->getID();
		
		$this->go($message, 0);
	}
	
	function online(){
		$message = new stdClass();
		$message->method = "online";
		$message->user = Session::currentUser()->getID();
		
		$this->go($message, 0);
	}
	
	function setRead($fromUserID){
		$AC = anyC::get("phim");
		$AC->addAssocV3("phimFromUserID", "=", $fromUserID);
		$AC->addAssocV3("phimToUserID", "=", Session::currentUser()->getID());
		$AC->addAssocV3("phimRead", "=", "0");
		while($P = $AC->n()){
			echo $P->getID()."\n";
			$P->changeA("phimRead", "1");
			$P->saveMe();
		}
		
		$message = new stdClass();
		$message->method = "read";
		#$message->content = $text;
		$message->from = $fromUserID;
		#$message->fromUser = Session::currentUser()->A("name");
		$message->to = Session::currentUser()->getID();
		$message->time = time();
		
		#$F->store();
		
		$this->go($message, Session::currentUser()->getID());
	}
	
	function sendMessage($to, $text){
		$F = new Factory("phim");
		
		$target = $to;
		$group = 0;
		if($to[0] == "g"){
			$group = str_replace("g", "", $to);
			$to = 0;
		}
		
		$F->sA("phimFromUserID", Session::currentUser()->getID());
		$F->sA("phimToUserID", $to);
		$F->sA("phimTime", time());
		$F->sA("phimMessage", $text);
		$F->sA("phimphimGruppeID", $group);
		
		$message = new stdClass();
		$message->method = "message";
		$message->content = $text;
		$message->from = Session::currentUser()->getID();
		$message->fromUser = Session::currentUser()->A("name");
		$message->to = $to;
		$message->time = time();
		$message->group = $group;
		
		$F->store();
		
		$this->go($message, $target);
	}
	
	private function go($message, $to){
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
		
		$realm = $S->A("WebsocketRealm");
		
		spl_autoload_unregister("phynxAutoloader");
		
		require Util::getRootPath().'PWS/Thruway/vendor/autoload.php';

		require_once __DIR__.'/ClientPhimAuthenticator.php';
		
		$client = new Thruway\Peer\Client($realm);
		$client->addClientAuthenticator(new ClientPhimAuthenticator($realm, "phimUser", $S->A("WebsocketToken")));
		
		$client->addTransportProvider(new Thruway\Transport\PawlTransportProvider("ws".($S->A("WebsocketSecure") ? "s" : "")."://".$S->A("WebsocketServer").":".$S->A("WebsocketServerPort")."/"));

		$client->on('open', function (Thruway\ClientSession $session) use ($message, $to) {

			$session->publish('it.furtmeier.phim_'.$to, [json_encode($message, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true])->then(
				function () {
					echo "Publish Acknowledged!\n";
					die();
				},
				function ($error) {
					// publish failed
					echo "Publish Error {$error}\n";
				}
			);

			//$session->close();
		});


		$client->start();
	}
}
?>