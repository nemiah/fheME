<?php
/*
 *  This file is part of lightAd.

 *  lightAd is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  lightAd is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
if(!file_exists("/var/www/status/cron_".gethostbyaddr('127.0.1.1')))
	die("Status file missing!");

ini_set('default_socket_timeout', 7);

if(file_exists("/var/www/status/cron_".gethostbyaddr('127.0.1.1'))){
	$status = file_get_contents("/var/www/status/cron_".gethostbyaddr('127.0.1.1'));
	#if(trim($status) !== "active")
	#	die();
	if(gethostbyaddr('127.0.1.1') != "cloud02.furtmeier.it")
		die();
}


openlog('ubiquitous/RSSParser', LOG_CONS | LOG_PID, LOG_USER);
syslog(LOG_INFO, "Started");


session_name("ExtConnGlobalRSSParser");

if(isset($argv[1]))
	$_SERVER["HTTP_HOST"] = $argv[1];

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$fp = fopen("/home/nemiah/globalCronRSSParser.lock", "a");
if(!$fp){
	syslog(LOG_ERR, "Could not create lock file /home/nemiah/globalCronRSSParser.lock! Exiting.");
	throw new Exception ("Could not create lock file /home/nemiah/globalCronRSSParser.lock");
}
if (!flock($fp, LOCK_EX | LOCK_NB)){
	syslog(LOG_WARNING, "Could not acquire lock! Exiting.");
	throw new Exception ("Could not acquire lock!");
}
syslog(LOG_INFO, "Lock acquired.");

$e = new ExtConn(Util::getRootPath());
$e->loadPlugin("plugins", "Cloud");

$absolutePathToPhynx = Util::getRootPath();

$e->addClassPath(FileStorage::getFilesDir());

$e->useDefaultMySQLData();

$ACC = new mCloud();
$ACC->addAssocV3("CloudOption", "=", "allowedPlugins");
$ACC->addAssocV3("CloudValue", "REGEXP", "(^mRSSParser,)|(,mRSSParser,)|(,mRSSParser$)|(^mRSSParser$)");
$ACC->lCV3();


$zugaenge = array();
while($C = $ACC->getNextEntry())
	$zugaenge[] = $C->A("CloudUser");


syslog(LOG_INFO, "Checked accounts: ".  implode(", ", $zugaenge));


$e->loadPlugin("ubiquitous", "RSSParser");
$lastUser = null;
$lastDir = null;
$lastZugang = null;
foreach($zugaenge AS $zugang){
	#echo "-----------------\n";
	#echo $zugang."\n";
	
	
	$_GET["cloud"] = $zugang;
	$_SERVER["HTTP_HOST"] = $argv[1];
	
	Environment::reset();
	Session::reloadDBData();

	if(!$e->useUser()){
		#echo "Kein Benutzer!\n";
		DBStorage::disconnect();
		continue;
	}
	
	$ek = Util::eK();
	if($lastUser == $ek){
		syslog(LOG_ERR, "Changing cloud failed! Test 1");
		throw new Exception("Changing cloud failed 1 ($lastZugang > $zugang)!");
	}
	
	if($lastDir == FileStorage::getFilesDir()){
		syslog(LOG_ERR, "Changing cloud failed! Test 2");
		throw new Exception("Changing cloud failed 2 ($lastZugang($lastDir) > $zugang(".FileStorage::getFilesDir().")!");
	}
	
	$e->addClassPath(FileStorage::getFilesDir());

	try {
		syslog(LOG_INFO, "$zugang updating...");
		mRSSParser::update($zugang);
	}
	catch(FieldDoesNotExistException $ex){
		echo $zugang." ".get_class($ex).": ".$ex->getField()."\n";
	} catch(Exception $ex){
		echo $zugang." ".get_class($ex).": ".$ex->getMessage()."\n";
	}
	
	syslog(LOG_INFO, "$zugang finished");
	
	$lastZugang = $zugang;
	$lastUser = $ek;
	$lastDir = FileStorage::getFilesDir();
	DBStorage::disconnect();
}

$e->cleanUp();


flock($fp, LOCK_UN);
fclose($fp);
syslog(LOG_INFO, "Done.");
closelog();
?>