<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
class OpenWeatherMap extends PersistentObject {
	public static $apiKey;
	
	function newAttributes() {
		$A = parent::newAttributes();
		
		$A->OpenWeatherMapUpdateInterval = 60;
		
		return $A;
	}
	
	public static function current(){
		$UID = Session::currentUser()->getID();
		$AC = anyC::get("OpenWeatherMap");
		$AC->addAssocV3("OpenWeatherMapUserIDs", "LIKE", $UID.";:;%");
		$AC->addAssocV3("OpenWeatherMapUserIDs", "=", $UID, "OR");
		$AC->addAssocV3("OpenWeatherMapUserIDs", "LIKE", "%;:;".$UID.";:;%", "OR");
		$AC->addAssocV3("OpenWeatherMapUserIDs", "LIKE", "%;:;".$UID);
		$W = $AC->n();
		if(!$W)
			return null;
		
		if($W->A("OpenWeatherMapLastUpdate") + 5 * 60 < time())
			$W->download();
		
		return $W;
	}
	
	public function download(){
		$update = false;
		$dataCurrent = file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=".$this->A("OpenWeatherMapCityID")."&lang=de&units=metric&APPID=".$this->apiKey());

		if($dataCurrent !== false){
			$this->changeA("OpenWeatherMapDataCurrent", $dataCurrent);
			$update = true;
		}
		
		$dataForecast = file_get_contents("http://api.openweathermap.org/data/2.5/forecast?id=".$this->A("OpenWeatherMapCityID")."&lang=de&units=metric&APPID=".$this->apiKey());
		if($dataForecast !== false){
			$this->changeA ("OpenWeatherMapDataForecast", $dataForecast);
			$update = true;
		}
		
		$dataForecastDaily = file_get_contents("http://api.openweathermap.org/data/2.5/forecast/daily?id=".$this->A("OpenWeatherMapCityID")."&lang=de&units=metric&APPID=".$this->apiKey());
		if($dataForecast !== false){
			$this->changeA ("OpenWeatherMapDataForecastDaily", $dataForecastDaily);
			$update = true;
		}
		
		if($update){
			$this->changeA("OpenWeatherMapLastUpdate", time());
			
			if($this->hasParsers)
				$this->changeA("OpenWeatherMapLastUpdate", Util::CLDateTimeParser(time()));
			$this->saveMe();
		}
	}
	
	public function apiKey(){
		if(self::$apiKey === null)
			self::$apiKey = mUserdata::getGlobalSettingValue("OWMApiKey", "");
		
		return self::$apiKey;
	}
	
	public static function icon($code){
		$list = array(
			"01d" => "wi-day-sunny",
			"02d" => "wi-day-sunny-overcast",
			"03d" => "wi-day-cloudy",
			"04d" => "wi-cloudy",
			"09d" => "wi-rain",
			"10d" => "wi-day-rain",
			"11d" => "wi-thunderstorm",
			"13d" => "wi-snow",
			"50d" => "wi-fog",
			
			"01n" => "wi-night-clear",
			"02n" => "wi-night-partly-cloudy",
			"03n" => "wi-night-cloudy",
			"04n" => "wi-cloudy",
			"09n" => "wi-rain",
			"10n" => "wi-night-rain",
			"11n" => "wi-night-thunderstorm",
			"13n" => "wi-snow",
			"50n" => "wi-fog"
		);
		
		if(!isset($list[$code]))
			return "wi-alien";
		
		return $list[$code];
	}
	
	public static function iconPng($code){
		$list = array(
			"01d" => "weather-clear",
			"02d" => "weather-very-few-clouds",
			"03d" => "weather-few-clouds",
			"04d" => "weather-overcast",
			"09d" => "weather-showers",
			"10d" => "weather-showers-scattered",
			"11d" => "weather-storm",
			"13d" => "weather-snow",
			"50d" => "weather-fog",
			
			"01n" => "weather-clear-night",
			"02n" => "weather-night-fullmoon-few-clouds",
			"03n" => "weather-night-fullmoon-cloudy",
			"04n" => "weather-overcast",
			"09n" => "weather-showers",
			"10n" => "weather-night-fullmoon-rain",
			"11n" => "weather-night-fullmoon-storm",
			"13n" => "weather-night-fullmoon-snow",
			"50n" => "weather-fog"
		);
		
		if(!isset($list[$code]))
			return "weather-tornado";
		
		return $list[$code];
	}
	
	
}
?>