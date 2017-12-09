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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
if(!file_exists("/var/www/status/cron_".gethostname()))
	die("Status file missing!");

if(file_exists("/var/www/status/cron_".gethostname())){
	$status = file_get_contents("/var/www/status/cron_".gethostname());
	if(trim($status) !== "active")
		die();
}

session_name("ExtConnGlobalOWM");

if(isset($argv[1]))
	$_SERVER["HTTP_HOST"] = $argv[1];

require_once realpath(dirname(__FILE__)."/../../system/connect.php");

$e = new ExtConn(Util::getRootPath());
$e->loadPlugin("plugins", "Cloud");

$absolutePathToPhynx = Util::getRootPath();

$e->addClassPath(FileStorage::getFilesDir());

$e->useDefaultMySQLData();

$ACC = new mCloud();
$ACC->addAssocV3("CloudOption", "=", "allowedPlugins");
$ACC->addAssocV3("CloudValue", "REGEXP", "(^mOpenWeatherMap,)|(,mOpenWeatherMap,)|(,mOpenWeatherMap$)|(^mOpenWeatherMap$)");
$ACC->lCV3();


$zugaenge = array();
while($C = $ACC->getNextEntry())
	$zugaenge[] = $C->A("CloudUser");

$fp = fopen("/home/nemiah/globalCronOWM.lock", "a");
if(!$fp)
	throw new Exception ("Could not create lock file /home/nemiah/globalCronOWM.lock");

if (!flock($fp, LOCK_EX | LOCK_NB))
	throw new Exception ("Could not acquire lock!");

$e->loadPlugin("ubiquitous", "OWM");
$lastUser = null;
$lastDir = null;
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
	if($lastUser == $ek)
		throw new Exception("Changing cloud failed!");
	
	if($lastDir == FileStorage::getFilesDir())
		throw new Exception("Changing cloud failed!");
	
	$e->addClassPath(FileStorage::getFilesDir());

	try {
		OpenWeatherMap::$apiKey = null;
		mOpenWeatherMap::update();
	}
	catch(FieldDoesNotExistException $ex){
		echo $zugang." ".get_class($ex).": ".$ex->getField()."\n";
	} catch(Exception $ex){
		echo $zugang." ".get_class($ex).": ".$ex->getMessage()."\n";
	}
	$lastUser = $ek;
	$lastDir = FileStorage::getFilesDir();
	DBStorage::disconnect();
}

$e->cleanUp();


flock($fp, LOCK_UN);
fclose($fp);
?>