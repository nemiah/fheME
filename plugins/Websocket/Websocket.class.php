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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
class Websocket extends PersistentObject {
	public static function getData($for){
		$AC = anyC::get("Websocket", "WebsocketUseFor", $for);
		
		return $AC->asJSON();
	}
	
	public static function publish($plugin, $message){
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
		Util::alienAutloaderLoad(Util::getRootPath().'plugins/Websocket/lib/vendor/autoload.php');
		
		$instance = Util::eK();
		
		$wamp = new \JSzczypk\WampSyncClient\Client(
				"ws".($S->A("WebsocketSecure") ? "s" : "")."://".$S->A("WebsocketServer").":".$S->A("WebsocketServerPort")."/", 
				$S->A("WebsocketRealm"),
				"client",
				$S->A("WebsocketToken"));
		#var_dump($wamp);
		$r = $wamp->publish('it.furtmeier.'.$instance.'.'.$plugin, [$message]);

		Util::alienAutloaderUnload();
		
		return $r;
	}
	
	/*public static function send($message, $plugin){
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
		$instance = Util::eK();
		
		$realm = $S->A("WebsocketRealm");
		
		spl_autoload_unregister("phynxAutoloader");
		Util::alienAutloaderLoad(Util::getRootPath().'PWS/Thruway/vendor/autoload.php');

		Thruway\Logging\Logger::set(new Psr\Log\NullLogger());
		$connection = new \Thruway\Connection([
			"realm"   => $realm,
			"url"     => "ws".($S->A("WebsocketSecure") ? "s" : "")."://".$S->A("WebsocketServer").":".$S->A("WebsocketServerPort")."/"
		]);

		$client = $connection->getClient();
		$auth = new Thruway\Authentication\ClientWampCraAuthenticator("client", $S->A("WebsocketToken"));
		$client->setAuthId('client');
		$client->addClientAuthenticator($auth);
		
		$connection->on('open', function (\Thruway\ClientSession $session) use ($connection, $message, $plugin, $instance) {
			$session->publish('it.furtmeier.'.$instance.'.'.$plugin, [$message], [], ["acknowledge" => true])->then(
				function () use ($connection) {
					$connection->close();
				}, function ($connection) {
					$connection->close();
				}
			);
			
		});
		
		$connection->on('error', function($reason){
			print_r($reason);
		});

		$connection->open();
		
		Util::alienAutloaderUnload();
	}*/
}
?>