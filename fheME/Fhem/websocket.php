<?php
/*
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */


class ThruwayLoader {
	public static function alienClassLoader($class){
		$ex = explode("\\", $class);
		
		#if($ex[0] == "figo"){
		$file = Util::getRootPath()."plugins/Websocket/WAMP/".implode("/", $ex).".php";

		if(!file_exists($file))
			return false;

		require_once $file;
		#}
	}
	
}
		
if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];


session_name("ExtConnFhem");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");
$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);
$e->addClassPath($absolutePathToPhynx."fheME/Fhem");

$lastCommand = null;
$lastCommandC = 0;
$fp = stream_socket_client("tcp://192.168.7.11:7072", $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "inform on\n");
    while (!feof($fp)) {
		$line = fgets($fp, 1024);
		
		if($line === false)
			continue;
		
		
		if($line == $lastCommand)
			$lastCommandC++;
		else
			$lastCommandC = 0;
		
		if($line == $lastCommand AND $lastCommandC > 3)
			continue;
		
		$lastCommand = $line;
		
		#echo $line."\n";
		
		$ex = explode(" ", $line);
		$type = $ex[0];
		$device = $ex[1];
		$command = str_replace(":", "", $ex[2]);
		
		$AC = anyC::get("Fhem", "FhemType", $type);
		$AC->addAssocV3("FhemName", "=", $device);
		$F = $AC->getNextEntry();
		DBStorage::disconnect();
		
		if($F == null)
			continue;
		
		if($F->A("FhemInOverview") == "0")
			continue;
		
		if($type == "FHT" AND $command != "end-xmit")
			continue;
		
		$entryData = array(
			'topic' => "fhem",
			'value' => trim($line),
			'id' => $F->getID(),
			'when' => time()
		);
		
		#print_r($entryData);
		go($entryData);
		
		#$context = new ZMQContext();
		#$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
		#$socket->connect("tcp://localhost:5555");
		#$socket->send(json_encode($entryData));
    }
    fclose($fp);
}

function go($message){

	$S = anyC::getFirst("Websocket", "WebsocketUseFor", "fheME");
	$realm = $S->A("WebsocketRealm");
	
	spl_autoload_unregister("phynxAutoloader");
	
	require Util::getRootPath().'PWS/Thruway/vendor/autoload.php';
		

	if(!class_exists("ClientPhimAuthenticator", false)){
		Thruway\Logging\Logger::set(new Psr\Log\NullLogger());
		
		class ClientPhimAuthenticator implements Thruway\Authentication\ClientAuthenticationInterface {
			private $authID;
			private $key;
			private $realm;

			function __construct($realm, $authID, $key){
				$this->authID = $authID;
				$this->key = $key;
				$this->realm = $realm;
			}

			public function getAuthId() {
				return $this->authID;
			}

			public function setAuthId($authid) {

			}

			public function getAuthMethods() {
				return ["phimAuth_".$this->realm];
			}

			public function getAuthenticateFromChallenge(Thruway\Message\ChallengeMessage $msg)	{
				return new \Thruway\Message\AuthenticateMessage($this->key);
			}
		}
	}
	
	$connection = new \Thruway\Connection([
		"realm"   => $realm,
		"url"     => "ws".($S->A("WebsocketSecure") ? "s" : "")."://".$S->A("WebsocketServer").":".$S->A("WebsocketServerPort")."/"
	]);
	
	$client = $connection->getClient();
	$client->addClientAuthenticator(new ClientPhimAuthenticator($realm, "phimUser", $S->A("WebsocketToken")));
		
	$connection->on('open', function (\Thruway\ClientSession $session) use ($connection, $message) {
		$session->publish('it.furtmeier.fheme', [json_encode($message, JSON_UNESCAPED_UNICODE)], [], ["acknowledge" => true])->then(
			function () use ($connection) {
				$connection->close();
			}, function ($connection) {
				$connection->close();
			}
		);
	});

	$connection->open();
	
	
	#spl_autoload_unregister(array("ThruwayLoader", "alienClassLoader"));
	#spl_autoload_register("phynxAutoloader");
}
?>