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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

echo "<pre>";

$uri = "http://localhost".dirname($_SERVER["SCRIPT_NAME"])."/referenceSOAPServer.php";

$options = array(
	"location" => $uri,
	"uri" => $uri);

$S = new SoapClient(null, $options);

try {
	$Devices = $S->getDevices();
	print_r($Devices);

	$Presets = $S->getPresets();
	print_r($Presets);

	$S->setPreset("Voll");

} catch(SoapFault $e){
	print_r($e);
	die();
}

echo "</pre>";
?>