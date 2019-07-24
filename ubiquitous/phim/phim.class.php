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
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */
class phim extends PersistentObject {

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
	
	protected function go($message, $to){
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
		
		$realm = $S->A("WebsocketRealm");
		
		spl_autoload_unregister("phynxAutoloader");
		
		require Util::getRootPath().'PWS/Thruway/vendor/autoload.php';

		require_once __DIR__.'/ClientPhimAuthenticator.php';
		
		Thruway\Logging\Logger::set(new Psr\Log\NullLogger());
		$connection = new \Thruway\Connection([
			"realm"   => $realm,
			"url"     => "ws".($S->A("WebsocketSecure") ? "s" : "")."://".$S->A("WebsocketServer").":".$S->A("WebsocketServerPort")."/"
		]);

		$client = $connection->getClient();
		$client->addClientAuthenticator(new ClientPhimAuthenticator($realm, "phimUser", $S->A("WebsocketToken")));

		$connection->on('open', function (\Thruway\ClientSession $session) use ($connection, $message, $to) {
			$session->publish('it.furtmeier.phim_'.$to, [json_encode($message, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true])->then(
				function () use ($connection) {
					$connection->close();
				}, function ($connection) {
					$connection->close();
				}
			);
		});

		$connection->open();
		
		/*$client = new Thruway\Peer\Client($realm);
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


		$client->start();*/
	}
	
	function newAttributes() {
		$A = parent::newAttributes();
		
		$A->phimUserID = Session::currentUser()->getID();
		
		return $A;
	}
	
	function deleteMe() {
		if($this->A("phimFromUserID") != Session::currentUser()->getID())
			Red::errorD ("Sie können nur eigene Nachrichten löschen!");
		
		parent::deleteMe();
	}
}
?>