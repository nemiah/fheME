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

session_name("ExtConnWetterstation");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);

$e->addClassPath(FileStorage::getFilesDir());
$e->loadPlugin("fheME", "Wetterstation");

$e->useDefaultMySQLData();

$e->useUser();

file_put_contents(__DIR__."/data.log", print_r($_POST, true));
#echo "<pre>";
try {
	/*Array
(
    [PASSKEY] => 86FBA8D673E41D9FB15C55D74218A5B2
    [stationtype] => EasyWeatherPro_V5.2.2
    [runtime] => 795
    [heap] => 21428
    [dateutc] => 2025-07-14 19:40:32
    [tempinf] => 75.2
    [humidityin] => 58
    [baromrelin] => 29.949
    [baromabsin] => 28.777
    [tempf] => 63.0
    [humidity] => 98
    [winddir] => 277
    [windspeedmph] => 0.00
    [windgustmph] => 0.00
    [maxdailygust] => 13.65
    [solarradiation] => 0.10
    [uv] => 0
    [rainratein] => 0.000
    [eventrainin] => 0.000
    [hourlyrainin] => 0.000
    [dailyrainin] => 0.000
    [weeklyrainin] => 0.031
    [monthlyrainin] => 0.142
    [yearlyrainin] => 68.961
    [totalrainin] => 68.961
    [vpd] => 0.012
    [wh65batt] => 0
    [freq] => 868M
    [model] => WS2900_V2.01.18
    [interval] => 8
)*/
	
	Wetterstation::log($_POST);
} catch (FieldDoesNotExistException $e){
	die($e->getField()." does not exist!\n");
}
$e->cleanUp();

#echo "</pre>";
?>