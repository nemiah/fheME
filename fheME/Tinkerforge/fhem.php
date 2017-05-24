<?php
/**
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
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

$_GET["cloud"] = "Any";

$_SERVER["HTTP_HOST"] = "*";

session_name("ExtConnTF");

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



$e->cleanUp();

?>