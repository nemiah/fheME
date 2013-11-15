<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
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

session_name("ExtConnNuntius");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");

register_shutdown_function('cronShutdownHandler');
function cronShutdownHandler() {
	$last_error = error_get_last();
	if ($last_error['type'] !== E_ERROR) 
		return;
	
	print_r(SysMessages::i()->getMessages());
}

$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);

#$e->addClassPath($absolutePathToPhynx."/lightCRM/Mail");
$e->loadPlugin("fheME", "Nuntius");

$e->useDefaultMySQLData();
$e->useUser();

$N  = new mNuntius();
$NuntiusID = $N->sendMessage(0, $_GET["message"].",".$_SERVER[REMOTE_ADDR], $_GET["from"], $_GET["urgency"]);
#file_put_contents("/home/nemiah/phonebook.xml", file_get_contents("php://input"));

if($_GET["from"] == "FritzBox"){
	/*$S = new SimpleXMLElement(file_get_contents("ftp://$_SERVER[REMOTE_ADDR]/phonebook.xml"));
	
	$dom = new DOMDocument('1.0');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($S->asXML());

	file_put_contents("/home/nemiah/pb.xml", $dom->saveXML());*/
}

if($_GET["urgency"] < 10){
	$entryData = array(
		'topic' => "nuntius",
		'NuntiusID' => $NuntiusID,
		'time' => time(),
		'timeout' => 30
	);

	$context = new ZMQContext();
	$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
	$socket->connect("tcp://localhost:5555");
	$socket->send(json_encode($entryData));
}

$e->cleanUp();

?>