<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
require "../classes/backend/BackgroundPluginState.class.php";
require "../classes/toolbox/Util.class.php";
session_name("phynx_".sha1(str_replace("".DIRECTORY_SEPARATOR."interface".DIRECTORY_SEPARATOR."showPDF.php","".DIRECTORY_SEPARATOR."system".DIRECTORY_SEPARATOR."connect.php",__FILE__)));
session_start();

if(!isset($_SESSION["BPS"]))
	die(Util::getBasicHTMLError("Ihre Sitzung ist nicht bekannt. Bitte loggen Sie sich ein.", "Sitzung unbekannt"));

$_SESSION["BPS"]->setActualClass("showPDF");
$f = $_SESSION["BPS"]->getACProperty("filename");
		
if($f == "") die("No filename set!");

header('Content-Type: application/pdf');
header('Content-Length: '.filesize($f));
header("Content-Disposition: inline; filename=\"".basename($f)."\"");

readfile($f);

if(BPS::getProperty("showPDF", "delete", true))
	unlink($f);

exit();
?>