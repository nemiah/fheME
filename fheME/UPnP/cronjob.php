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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnUPnP");
define("PHYNX_LOW_FOOTPRINT", true);

require_once realpath(dirname(__FILE__)."/../../system/connect.php");
#print_r(get_included_files());
register_shutdown_function('cronShutdownHandler');
function cronShutdownHandler() {
	$last_error = error_get_last();
	if ($last_error['type'] !== E_ERROR) 
		return;
	
	print_r(SysMessages::i()->getMessages());
}

$e = new ExtConn(realpath(dirname(__FILE__)."/../../")."/");

$e->loadPlugin("fheME", "UPnP");

$e->useDefaultMySQLData();

$e->useUser();

$U = new mUPnP();
$U->discoverNow(null, true, true);

$e->cleanUp();

?>