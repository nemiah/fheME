<?php
/*
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
 *  2007 - 2018, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnKalender");
require_once realpath(dirname(__FILE__) . "/../../system/connect.php");
$absolutePathToPhynx = realpath(dirname(__FILE__) . "/../../") . "/";

$E = new ExtConn($absolutePathToPhynx);
$E->addClassPath($absolutePathToPhynx . "ubiquitous/xCal");
$E->addClassPath(FileStorage::getFilesDir());
$E->useDefaultMySQLData();
$E->useUser();

$AC = anyC::get("xCal", "xCalServerActive", "1");
while ($S = $AC->getNextEntry()) {

	// XML
	$xml = file_get_contents($S->A("xCalUrl"));
	if($xml === false)
		continue;
	
	$S->changeA("xCalCache", $xml);
	$S->saveMe();
}

$E->cleanUp();
?>