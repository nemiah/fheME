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
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */
set_time_limit(0);

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnPV");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);
#$e->forbidCustomizers();

$e->addClassPath(FileStorage::getFilesDir());
$e->loadPlugin("fheME", "Zweirad");

$e->useDefaultMySQLData();

$e->useUser();


#echo "<pre>";
try {
	Zweirad::APIUpdate();
} catch (FieldDoesNotExistException $e){
	die($e->getField()." does not exist!\n");
}
$e->cleanUp();

#echo "</pre>";
?>