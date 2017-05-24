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

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnEinkaufszettel");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);

$e->addClassPath($absolutePathToPhynx."/fheME/Einkaufszettel");
$e->addClassPath($absolutePathToPhynx."/ubiquitous/openEAN");

$e->useDefaultMySQLData();

$e->useUser();

$data = explode(";", $_GET["data"]);
foreach($data AS $k => $v)
	$data[$k] = LinuxKeycodes::codeToKey($v);

$E = new mEinkaufszettelGUI(-1);
$E->addEAN(strtolower(implode("", $data)), false);

$e->cleanUp();

?>