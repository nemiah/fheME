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
class phim extends PersistentObject {
	private static $Users = null;
	public static function formatMessage(phim $M){
		if(self::$Users === null)
			self::$Users = Users::getUsersArray();
		
		$BR = new Button("Gelesen", "check", "iconicG");
		$BR->id("phim_".$M->getID());
		$BR->addClass("isread_".$M->A("phimToUserID"));
		$BR->style("font-size:10px;".(strpos($M->A("phimReadBy"), ";".$M->A("phimToUserID").";") === false ? "display:none;" : ""));

		if($M->A("phimFromUserID") != Session::currentUser()->getID())
			$BR = "";

		return "<div class=\"chatMessage ".(($M->A("phimFromUserID") != Session::currentUser()->getID() AND strpos($M->A("phimReadBy"), ";".Session::currentUser()->getID().";") === false) ? "highlight" : "")."\"><span class=\"time\">".Util::CLDateTimeParser($M->A("phimTime"))."$BR</span><span class=\"username\">".self::$Users[$M->A("phimFromUserID")].": </span><span class=\"content\">".$M->A("phimMessage")."</span></div>";
	}
	
	function sendMessage($to, $text){
		$F = new Factory("phim");
		
		$target = $to;
		$group = 0;
		if($to[0] == "g"){
			$group = str_replace("g", "", $to);
			$to = 0;
		}
		
		if($group){
			$G = new phimGruppe($group);
			$G->changeA("phimGruppeClosed", "");
			$G->saveMe();
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
		
		$MID = $F->store();
		
		$M = new phim($MID);
		$append = self::formatMessage($M);
		
		$this->go($message, $target);
		
		echo $append;
	}
	
	protected function go($message, $to){
		$S = anyC::getFirst("Websocket", "WebsocketUseFor", "phim");
		$instance = Util::eK();
		
		$realm = $S->A("WebsocketRealm");
		
		spl_autoload_unregister("phynxAutoloader");
		
		require Util::getRootPath().'PWS/Thruway/vendor/autoload.php';

		Thruway\Logging\Logger::set(new Psr\Log\NullLogger());
		$connection = new \Thruway\Connection([
			"realm"   => $realm,
			"url"     => "ws".($S->A("WebsocketSecure") ? "s" : "")."://".$S->A("WebsocketServer").":".$S->A("WebsocketServerPort")."/"
		]);

		$client = $connection->getClient();
		$auth = new Thruway\Authentication\ClientWampCraAuthenticator("client", $S->A("WebsocketToken"));
		$client->setAuthId('client');
		$client->addClientAuthenticator($auth);
		
		$connection->on('open', function (\Thruway\ClientSession $session) use ($connection, $message, $to, $instance) {
			if(!is_array($message)){
				
				if($message->method == "message"){
					$newMessage = new stdClass();
					$newMessage->method = "newMessage";
					$newMessage->to = $to;
					$newMessage->from = $message->from;
					#$newMessage->group = $message->group;

					$session->publish('it.furtmeier.'.$instance.'.phim_Watchdog', [json_encode($newMessage, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true]);
				}
				
				$session->publish('it.furtmeier.'.$instance.'.phim_'.$to, [json_encode($message, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true])->then(
					function () use ($connection) {
						$connection->close();
					}, function ($connection) {
						$connection->close();
					}
				);
			} else {
				foreach($message AS $k => $singleMessage){
					$close = ($k == count($message) - 1);
					
					if($singleMessage->method == "message"){
						$newMessage = new stdClass();
						$newMessage->method = "newMessage";
						$newMessage->to = $to;
						$newMessage->from = $message->from;
						#$newMessage->group = $message->group;

						$session->publish('it.furtmeier.'.$instance.'.phim_Watchdog', [json_encode($newMessage, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true]);
					}
					
					$session->publish('it.furtmeier.'.$instance.'.phim_'.$to, [json_encode($singleMessage, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true])->then(
						function () use ($connection, $close) {
							if($close)
								$connection->close();
						}, function ($connection) {
							$connection->close();
						}
					);
				}
			}
		});
		
		$connection->on('error', function($reason){
			print_r($reason);
		});

		$connection->open();
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