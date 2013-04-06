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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class Wetter extends PersistentObject {
	public function getData(){
		$data = file_get_contents("http://query.yahooapis.com/v1/public/yql?q=%20SELECT%20*%20FROM%20weather.forecast%20WHERE%20location%3D%22".$this->A("WetterWOEID")."%22and%20u%3D%22c%22&format=json");
		  
		return json_decode($data)->query->results->channel;
	}
	
	public static function translate($string){
		$file = file(Util::getRootPath()."ubiquitous/Wetter/translation/de.po");
		
		foreach($file AS $nr => $line)
			if(trim($line) == "msgid \"$string\"")
				return substr($file[$nr + 1], 8, -2);
			
		return $string;
	}
	
	public static function getWeatherCondition($code) {
		switch ($code) {
			case 0:/* tornado */
				return self::translate('Tornado');
			case 1:/* tropical storm */
				return self::translate('Tropical storm');
			case 2:/* hurricane */
				return self::translate('Hurricane');
			case 3:/* severe thunderstorms */
				return self::translate('Severe thunderstorms');
			case 4:/* thunderstorms */
				return self::translate('Thunderstorms');
			case 5:/* mixed rain and snow */
				return self::translate('Mixed rain and snow');
			case 6:/* mixed rain and sleet */
				return self::translate('Mixed rain and sleet');
			case 7:/* mixed snow and sleet */
				return self::translate('Mixed snow and sleet');
			case 8:/* freezing drizzle */
				return self::translate('Freezing drizzle');
			case 9:/* drizzle */
				return self::translate('Drizzle');
			case 10:/* freezing rain */
				return self::translate('Freezing rain');
			case 11:/* showers */
				return self::translate('Showers');
			case 12:/* showers */
				return self::translate('Showers');
			case 13:/* snow flurries */
				return self::translate('Snow flurries');
			case 14:/* light snow showers */
				return self::translate('Light snow showers');
			case 15:/* blowing snow */
				return self::translate('Blowing snow');
			case 16:/* snow */
				return self::translate('Snow');
			case 17:/* hail */
				return self::translate('Hail');
			case 18:/* sleet */
				return self::translate('Sleet');
			case 19:/* dust */
				return self::translate('Dust');
			case 20:/* foggy */
				return self::translate('Foggy');
			case 21:/* haze */
				return self::translate('Haze');
			case 22:/* smoky */
				return self::translate('Smoky');
			case 23:/* blustery */
				return self::translate('Blustery');
			case 24:/* windy */
				return self::translate('Windy');
			case 25:/* cold */
				return self::translate('Cold');
			case 26:/* cloudy */
				return self::translate('Cloudy');
			case 27:/* mostly cloudy (night) */
			case 28:/* mostly cloudy (day) */
				return self::translate('Mostly cloudy');
			case 29:/* partly cloudy (night) */
			case 30:/* partly cloudy (day) */
				return self::translate('Partly cloudy');
			case 31:/* clear (night) */
				return self::translate('Clear');
			case 32:/* sunny */
				return self::translate('Sunny');
			case 33:/* fair (night) */
			case 34:/* fair (day) */
				return self::translate('Fair');
			case 35:/* mixed rain and hail */
				return self::translate('Mixed rain and hail');
			case 36:/* hot */
				return self::translate('Hot');
			case 37:/* isolated thunderstorms */
				return self::translate('Isolated thunderstorms');
			case 38:/* scattered thunderstorms */
			case 39:/* scattered thunderstorms */
				return self::translate('Scattered thunderstorms');
			case 40:/* scattered showers */
				return self::translate('Scattered showers');
			case 41:/* heavy snow */
				return self::translate('Heavy snow');
			case 42:/* scattered snow showers */
				return self::translate('Scattered snow showers');
			case 43:/* heavy snow */
				return self::translate('Heavy snow');
			case 44:/* partly cloudy */
				return self::translate('Partly cloudy');
			case 45:/* thundershowers */
				return self::translate('Thundershowers');
			case 46:/* snow showers */
				return self::translate('Snow showers');
			case 47:/* isolated thundershowers */
				return self::translate('Isolated thundershowers');
			case 3200:/* not available */
			default:
				return self::translate('Not available');
		}
	}
	
	public static function getWeatherIcon($code) {
		switch ($code) {
			case 0:/* tornado */
				return array('weather-tornado');
			case 1:/* tropical storm */
				return array('weather-severe-alert');
			case 2:/* hurricane */
				return array('weather-hurricane');
			case 3:/* severe thunderstorms */
				return array('weather-severe-alert');
			case 4:/* thunderstorms */
				return array('weather-storm');
			case 5:/* mixed rain and snow */
				return array('weather-showers-scattered-snow', 'weather-snow');
			case 6:/* mixed rain and sleet */
				return array('weather-showers-scattered-snow', 'weather-snow');
			case 7:/* mixed snow and sleet */
				return array('weather-snow');
			case 8:/* freezing drizzle */
				return array('weather-freezing-rain', 'weather-showers');
			case 9:/* drizzle */
				return array('weather-fog');
			case 10:/* freezing rain */
				return array('weather-freezing-rain', 'weather-showers');
			case 11:/* showers */
				return array('weather-showers');
			case 12:/* showers */
				return array('weather-showers');
			case 13:/* snow flurries */
				return array('weather-snow');
			case 14:/* light snow showers */
				return array('weather-snow');
			case 15:/* blowing snow */
				return array('weather-snow');
			case 16:/* snow */
				return array('weather-snow');
			case 17:/* hail */
				return array('weather-hail');
			case 18:/* sleet */
				return array('weather-snow');
			case 19:/* dust */
				return array('weather-dusty');
			case 20:/* foggy */
				return array('weather-fog');
			case 21:/* haze */
				return array('weather-hazy');
			case 22:/* smoky */
				return array('weather-fog');
			case 23:/* blustery */
				return array('weather-few-clouds');
			case 24:/* windy */
				return array('weather-lightwind');
			case 25:/* cold */
				return array('weather-few-clouds');
			case 26:/* cloudy */
				return array('weather-overcast');
			case 27:/* mostly cloudy (night) */
				return array('weather-night-fullmoon-few-clouds', 'weather-few-clouds-night');
			case 28:/* mostly cloudy (day) */
				return array('weather-overcast');#'weather-clouds'
			case 29:/* partly cloudy (night) */
				return array('weather-night-fullmoon-cloudy');
			case 30:/* partly cloudy (day) */
				return array('weather-few-clouds');
			case 31:/* clear (night) */
				return array('weather-clear-night');
			case 32:/* sunny */
				return array('weather-clear');
			case 33:/* fair (night) */
				return array('weather-clear-night');
			case 34:/* fair (day) */
				return array('weather-clear');
			case 35:/* mixed rain and hail */
				return array('weather-snow-rain', 'weather-showers');
			case 36:/* hot */
				return array('weather-clear-very-hot');
			case 37:/* isolated thunderstorms */
				return array('weather-storm');
			case 38:/* scattered thunderstorms */
				return array('weather-storm');
			case 39:/* http://developer.yahoo.com/forum/YDN-Documentation/Yahoo-Weather-API-Wrong-Condition-Code/1290534174000-1122fc3d-da6d-34a2-9fb9-d0863e6c5bc6 */
			case 40:/* scattered showers */
				return array('weather-showers-scattered', 'weather-showers');
			case 41:/* heavy snow */
				return array('weather-snow');
			case 42:/* scattered snow showers */
				return array('weather-snow');
			case 43:/* heavy snow */
				return array('weather-snow');
			case 44:/* partly cloudy */
				return array('weather-few-clouds');
			case 45:/* thundershowers */
				return array('weather-storm');
			case 46:/* snow showers */
				return array('weather-snow');
			case 47:/* isolated thundershowers */
				return array('weather-storm');
			case 3200:/* not available */
			default:
				return array('weather-severe-alert');
		}
	}
}
?>