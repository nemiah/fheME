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
header("Content-type: text/javascript");

require_once "../libraries/minify/JSMin.php";

$root = realpath(dirname(__FILE__)."/../");

foreach($_GET["path"] AS $path){
	if(strpos($path, "./javascript/") !== 0 AND strpos($path, "./libraries/") !== 0 AND strpos($path, "./ubiquitous/Wysiwyg/") !== 0)
		continue;
	#echo file_get_contents($root."/".$path)."\n\n";
	echo JSMin::minify(file_get_contents($root."/".$path))."\n\n";
	
	#echo $path."\n";
}
?>