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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnTinkerforge");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");

register_shutdown_function('cronShutdownHandler');
function cronShutdownHandler() {
	$last_error = error_get_last();
	if ($last_error['type'] !== E_ERROR) 
		return;
	
	print_r(SysMessages::i()->getMessages());
}

$e = new ExtConn(realpath(dirname(__FILE__)."/../../")."/");
$e->loadPlugin("fheME", "Tinkerforge");
$e->useDefaultMySQLData();

$e->useUser();

require_once(__DIR__.'/lib/IPConnection.php');
require_once(__DIR__.'/lib/BrickletTemperatureIR.php');
#require_once(__DIR__.'/lib/BrickletAnalogOut.php');
#require_once(__DIR__.'/lib/BrickletIO4.php');

declare(ticks = 1);

function callback($value, $data) {

	$bricklet = $data[0];
	$type = $data[1];
	
	$entryData = array(
		'topic' => "tinkerforge",
		'value' => $value,
		'bricklet' => $bricklet,
		'type' => $type,
		'time' => time()
	);

	$context = new ZMQContext();
	$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
	$socket->connect("tcp://localhost:5555");
	$socket->send(json_encode($entryData));

}


use Tinkerforge\IPConnection;

#$connections = array();

/*$AC = anyC::get("Tinkerforge");
while($T = $AC->getNextEntry()){
	$connections[$T->getID()] = new IPConnection();
	$connections[$T->getID()]->connect($T->A("TinkerforgeServerIP"), $T->A("TinkerforgeServerPort"));
	
	$ACB = anyC::get("TinkerforgeBricklet", "TinkerforgeBrickletTinkerforgeID", $T->getID());
	while($B = $ACB->getNextEntry()){
		$Type = "Tinkerforge\\".$B->A("TinkerforgeBrickletType");
		
		try {
			switch($B->A("TinkerforgeBrickletType")){
				case "BrickletTemperatureIR":
					$Bricklet = new $Type($B->A("TinkerforgeBrickletUID"), $connections[$T->getID()]);
					
					callback($Bricklet->getObjectTemperature(), array($B->A("TinkerforgeBrickletUID"), $B->A("TinkerforgeBrickletType")));
					
					$Bricklet->setObjectTemperatureCallbackPeriod(60000);
					$Bricklet->registerCallback($Type::CALLBACK_OBJECT_TEMPERATURE, 'callback', array($B->A("TinkerforgeBrickletUID"), $B->A("TinkerforgeBrickletType")));
				break;
			}
		} catch (ClassNotFoundException $ex){
			echo "Class not found: ";
			echo $ex->getClassName();
			echo "\n";
		}
		
	}
	$connections[$T->getID()]->dispatchCallbacks(-1);
	$connections[$T->getID()]->disconnect();
}*/

$nextRequest = array();
$counter = array();
while(true){
	$AC = anyC::get("Tinkerforge");
	while($T = $AC->getNextEntry()){
		#echo "go\n";
		try {
			$connection = new IPConnection();
			$connection->connect($T->A("TinkerforgeServerIP"), $T->A("TinkerforgeServerPort"));
		} catch(Exception $e){
			echo $e->getMessage();
			continue;
		}
		$ACB = anyC::get("TinkerforgeBricklet", "TinkerforgeBrickletTinkerforgeID", $T->getID());
		while($B = $ACB->getNextEntry()){
			$Type = "Tinkerforge\\".$B->A("TinkerforgeBrickletType");
			if(!isset($counter[$B->getID()]))
				$counter[$B->getID()] = 0;
			try {
				switch($B->A("TinkerforgeBrickletType")){
					case "BrickletTemperatureIR":
						if(isset($nextRequest[$B->getID()]) AND $nextRequest[$B->getID()] > time())
							continue;
						
						$Bricklet = new $Type($B->A("TinkerforgeBrickletUID"), $connection);

						$temp = floor($Bricklet->getObjectTemperature() / 10.0);
						callback($temp, array($B->A("TinkerforgeBrickletUID"), $B->A("TinkerforgeBrickletType")));
						#echo date("H:i:s").": ".$temp."\n";
						
						if($temp < 35)
							$counter[$B->getID()]++;
						else
							$counter[$B->getID()] = 0;
						
						if($counter[$B->getID()] >= 3)
							$nextRequest[$B->getID()] = time() + 60 * 30;
						else
							$nextRequest[$B->getID()] = time() + 60;
						
					break;
				}
			} catch (ClassNotFoundException $ex){
				echo "Class not found: ";
				echo $ex->getClassName();
				echo "\n";
			}

		}
		
		$connection->disconnect();
	}
	
	DBStorage::disconnect();
	sleep(60);
}

$e->cleanUp();
?>