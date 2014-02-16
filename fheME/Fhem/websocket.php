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


session_name("ExtConnFhem");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$fp = stream_socket_client("tcp://localhost:7072", $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "inform on\n");
    while (!feof($fp)) {
		$line = fgets($fp, 1024);
		
		if($line === false)
			continue;
		
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

		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
		$socket->connect("tcp://localhost:5555");
		$socket->send(json_encode($entryData));
    }
    fclose($fp);
}
?>