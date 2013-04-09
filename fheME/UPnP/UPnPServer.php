<?php
if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnUPnPServer");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");
registerClassPath("phpUPnP", dirname(__FILE__)."/phpUPnP.class.php");

$S = new phpUPnP();

$S->mServer();

?>