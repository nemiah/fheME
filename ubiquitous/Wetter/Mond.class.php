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
class Mond {

	public static function translate($string){
		$file = file(Util::getRootPath()."ubiquitous/Wetter/translation/moon-phases-de_DE.po");
		
		foreach($file AS $nr => $line)
			if(trim($line) == "msgid \"$string\"")
				return substr($file[$nr + 1], 8, -3);
			
		return $string;
	}
	
	public static function normalize($v) {
		$v -= floor($v);
		if ($v < 0) {
			$v += 1;
		}
		return $v;
	}

	public static function phase() {
		// Get date
		$y = date('Y');
		$m = date('n');
		$d = date('j');

		// Calculate julian day
		$yy = $y - floor((12 - $m) / 10);
		$mm = $m + 9;
		if ($mm >= 12)
			$mm = $mm - 12;
		

		$k1 = floor(365.25 * ($yy + 4712));
		$k2 = floor(30.6 * $mm + 0.5);
		$k3 = floor(floor(($yy / 100) + 49) * 0.75) - 38;

		$jd = $k1 + $k2 + $d + 59;
		if ($jd > 2299160)
			$jd = $jd - $k3;
		

		// Calculate the moon phase
		$ip = Mond::normalize(($jd - 2451550.1) / 29.530588853);
		$ag = $ip * 29.53;

		if ($ag < 1.84566) {
			$phase = Mond::translate('New Moon');
			$image = 'weather-night-newmoon.png';
		} else if ($ag < 5.53699) {
			$phase = Mond::translate('Waxing Crescent Moon');
			$image = 'weather-night-waxing-crescent.png';
		} else if ($ag < 9.22831) {
			$phase = Mond::translate('First Quarter Moon');
			$image = 'weather-night-firstquarter.png';
		} else if ($ag < 12.91963) {
			$phase = Mond::translate('Waxing Gibbous Moon');
			$image = 'weather-night-waxing-gibbous.png';
		} else if ($ag < 16.61096) {
			$phase = Mond::translate('Full Moon');
			$image = 'weather-night-fullmoon.png';
		} else if ($ag < 20.30228) {
			$phase = Mond::translate('Waning Gibbous Moon');
			$image = 'weather-night-waning-gibbous.png';
		} else if ($ag < 23.99361) {
			$phase = Mond::translate('Third Quarter Moon');
			$image = 'weather-night-lastquarter.png';
		} else if ($ag < 27.68493) {
			$phase = Mond::translate('Waning Crescent Moon');
			$image = 'weather-night-waning-crescent.png';
		} else {
			$phase = Mond::translate('New Moon');
			$image = 'weather-night-newmoon.png';
		}

		// Convert phase to radians
		$ip = $ip * 2 * pi();

		// Calculate moon's distance
		$dp = 2 * pi() * Mond::normalize(($jd - 2451562.2) / 27.55454988);
		$di = 60.4 - 3.3 * cos($dp) - 0.6 * cos(2 * $ip - $dp) - 0.5 * cos(2 * $ip);

		// Calculate moon's ecliptic latitude
		$np = 2 * pi() * Mond::normalize(($jd - 2451565.2) / 27.212220817);
		$la = 5.1 * sin($np);

		// Calculate moon's ecliptic longitude
		$rp = Mond::normalize(($jd - 2451555.8) / 27.321582241);
		$lo = 360 * $rp + 6.3 * sin($dp) + 1.3 * sin(2 * $ip - $dp) + 0.7 * sin(2 * $ip);

		// Calculate zodiac sign
		if ($lo < 30)
			$zodiac = Mond::translate('Aries');
		else if ($lo < 60)
			$zodiac = Mond::translate('Taurus');
		else if ($lo < 90)
			$zodiac = Mond::translate('Gemini');
		else if ($lo < 120)
			$zodiac = Mond::translate('Cancer');
		else if ($lo < 150)
			$zodiac = Mond::translate('Leo');
		else if ($lo < 180)
			$zodiac = Mond::translate('Virgo');
		else if ($lo < 210)
			$zodiac = Mond::translate('Libra');
		else if ($lo < 240)
			$zodiac = Mond::translate('Scorpio');
		else if ($lo < 270)
			$zodiac = Mond::translate('Sagittarius');
		else if ($lo < 300)
			$zodiac = Mond::translate('Capricorn');
		else if ($lo < 330)
			$zodiac = Mond::translate('Aquarius');
		else
			$zodiac = Mond::translate('Pisces');
		

		// Age
		$age = floor($ag);

		// Distance
		$distance = round(100 * $di) / 100;

		// Ecliptic latitude
		$latitude = round(100 * $la) / 100;

		// Ecliptic longitude
		$longitude = round(100 * $lo) / 100;
		if ($longitude > 360) {
			$longitude -= 360;
		}

		$O = new stdClass();
		
		$O->days = $age;
		$O->phase = $phase;
		$O->zodiac = $zodiac;
		$O->image = $image;
		return $O;
	}

}

?>
