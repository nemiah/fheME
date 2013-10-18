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

session_name("ExtConnRC");

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

$e->loadPlugin("fheME", "RC");

$e->useDefaultMySQLData();
$e->useUser();



$e->cleanUp();

?>