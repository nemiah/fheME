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
define("PHYNX_LOW_FOOTPRINT", true);
$data = json_decode($argv[1]);

#session_name("ExtConnWetterstation");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);

$e->addClassPath(FileStorage::getFilesDir());
$e->loadPlugin("fheME", "Wetterstation");

$e->useDefaultMySQLData();

$e->useUser();


#echo "<pre>";
try {
	/*$data = [
		"PASSKEY" => "86FBA8D673E41D9FB15C55D74218A5B2",
		"stationtype" => "EasyWeatherPro_V5.0.2",
		"dateutc" => "2022-08-26 13:08:32",
		"tempinf" => "78.8",
		"humidityin" => "57",
		"baromrelin" => "29.728",
		"baromabsin" => "28.585",
		"tempf" => "79.9",
		"humidity" => "57",
		"winddir" => "286",
		"windspeedmph" => "1.12",
		"windgustmph" => "1.12",
		"maxdailygust" => "9.17",
		"solarradiation" => "481.99",
		"uv" => "4",
		"rainratein" => "0.000",
		"eventrainin" => "0.000",
		"hourlyrainin" => "0.000",
		"dailyrainin" => "0.000",
		"weeklyrainin" => "0.051",
		"monthlyrainin" => "0.051",
		"totalrainin" => "0.051",
		"wh65batt" => "0",
		"freq" => "868M",
		"model" => "WS2900_V2.01.18"
	];*/
	
	
	$DB = new DBStorage();
	$C = $DB->getConnection();
	
	$Q = $C->query("SELECT * FROM Wetterstation WHERE WetterstationID = 1");
	$R = $Q->fetch_object();
	
	$updateYesterday = "";
	if(date("Ymd", $R->WetterstationLastUpdate) != date("Ymd"))
		$updateYesterday = "
		WetterstationOutdoorRainTotalYesterday = '".$R->WetterstationOutdoorRainTotal."',";
	
	$C->query("UPDATE 
		`Wetterstation` 
	SET 
		WetterstationOutdoorTemp = '".$data->temperature_C."', 
		WetterstationOutdoorHumidity = '".$data->humidity."', 
		WetterstationLastUpdate = UNIX_TIMESTAMP(),
		WetterstationOutdoorRainTotal = '".$data->rain_mm."',$updateYesterday
		WetterstationOutdoorUVI = '".$data->uvi."'
	WHERE 
		WetterstationID = '1'");
} catch (FieldDoesNotExistException $e){
	die($e->getField()." does not exist!\n");
}
$e->cleanUp();

#echo "</pre>";
?>