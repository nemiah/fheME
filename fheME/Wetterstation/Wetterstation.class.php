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
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */
class Wetterstation extends PersistentObject {
	public static function log($data){
		$W = anyC::getFirst("Wetterstation", "WetterstationKey", $data["PASSKEY"]);
		if(!$W)
			throw new Exception("Does not exist!");
		
		$F = new Factory("WetterstationLog");
		
		$F->sA("WetterstationLogWetterstationID", $W->getID());
		$timeZone = date_default_timezone_get();
		date_default_timezone_set('UTC');
		$F->sA("WetterstationLogTime", strtotime($data["dateutc"]));
		date_default_timezone_set($timeZone);
		
		$F->sA("WetterstationLogIndoorTemp", $W->FtoC($data["tempinf"]));
		$F->sA("WetterstationLogIndoorHumidity", $data["humidityin"]);
		$F->sA("WetterstationLogOutdoorTemp", $W->FtoC($data["tempf"]));
		$F->sA("WetterstationLogOutdoorHumidity", $data["humidity"]);
		$F->sA("WetterstationLogOutdoorWindSpeed", $W->milesPerHToMetersPerS($data["windspeedmph"]));
		$F->sA("WetterstationLogOutdoorWindGust", $W->milesPerHToMetersPerS($data["windgustmph"]));
		$F->sA("WetterstationLogOutdoorRainDaily", $W->inchToMm($data["dailyrainin"]));
		$F->sA("WetterstationLogOutdoorRainRate", $W->inchToMm($data["rainratein"]));
		$F->sA("WetterstationLogOutdoorRainWeek", $W->inchToMm($data["weeklyrainin"]));
		$F->sA("WetterstationLogOutdoorRainMonth", $W->inchToMm($data["monthlyrainin"]));
		$F->sA("WetterstationLogOutdoorRainTotal", $W->inchToMm($data["totalrainin"]));
		$F->sA("WetterstationLogOutdoorUVI", $data["uv"]);
		$F->sA("WetterstationLogOutdoorSunRadiation", $data["solarradiation"]);
		
		$F->store();
	}
	
	public function FtoC($f){
		return ($f - 32) / 1.8000;
	}
	
	public function milesPerHToMetersPerS($mps){
		return $mps * 0.44704;
	}
	
	public function inchToMm($inch){
		return $inch * 25.4;
	}
}
?>