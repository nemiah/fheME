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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class Util {
	public static function ext($filename){
		return trim(strtolower(pathinfo($filename, PATHINFO_EXTENSION)));
	}
	
	public static function isDirEmpty($dir) {
		if (!is_readable($dir))
			return null; 
		
		$handle = opendir($dir);
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				return false;
			}
		}
		return true;
	  }
	
	/**
	 * From http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
	 * 
	 * @param array $rgb
	 * @return string
	 */
	public static function rgb2hex($rgb) {
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

		return $hex; // returns the hex value including the number sign (#)
	}

	public static function filesTree($files){

		$zipDirectories = array();
		foreach($files AS $file)
			if(substr($file, -1) == "/")
				$zipDirectories[dirname($file)] = array();
			else {
				if(strpos($file, "/") !== false)
					$zipDirectories[dirname($file)][] = basename($file);
				else
					$zipDirectories[] = $file;
			}
		
		
		$reverse = array_reverse($zipDirectories, true);
		foreach($reverse AS $dir => $content){
			$path = explode("/", $dir);
			if(count($path) < 2)
				continue;
			
			$zipDirectories[dirname($dir)][basename($dir)] = $content;
			unset($zipDirectories[$dir]);
		}
		
		if(isset($zipDirectories["."]))
			unset($zipDirectories["."]);
		
		return $zipDirectories;
	}
	
	public static function getCloudHost($host = null){
		if($host == null AND isset($_SERVER["HTTP_HOST"]))
			$host = $_SERVER["HTTP_HOST"];
		
		$host = Aspect::joinPoint("host", null, __METHOD__, array($host), $host);
		
		if($host == "*" OR $host === null)
			return null;
		
		$h = "CloudHost".str_replace(array(":", "-"), "_", implode("", array_map("ucfirst", explode(".", $host))));

		if(defined("PHYNX_VIA_INTERFACE")){
			if(file_exists(Util::getRootPath()."specifics/$h.class.php"))
				require_once(Util::getRootPath()."specifics/$h.class.php");

			if(file_exists(Util::getRootPath()."specifics/CloudHostAny.class.php"))
				require_once(Util::getRootPath()."specifics/CloudHostAny.class.php");
		}
		
		try {
			if(defined("PHYNX_VIA_INTERFACE") AND !class_exists($h, false))
				throw new ClassNotFoundException($h);
			
			$c = new $h();
			return Aspect::joinPoint("fix", null, __METHOD__, array($c), $c);;
		} catch (ClassNotFoundException $e){
			try {
				if(defined("PHYNX_VIA_INTERFACE") AND !class_exists("CloudHostAny", false))
					throw new ClassNotFoundException("CloudHostAny");
			
				$c = new CloudHostAny();
				return $c;
			} catch (ClassNotFoundException $e){
				return null;
			}
		}
	}
	
	public static function makeHTMLMail($html, $styles = null){
		if(stripos($html, "</p>") === false)
			return $html;
			
		if(stripos($html, "</html>") !== false)
			return $html;
		
		if($styles == null)
			$styles = array(
				"p" => "font-size: 12pt;font-family:sans-serif;",
				"li" => "font-size: 12pt;font-family:sans-serif;",
				"h1" => "font-size: 18pt;font-family:sans-serif;",
				"h2" => "font-size: 16pt;font-family:sans-serif;",
				"h3" => "font-size: 15pt;font-family:sans-serif;",
				"h4" => "font-size: 13pt;font-family:sans-serif;",
				"h5" => "font-size: 13pt;font-family:sans-serif;",
				"h6" => "font-size: 13pt;font-family:sans-serif;"
			);
		
		foreach($styles AS $tag => $style){
			$html = str_replace("<$tag style=\"", "<$tag style=\"".trim(str_replace("\n", "", $style))."", $html);
			$html = str_replace("<$tag>", "<$tag style=\"".trim(str_replace("\n", "", $style))."\">", $html);
		}
		
		return "<html>
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">
  </head>
  <body style=\"background-color:#FFFFFF;color:#222222;\">
  ".$html."
  </body>
</html>";
	}
	
	public static function toBytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		switch ($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
	
	/**
	 * Create fixed-size string
	 * 
	 * @param string $string
	 * @param int $width
	 * @param int $pad_type STR_PAD_RIGHT OR STR_PAD_LEFT
	 * @deprecated since version 23.09.2012
	 */
	public static function utf8_str_col($string, $width, $pad_string = " ", $pad_type = STR_PAD_RIGHT){
		return phynx_mb_str_pad(mb_substr($string, 0, $width), $width, $pad_string, $pad_type, "UTF-8");
		/*preg_match_all("/./su", $string, $ar);
		
		$ar = array_slice($ar[0], 0, $width);
		
		switch($pad_type){
			case STR_PAD_RIGHT:
				while(count($ar) < $width)
					$ar[] = $pad_string[0];
			break;
				
			case STR_PAD_LEFT:
				while(count($ar) < $width)
					array_unshift($ar, $pad_string[0]);
			break;
		}
		
		return implode("", $ar);*/
	}
	
	public static function getAppServerClient($auth = true){
		try {
			$AppServer = mUserdata::getGlobalSettingValue("AppServer", "");
			if($AppServer != ""){
				$CU = Session::currentUser();
				$uri = $AppServer."/plugins/AppServer/AppServer.php";
				
				if($CU != null AND $auth){
					$c = Session::currentUser()->getA();

					return new SoapClient(null, array(
						"location" => $uri,
						"uri" => $uri,
						#"trace" => 1,
						"login" => $c->username,
						"password" => $c->SHApassword));
				} else {
					return new SoapClient(null, array(
						"location" => $uri,
						"uri" => $uri/*,
						"trace" => 1*/));
				}

				#$user = $S->getUser($username, $password);
			}
		} catch (Exception $e){}

		return null;
	}

	public static function getAppClient($App, $local = false, $username = null, $password = null){
		$host = "";
		if(!$local){
			$S = self::getAppServerClient($username != null);

			if($S == null)
				throw new Exception("Es steht kein AppServer zur Verfügung!");

			$Apps = $S->getApplications();

			$host = $Apps[$App][0];
		} else
			$host = AppProvider::getAppHost($App);
		
		if($host == "" OR $host == null)
			throw new Exception("Es steht keine Installation von $App zur Verfügung!");

		$uri = $host."/$App/ExtConn/ExtConn.php";
		if($username != null)
			$S2 = new SoapClient(null, array(
				"location" => $uri,
				"uri" => $uri,
				"login" => $username,
				"password" => $password/*,
				"trace" => 1*/));
		else
			$S2 = new SoapClient(null, array(
				"location" => $uri,
				"uri" => $uri/*,
				"trace" => 1*/));

		return $S2;
	}

	public static function getCountryAddressFormat($ISOCountry){
		$r = "";

		switch($ISOCountry){
			case "GB":
			case "US":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{zusatz1}\n";
				$r .= "{nr}{strasse}\n";
				$r .= "{zusatz2}\n";
				$r .= "{ort}\n";
				$r .= "{plz}\n";
				$r .= "{land}";
			break;
		
			case "CH":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{zusatz1}\n";
				$r .= "{strasse}{nr}\n";
				$r .= "{plz}{ort}\n";
				$r .= "{land}";
			break;
			
			case "LU":
			case "GR":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{nr}, {strasse}\n";
				$r .= "{plz}{ort}\n";
				$r .= "{land}";
			break;
		
			case "BE":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{strasse}, {nr}\n";
				$r .= "{plz}{ort}\n";
				$r .= "{land}";
			break;
		
			case "DK":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{strasse}{nr}\n";
				$r .= "{bezirk}\n";
				$r .= "{plz}{ort}\n";
				$r .= "{land}";
			break;
			
			case "HU":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{ort}\n";
				$r .= "{strasse}{nr}\n";
				$r .= "{plz}\n";
				$r .= "{land}";
			break;
		
			case "LT":
			case "HR":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{strasse}{nr}\n";
				$r .= "$ISOCountry-{plz}{ort}\n";
				$r .= "{land}";
			break;
			
			case "ES":
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{bezirk}\n";
				$r .= "{strasse}{nr}\n";
				$r .= "{plz}{ort}\n";
				$r .= "{land}";
			break;
		
			case "SK":
			case "NL":
			case "FR":
			case "SI":
			case "RO":
			case "CZ":
			case "PL":
			case "IT":
			default:
				$r .= "{firma}\n";
				$r .= "{abteilung}\n";
				$r .= "{position}{vorname}{nachname}{titelSuffix}\n";
				$r .= "{strasse}{nr}\n";
				$r .= "{zusatz2}\n";
				$r .= "{plz}{ort}\n";
				$r .= "{land}";
			break;
		}

		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		return Aspect::joinPoint("after", null, __METHOD__, $r);
		// </editor-fold>
	}

	public static function getMaxUpload(){
		$badFormat = ini_get("upload_max_filesize");
		$maxSize = 0;

		if(stripos($badFormat, "m") !== false)
			$maxSize = substr($badFormat, 0, strlen($badFormat)-1)*1024*1024;

		if(stripos($badFormat, "k") !== false)
			$maxSize = substr($badFormat, 0, strlen($badFormat)-1)*1024;

		if(stripos($badFormat, "g") !== false)
			$maxSize = substr($badFormat, 0, strlen($badFormat)-1)*1024*1024*1024;

		return $maxSize;
	}

	public static function getMaxMemory(){
		$badFormat = ini_get("memory_limit");
		$maxSize = 0;

		if(stripos($badFormat, "m") !== false)
			$maxSize = substr($badFormat, 0, strlen($badFormat)-1)*1024*1024;

		if(stripos($badFormat, "k") !== false)
			$maxSize = substr($badFormat, 0, strlen($badFormat)-1)*1024;

		if(stripos($badFormat, "g") !== false)
			$maxSize = substr($badFormat, 0, strlen($badFormat)-1)*1024*1024*1024;

		return $maxSize;
	}

	public static function getRootPath(){
		return str_replace("classes".DIRECTORY_SEPARATOR."toolbox".DIRECTORY_SEPARATOR."Util.class.php","",__FILE__);
	}

	public static function PostToHostCustom($host, $port = 80, $path = "/", $data = "", array $headers = null){
		$fp = fsockopen($host, $port);
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host:$port\r\n");
		
		foreach($headers AS $k => $v)
			fputs($fp, "$k:$v\r\n");
		
		if(!isset($headers["Content-type"]))
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		
		fputs($fp, "Content-length: ". strlen($data) ."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data);

		$res = "";

		while(!feof($fp))
			$res .= fgets($fp, 128);

		#printf("Done!\n");
		fclose($fp);

		return $res;
	}
	
	/**
	 * Setzt einen Post-Request auf Port 80 ab
	 *
	 * Kopiert von http://www.php-faq.de/q-code-post.html
	 * 
	 * @param string $host z.B. "www.linux.com"
	 * @param string $path z.B. "/polls/index.phtml"
	 * @param string $referer z.B. "http://www.linux.com/polls/index.phtml?pid=14"
	 * @param string $data_to_send z.B. "pid=14&poll_vote_number=2"
	 * @return string
	 */
	public static function PostToHost($host, $port = 80, $path = "", $referer = "", $data_to_send = "", $user = null, $pass = null, $contentType = "application/x-www-form-urlencoded") {
		$fp = fsockopen($host, $port);
		#printf("Open!\n");
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host:$port\r\n");
		fputs($fp, "Referer: $referer\r\n");
		if($user != null AND $pass != null){
			$string = base64_encode("$user:$pass");
			fputs($fp, "Authorization: Basic ".$string."\r\n");
		}
		fputs($fp, "Content-type: $contentType\r\n");
		fputs($fp, "Content-length: ". strlen($data_to_send) ."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data_to_send);
		#printf("Sent!\n");

		$res = "";

		while(!feof($fp))
			$res .= fgets($fp, 128);

		#printf("Done!\n");
		fclose($fp);

		return $res;
	}


	public static function isWindowsHost(){
		return stripos(getenv("OS"), "Windows") !== false;
	}
	
	public static function isLinuxHost(){
		return stripos(PHP_OS, "Linux") !== false;
	}

	/**
	 * Formatiert eine Zahl $number nach Sprache $language
	 * Wenn $digits angegeben wird, so wird die Einstellung der Sprache überschrieben
	 * $digits kann auch das Format "max2" haben. In diesem Fall werden Nullen am Ende
	 * abgeschnitten bei maximal 2 Nachkommastellen
	 */
	public static function formatNumber($language, $number, $digits = "default", $showZero = true, $endingZeroes = true, $thousandSeparator = true){
		$format = Util::getLangNumbersFormat($language);
		$ren = false;
		
		if(strstr($digits, "max")) {
			$ren = true;
			$digits = str_replace("max","",$digits) * 1;
		}
		
		$float = Util::parseFloat($language, $number);

		$stringNumber = number_format($float, ($digits === "default" ? $format[1] : $digits), $format[0], $thousandSeparator ? $format[2] : "")."";

		if($ren){
			for($i = 0; $i < $digits; $i++){
				if($stringNumber{strlen($stringNumber) - 1 - $i} == "0") $stringNumber{strlen($stringNumber) - 1 - $i} = " ";
				else break;
			}
			
			$stringNumber = trim($stringNumber);
			if($stringNumber{strlen($stringNumber) - 1} == $format[0]) {
				$stringNumber{strlen($stringNumber) - 1} = " ";
				$stringNumber = trim($stringNumber);
			}
		}
		
		if(!$showZero AND $stringNumber == "0") $stringNumber = "";
		
		if(!$endingZeroes)
			$stringNumber = str_replace($format[0].str_pad("",($digits == "default" ? $format[1] : $digits),"0"),"",$stringNumber);
		
		return $stringNumber;
	}
	
	public static function CLFormatNumber($number, $digits = "default", $showZero = true, $endingZeroes = true, $thousandSeparator = true){
		return Util::formatNumber($_SESSION["S"]->getUserLanguage(), $number * 1, $digits, $showZero, $endingZeroes, $thousandSeparator);
	}
	
	public static function formatByCurrency($currency, $number, $useSymbol = false, $dezimalstellen = null){
		$format = Util::getCurrencyFormat($currency, $useSymbol);

		$float = $number * 1;
		
		$negative = false;
		if($float < 0) $negative = true;
		$float = abs($float);

		if($dezimalstellen != null)
			$format[4] = $dezimalstellen;

		$stringCurrency = number_format(Util::kRound($float, $format[4]), $format[4], $format[3], $format[5]);
		$stringCurrency = str_replace("n", $stringCurrency, $negative ? $format[2] : $format[1]);
		
		return $stringCurrency;
	}
	
	/**
	 * Formatiert einen String oder eine Zahl als Währung
	 * $number sollte entweder in der Sprache vorliegen, in der die Währung ausgegeben wird
	 * oder als float oder int
	 */
	public static function formatCurrency($language, $number, $withSymbol = false, $dezimalstellen = null){
		$format = Util::getLangCurrencyFormat($language);
		
		$float = Util::parseFloat($language, $number);
		
		#$float *= Util::getLangCurrencyFactor($language);
		
		$negative = false;
		if($float < 0) $negative = true;
		$float = abs($float);

		if($dezimalstellen != null) $format[4] = $dezimalstellen;

		$stringCurrency = number_format(Util::kRound($float, $format[4]), $format[4], $format[3], $format[5]);
		$stringCurrency = str_replace("n", $stringCurrency, $negative ? $format[2] : $format[1]);
		
		if(!$withSymbol) $stringCurrency = trim(str_replace($format[0], "", $stringCurrency));
		
		return $stringCurrency;
	}
	
	/*public static function getLangCurrencyFactor($language){
		if(!Session::isPluginLoaded("mSprache")) return 1;
		
		$Sprache = anyC::getFirst("Sprache", "SpracheIdentifier", $language);
		
		$faktor = 1;
		if($Sprache != null)
			$faktor = $Sprache->A("SpracheWaehrungFaktor") * 1;
		
		if($Sprache == null){
			$Sprache = anyC::getFirst("Sprache", "CONCAT(SpracheIdentifier, '_', SpracheWaehrung) ", $language);
			if($Sprache != null)
				$faktor = $Sprache->A("SpracheWaehrungFaktor") * 1;
		}
		
		return $faktor;
	}*/
	
	public static function CLFormatCurrency($number, $withSymbol = false){
		return Util::formatCurrency($_SESSION["S"]->getUserLanguage(), $number * 1, $withSymbol);
	}
	
	public static function CLNumberParser($number, $l = "load"){
		if($l == "load") return Util::formatNumber($_SESSION["S"]->getUserLanguage(), $number * 1, 0, true, false);
		if($l == "store") return Util::parseFloat($_SESSION["S"]->getUserLanguage(), $number);
	}
	
	public static function CLTimeParser($time, $l = "load"){
		if($l == "load") return Util::formatTime($_SESSION["S"]->getUserLanguage(), $time);
		if($l == "store") return Util::parseTime($_SESSION["S"]->getUserLanguage(), $time);
	}

	public static function CLTimeParserE($time, $l = "load"){
		if($l == "load" AND $time == "-1") return "";
		if($l == "store" AND $time == "") return "-1";

		return self::CLTimeParser($time, $l);
	}
	
	public static function CLHoursParser($time, $l = "load"){
		if($l == "load") return Util::formatSeconds($time, false, $_SESSION["S"]->getUserLanguage());
		if($l == "store") return Util::parseTime($_SESSION["S"]->getUserLanguage(), $time);
	}
	
	public static function CLNumberParserZ($number, $l = "load"){
		if($l == "load") {
			$n = Util::formatNumber($_SESSION["S"]->getUserLanguage(), $number * 1, 3, true, true);
			$l = strlen($n) - 1;
			if($n[$l] == "0") $n = substr($n, 0, $l);
			return $n;
		}
		if($l == "store") return Util::parseFloat($_SESSION["S"]->getUserLanguage(), $number);
	}
	
	public static function CLFormatAnrede(Adresse $Adresse, $shortmode = false, $lessFormal = false, $perDu = false){
		return self::formatAnrede(Session::getLanguage(), $Adresse, $shortmode, $lessFormal, $perDu);
	}
	
	public static function formatAnrede($language, Adresse $Adresse, $shortmode = false, $lessFormal = false, $perDu = false){
		$format = self::getLangAnrede($language, $lessFormal);

		switch($Adresse->A("anrede")){
			case "2":
				if($shortmode) $A = $format["maleShort"].($Adresse->A("titelPrefix") != "" ? " ".$Adresse->A("titelPrefix") : "");
				else {
					$A = $format["male"].($Adresse->A("titelPrefix") != "" ? " ".$Adresse->A("titelPrefix") : "")." ".trim($Adresse->A("nachname"));
					if(trim($Adresse->A("nachname")) == "")
						$A = $format["unknown"];
				}
				
				if($perDu == true)
					$A = "Hallo ".trim($Adresse->A("vorname"));
			break;
			case "1":
				if($shortmode) $A = $format["femaleShort"].($Adresse->A("titelPrefix") != "" ? " ".$Adresse->A("titelPrefix") : "");
				else {
					$A = $format["female"].($Adresse->A("titelPrefix") != "" ? " ".$Adresse->A("titelPrefix") : "")." ".trim($Adresse->A("nachname"));
					if(trim($Adresse->A("nachname")) == "")
						$A = $format["unknown"];
				}
				
				if($perDu == true)
					$A = "Hallo ".trim($Adresse->A("vorname"));
			break;
			case "3":
				if($shortmode) $A = "";
				else $A = $format["unknown"];
				
				if($perDu == true)
					$A = "Hallo";
			break;
			case "4":
				if($shortmode) $A = $format["familyShort"];
				else $A = $format["family"]." ".trim($Adresse->A("nachname"));
				
				if($perDu == true)
					$A = "Hallo";
			break;
			default:
				if($shortmode) $A = "";
				else $A = $format["unknown"];
				
				if($perDu == true)
					$A = "Hallo";
			break;
		}
		
		$args = func_get_args();
		return Aspect::joinPoint("alterAnrede", null, __METHOD__, $args, $A);
	}
	
	public static function CLFormatDate($timeStamp = -1, $long = false){
		return self::formatDate($_SESSION["S"]->getUserLanguage(), $timeStamp, $long);
	}
	
	public static function CLCheckDate($CLDate){
		$format = Util::getLangDateFormat(Session::getLanguage());
		
		$split = explode($format[1],$CLDate);
		$refer = explode($format[1],$format[0]);
		
		if(count($split) != 3) return false;

		$monat = $split[array_search("m",$refer)];
		$tag   = $split[array_search("d",$refer)];
		$jahr  = $split[array_search("Y",$refer)];
		
		if($tag < 1 OR $tag > 31) return false;
		if($monat < 1 OR $monat > 12) return false;
		
		return checkdate($monat, $tag, $jahr);
	}
	
	public static function formatDate($language, $timeStamp = -1, $long = false){
		if($timeStamp == -1) $timeStamp = time();
		$format = Util::getLangDateFormat($language);
		
		if(!$long) $format = $format[0];
		else $format = $format[2];
		
		$weekdayNames = Util::getLangWeekdayNames($language);
		$monthNames = Util::getLangMonthNames($language);

		$date = date($format, $timeStamp);
		$date = str_replace(date("l", $timeStamp), $weekdayNames[date("w", $timeStamp)], $date);
		$date = str_replace(date("F", $timeStamp), $monthNames[date("m", $timeStamp)*1], $date);
		
		return $date;
	}
	
	public static function CLMonthName($number){
		$monthNames = Util::getLangMonthNames($_SESSION["S"]->getUserLanguage());
		return $monthNames[$number*1];
	}
	
	public static function CLWeekdayName($number){
		$weekdayNames = Util::getLangWeekdayNames($_SESSION["S"]->getUserLanguage());
		return $weekdayNames[$number*1];
	}

	public static function CLDateParser($date, $l = "load"){
		if($l == "load") return Util::formatDate($_SESSION["S"]->getUserLanguage(), $date);
		if($l == "store") return Util::parseDate($_SESSION["S"]->getUserLanguage(), $date);
	}

	public static function CLDateTimeParser($dateTime, $l = "load"){
		if($dateTime == "0" AND $l == "load") return "";
		if($dateTime == "" AND $l == "store") return "0";
		
		if($l == "load") return Util::CLDateParser($dateTime)." ".Util::CLTimeParser($dateTime);
		if($l == "store") {
			$ex = explode(" ", $dateTime);
			return Util::CLDateParser($ex[0], "store")-60+Util::CLTimeParser($ex[1], "store");
		}
	}

	public static function CLDateParserE($date, $l = "load"){
		if($date == null AND $l == "load") return "";
		if($date == "0" AND $l == "load") return "";
		if($date == "" AND $l == "store") return "0";
		if($l == "load") return Util::formatDate($_SESSION["S"]->getUserLanguage(), $date);
		if($l == "store") return Util::parseDate($_SESSION["S"]->getUserLanguage(), $date);
	}
	
	public static function CLDateParserL($date, $l = "load"){
		if($l == "load") return Util::formatDate($_SESSION["S"]->getUserLanguage(), $date, true);
		return "mode $l not supported";
	}
	
	public static function formatTime($language, $seconds, $showSeconds = false){
		$format = Util::getLangTimeFormat($language);
		
		if(!$showSeconds) $f = $format[1];
		else $f = $format[0];
		
		return date($f, $seconds - ($seconds <= 3600 * 24 * 7 ? 3600 : 0));
	}
	
	public static function parseTime($language, $time){
		$format = Util::getLangTimeFormat($language);
		
		$fak = 1;
		if($time[0] == "-"){
			$time = substr($time, 1);
			$fak = -1;
		}
		
		$s = explode($format[2], $time);
		$r = explode($format[2], $format[0]);
		
		return (mktime(
			$s[array_search("H", $r)] * 1, 
			$s[array_search("i", $r)] * 1, 
			(isset($s[array_search("s", $r)]) ? 
				$s[array_search("s", $r)] * 1 : 
				0), 
			1, 1, 1970)
			+ 3600) * $fak;
	}
	
	/**
	 * Formatiert eine Zahl als (k, M, G, ...)Byte mit Einheit
	 */
	public static function formatByte($byte, $digits = 0){
		$unit = "B";
		if($byte > 1024){
			$byte /= 1024;
			$unit = "kB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "MB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "GB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "TB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "PB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "EB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "ZB";
		}
		if($byte > 1024){
			$byte /= 1024;
			$unit = "YB";
		}
		
		return round($byte,$digits).$unit;
	}
	
	public static function parseDate($language, $date){
		$format = Util::getLangDateFormat($language);
		
		$split = explode($format[1],$date);
		$refer = explode($format[1],$format[0]);
		
		if(count($split) != 3) return -1;
		
		#if($split[0] < 1 OR $split[0] > 31) return -1;
		#if($split[1] < 1 OR $split[1] > 12) return -1;

		$monat = $split[array_search("m",$refer)];
		$tag   = $split[array_search("d",$refer)];
		$jahr  = $split[array_search("Y",$refer)];
		
		if($tag < 1 OR $tag > 31) return -1;
		if($monat < 1 OR $monat > 12) return -1;
		
		return mktime(0, 1, 0, $monat,$tag,$jahr);
		
	}
	
	/**
	 * Parst einen String, der eine Zahl in der Sprache $language enthält, als float oder int
	 * $stringNumber darf nur Ziffern und Separatoren enthalten ansonsten wird null zurückgegeben
	 */
	public static function parseFloat($language, $stringNumber){
		if(is_float($stringNumber) OR is_int($stringNumber)) return $stringNumber;
		
		$format = Util::getLangNumbersFormat($language);

		$stringNumber = str_replace($format[2], "", stripslashes($stringNumber));
		$stringNumber = str_replace($format[0], ".", $stringNumber);
		$number = $stringNumber * 1;

		for($i = 0; $i < strlen($stringNumber); $i++) {
			if(!strstr($stringNumber, ".")) break;
			if($stringNumber{strlen($stringNumber) - 1 - $i} == "0" OR $stringNumber{strlen($stringNumber) - 1 - $i} == ".")
				$stringNumber{strlen($stringNumber) - 1 - $i} = " ";
			else break;
		}
		$stringNumber = trim($stringNumber);
		if(strcmp($number."", $stringNumber) != 0) return null;

		return $number;
	}
	
	/**
	 * Rundet kaufmännisch, auch negative Werte
	 * Also ab 0.5 aufwärts
	 */
	public static function kRound($nummer, $stellen = 2){
		$negative = false;
		if($nummer < 0) $negative = true;
		return round(abs($nummer) + 0.0000000001, $stellen) * ($negative ? -1 : 1);
	}
	
	public static function getLangAnrede($languageTag = null, $lessFormal = false){
		if($languageTag == null)
			$languageTag = Session::getLanguage();
		
		switch($languageTag){
			case "en_GB":
				return array(
					"male" => "Dear Mr",
					"maleShort" => "Mr",
					"female" => "Dear Ms",
					"femaleShort" => "Ms",
					"unknown" => "Dear Sir or Madam",
					"family" => "Dear family",
					"familyShort" => "Family"
				);
			break;
		
			default:
				return array(
					"male" => ($lessFormal ? "Hallo" : "Sehr geehrter")." Herr",
					"maleShort" => "Herr",
					"female" => ($lessFormal ? "Hallo" : "Sehr geehrte")." Frau",
					"femaleShort" => "Frau",
					"unknown" => "Sehr geehrte Damen und Herren",
					"family" => ($lessFormal ? "Hallo" : "Sehr geehrte")." Familie",
					"familyShort" => "Familie"
				);
		}
		
	}
	
	public static function getLangNumbersFormat($languageTag = null){
		if($languageTag == null)
			$languageTag = Session::getLanguage();
		/* array(
		 * Decimal symbol,
		 * # digits after decimal,
		 * Digit grouping symbol);
		 */
		switch($languageTag) {
			case "de_DE":
				return array(",",2,".");
			break;
			case "de_CH":
				return array(".",2,"'");
			break;
			case "en_US":
				return array(".",2,",");
			break;
			case "en_GB":
				return array(".",2,",");
			break;
			default:
				return array(",",2,".");
			break;
		}
	}
	
	public static function getCurrencyFormat($currency, $useSymbol = true){
		switch($currency) {
			case "EUR":
				if($useSymbol)
					return array("€", "n €", "-n €", ",", 2, ".");
				
				return array("EUR", "n EUR", "-n EUR", ",", 2, ".");
		
			case "CHF":
				if($useSymbol)
					return array("SFr.", "SFr. n", "SFr. -n", ".", 2, "'");
				
				return array("CHF", "CHF n", "CHF -n", ".", 2, "'");
				
			case "AED":
				if($useSymbol)
					return array("?", "n ?", "-n ?", ".", 2, ",");
				
				return array("AED", "AED n", "AED -n", ".", 2, ",");
		
			case "USD":
				if($useSymbol)
					return array("$", "\$n", "\$(n)", ".", 2, ",");
				
				return array("USD", "n USD", "-n USD", ".", 2, ",");
		
			case "GBP":
				if($useSymbol)
					return array("£", "£ n", "-£ n", ".", 2, ",");
				
				return array("GBP", "GBP n", "-GBP n", ".", 2, ",");
		
			case "NOK":
				if($useSymbol)
					return array("kr", "kr n", "kr -n", ",", 2, " ");
				
				return array("NOK", "n NOK", "-n NOK", ",", 2, " ");
		
			case "DKK":
				if($useSymbol)
					return array("kr.", "kr. n", "kr. -n", ",", 2, ".");
				
				return array("DKK", "n DKK", "-n DKK", ",", 2, ".");
		
			case "SEK":
				if($useSymbol)
					return array("kr", "n kr", "-n kr", ",", 2, ".");
				
				return array("SEK", "n SEK", "-n SEK", ",", 2, ".");
		
			default:
				if($useSymbol)
					return array("€", "n €", "-n €", ",", 2, ".");
				
				return array("EUR", "n EUR", "-n EUR", ",", 2, ".");
		}
	}
	
	public static function getLangCurrencyFormat($languageTag = null){
		if($languageTag == null)
			$languageTag = Session::getLanguage();
		
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", null, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", null, __METHOD__, $MArgs);
		// </editor-fold>
		
		$languageTag = substr($languageTag, strpos($languageTag, "_") + 1);
		/* array(
		 * Currency symbol, 
		 * positive number Format,
		 * negative number Format,
		 * Decimal symbol, 
		 * # digits after decimal,
		 * Digit grouping symbol);
		 */
		switch($languageTag) {
			case "DE":
				return self::getCurrencyFormat("EUR", true);
			break;
		
			case "DE_EUR":
			case "US_EUR":
				return self::getCurrencyFormat("EUR", false);
			break;
		
			case "CH":
				return self::getCurrencyFormat("CHF", true);
			break;
		
			case "CH_CHF":
				return self::getCurrencyFormat("CHF", false);
			break;
		
			case "US":
				return self::getCurrencyFormat("USD", true);
			break;
		
			case "GB":
				return self::getCurrencyFormat("GBP", true);
			break;
		
			case "GB_GBP":
				return self::getCurrencyFormat("GBP", false);
			break;
		
			case "NO":
				return self::getCurrencyFormat("NOK", true);
			break;
		
			case "NO_NOK":
				return self::getCurrencyFormat("NOK", false);
			break;
		
			case "DK":
				return self::getCurrencyFormat("DKK", true);
			break;
		
			case "DK_DKK":
				return self::getCurrencyFormat("DKK", false);
			break;
		
			case "SE":
				return self::getCurrencyFormat("SEK", true);
			break;
		
			case "SE_SEK":
				return self::getCurrencyFormat("SEK", false);
			break;
		
			default:
				return self::getCurrencyFormat("EUR", true);
			break;
		}
	}
	
	public static function getLangTimeFormat($languageTag){
		/* array(
		 * Time format,
		 * Time format without seconds,
		 * Time separator, 
		 * AM symbol, 
		 * PM symbol);
		 */
		switch($languageTag) {
			case "de_DE":
				return array("H:i:s","H:i",":","","");
			break;
			case "de_CH":
				return array("H:i:s","H:i",":","","");
			break;
			case "en_GB":
				return array("H:i:s","H:i",":","AM","PM");
			break;
			default:
				return array("H:i:s","H:i",":","","");
			break;
		}
	}
	
	public static function getLangDateFormat($languageTag){
		/* array(
		 * Short date format, 
		 * Date separator, 
		 * Long date Format);
		 */
		switch($languageTag) {
			case "de_DE":
			case "de_CH":
			case "ru_RU":
				return array("d.m.Y",".", "l, d. F Y");
			break;
			case "en_GB":
				return array("m/d/Y","/", "l, d. F Y");
			break;
			case "it_IT":
			case "es_ES":
				return array("d/m/Y","/", "l, d. F Y");
			break;
			default:
				return array("d.m.Y",".", "l, d. F Y");
			break;
		}
	}

	public static function getLangWeekdayNames($languageTag){
		switch($languageTag) {
			case "de_DE":
				return Datum::getGerWeekArray();
			break;
			default:
				return Datum::getGerWeekArray();
			break;
		}
	}

	public static function getLangMonthNames($languageTag){
		switch($languageTag) {
			case "de_DE":
				return Datum::getGerMonthArray();
			break;
			default:
				return Datum::getGerMonthArray();
			break;
		}
	}
	
	public static function base64Parser($w, $mode = "store"){
		if($mode == "load") return base64_encode($w);
		
		return base64_decode($w);
	}
	
	public static function nothingParser($w, $mode = "store"){
		return $w;
	}
	
	public static function fillStdClassWithAssocArray($class, $values){
	    $a = PMReflector::getAttributesArray($class);

		for($i = 0;$i < count($a);$i++){
			$f = $a[$i];
			if(isset($values[$a[$i]])) $class->$f = str_replace("\$","\\$", $values[$a[$i]]);
		}
		return $class;
	}
	
	public static function usePDFViewer(){
		return ($_SESSION["S"]->getAgent() == "IE" OR is_writable("../system/IECache/"));
	}
	
	public static function makeOptions($keys, $values){
		$html = "";
		foreach($keys AS $k => $v)
			$html .= "<option value=\"{$v}\">{$values[$k]}</option>";
		
		return $html;
	}

	public static function checkIsEmail($email){
		if(preg_match("/^[a-z0-9]+[a-z0-9_\.-]+@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,12}$/", strtolower(trim($email))))
			return true;
		else
			return false;
	}

	/**
	 * Generates an encryption key to encrypt and decrypt data
	 */
	public static function getEncryptionKey(){
		return sha1(mt_rand(100, 100000000)).sha1(mt_rand(100, 100000000));
	}

	public static function conv_euro($text){
		$text = str_replace("“", "\"", $text);
		$text = str_replace("„", "\"", $text);
		$text = str_replace("–", "-", $text);
		$text = str_replace("€", chr(128), $text);
		$text = str_replace("£", chr(163), $text);
		return $text;
	}

	public static function conv_euro8($text){
		$text = str_replace("“", utf8_encode("\""), $text);
		$text = str_replace("„", utf8_encode("\""), $text);
		$text = str_replace("–", utf8_encode("-"), $text);
		$text = str_replace("€", utf8_encode(chr(128)), $text);
		$text = str_replace("£", utf8_encode(chr(163)), $text);
		return $text;
	}
	
	public static function countUmlaute($text){
		$us = 0;

		$us += substr_count($text, "ä");
		$us += substr_count($text, "ü");
		$us += substr_count($text, "ö");
		$us += substr_count($text, "Ä");
		$us += substr_count($text, "Ü");
		$us += substr_count($text, "Ö");
		$us += substr_count($text, "ß");
		
		return $us;
	}

	public static function formatSeconds($seconds, $showSeconds = true, $language = "de_DE"){
		$format = Util::getLangTimeFormat($language);
		
		$h = ($seconds / 3600);
		$hours = floor($h);
		$minutes = floor(($seconds - $hours * 3600) / 60);
		$sec = $seconds - $hours * 3600 - $minutes * 60;
		if($sec < 10) $sec = "0".$sec;
		
		$minutes  = ($minutes < 10 ? "0" : "").$minutes;
		return $hours.$format[2].$minutes.($showSeconds ? $format[2].$sec : "");
	}
	
	public static function formatSecondsSigned($seconds, $showSeconds = true){
		return ($seconds < 0 ? "-" : "").self::formatSeconds(abs($seconds), $showSeconds);
	}

	/**
	 * returns true if the running php version is greater OR equal $version
	 */
	public static function phpVersionGEThen($version) {
		return version_compare(phpversion(), $version, ">=");
    }
    
	public static function versionCheck($version1, $version2, $op = ">") {
		$version1 = str_replace("a",".1", $version1);
		$version2 = str_replace("a",".1", $version2);
		
		$version1 = str_replace("b",".2", $version1);
		$version2 = str_replace("b",".2", $version2);
		
		$version1 = str_replace("c",".3", $version1);
		$version2 = str_replace("c",".3", $version2);
		
		$version1 = str_replace("d",".4", $version1);
		$version2 = str_replace("d",".4", $version2);
		
		$version1 = str_replace("e",".5", $version1);
		$version2 = str_replace("e",".5", $version2);
		
		$version1 = str_replace("f",".6", $version1);
		$version2 = str_replace("f",".6", $version2);

		$version1 = str_replace(".","", $version1);
		$version2 = str_replace(".","", $version2);
		
		$version1 = str_pad($version1, 5, "0", STR_PAD_RIGHT) * 1;
		$version2 = str_pad($version2, 5, "0", STR_PAD_RIGHT) * 1;

		if($op == ">") return $version1 > $version2;
		if($op == "<") return $version1 < $version2;
		if($op == "==") return $version1 == $version2;
		if($op == "!=") return $version1 != $version2;
		
		#return version_compare($version1, $version2, $op);
    }
    
    public function hidePHPErrors(){
    	if(!isset($_SESSION["HideErrors"])) $_SESSION["HideErrors"] = true;
    	else $_SESSION["HideErrors"] = !$_SESSION["HideErrors"];
    }
    
    public function showPHPErrors(){
    	#echo "<pre style=\"font-size:8px;width:300px;\">";
    	$m = "";
    	if(isset($_SESSION["phynx_errors"]))
	    	foreach($_SESSION["phynx_errors"] AS $value){
	    		$m .= "<b>FehlerTyp:</b> ".$value[0]."\n";
	    		$m .= "<b>FehlerNachricht:</b> ".$value[1]."\n";
	    		$m .= "<b>FehlerDatei:</b> ".$value[2]." ($value[3])\n\n";
	    	}
	    echo Util::getBasicHTMLText($m, "PHP-Fehler");
    	#echo "</pre>";
    }
    
    public function deletePHPErrors(){
    	unset($_SESSION["phynx_errors"]);
    }
    
    public static function replaceNonURLChars($string){
    	$string = str_replace(" ", "_", $string);
    	$string = str_replace("ä", "ae", $string);
    	$string = str_replace("ü", "ue", $string);
    	$string = str_replace("ö", "oe", $string);
    	$string = str_replace("Ä", "Ae", $string);
    	$string = str_replace("Ü", "Ue", $string);
    	$string = str_replace("Ö", "Oe", $string);
    	$string = str_replace("ß", "ss", $string);
    	$string = str_replace("&", "+", $string);
    	$string = str_replace("/", "", $string);
    	
    	return $string;
    }

	public static function makeFilename($string){
		$filename = Util::replaceNonURLChars($string);
		$filename = str_replace(array("Á","À","Â","Ã","á","à","â","ã"), array("A","A","A","A","a","a","a","a"), $filename);
		$filename = str_replace(array("Ç","ç","É","È","Ê","é","è","ê", "ë", "Č"), array("C","c","E","E","E","e","e","e", "e", "C"), $filename);
		$filename = str_replace(array("Í","Ì","í","ì", "ï","Õ","Ô","Ó"), array("I","I","i","i", "i","O","O","O"), $filename);
		$filename = str_replace(array("õ","ô","ó","Ú","ú"), array("o","o","o","U","u"), $filename);
    	$filename = str_replace(array(":", "–", "\n", "'", "?", "(", ")", ";", "\"", "+", "<", ">", ",", "´", "`", "|"), array("_", "-", "", "", "", "", "", "", "", "", "", "", "", "", "", "_"), $filename);
		$filename = str_replace(array("__"), array("_"), $filename);

		return $filename;
	}
    
	public static function PDFViewer($filename, $delete = true){
		if(!strstr($filename, "IECache")) {
			$_SESSION["BPS"]->registerClass("showPDF");
			$_SESSION["BPS"]->setACProperty("filename","$filename");
			$_SESSION["BPS"]->setACProperty("delete", $delete ? "1" : "0");
			$CH = self::getCloudHost();
			if($CH AND isset($CH->usePreviewRewrite) AND $CH->usePreviewRewrite)
				echo "<!DOCTYPE html><html><script type=\"text/javascript\">document.location='../preview/".basename($filename)."';</script></html>";
			else
				echo "<!DOCTYPE html><html><script type=\"text/javascript\">document.location='./showPDF.php';</script></html>";
		} else
			echo "<!DOCTYPE html><html><script type=\"text/javascript\">document.location='../system/IECache/".$_SESSION["S"]->getCurrentUser()->getID()."/".basename($filename)."?rand=".rand(100, 1000000)."';</script></html>";
	}

	public static function showPDF($object, $callbackFunction = "getPDF"){
		if(Util::usePDFViewer()){
			$filename = $object->$callbackFunction(true);
			Util::PDFViewer($filename);
		} else 
			$object->$callbackFunction(false);	
	}
	
	public static function genPassword($length=8) {
		$pass =  chr(mt_rand(65,90));
	   
	    for($k=0; $k < $length - 1; $k++) {
	        $probab = mt_rand(1,10);
	   
	        if($probab <= 8)
	            $pass .= chr(mt_rand(97,122));
	        else
	            $pass .= chr(mt_rand(48, 57));
	    }
	    
	    return $pass;
	}

	public static function encrypt($input){
		if(!isset($_SESSION["MCryptKey"])) return null;
		if($input == "") return "";
		
		$td = mcrypt_module_open(MCRYPT_TWOFISH, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $_SESSION["MCryptKey"], $iv);
		
		$encrypted = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return $encrypted;
	}
	
	public static function decrypt($input){
		if(!isset($_SESSION["MCryptKey"])) return null;
		if($input == "") return "";
		
		$td = mcrypt_module_open(MCRYPT_TWOFISH, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $_SESSION["MCryptKey"], $iv);

		$decrypted = mdecrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return trim($decrypted);
		
	}
	
	public static function dencryptParser($w, $l){
		if($l == "load")
			return Util::decrypt(base64_decode($w, true));
		
		return base64_encode(Util::encrypt($w));
	}
	
	public static function PDFCurrencyParser($w, $l = "load"){
		return Util::conv_euro(Util::CLFormatCurrency($w * 1, true));
	}
	
	public static function getTempDir(){
		$dirtouse = Util::getRootPath()."system/IECache/";
		
		if(!is_writable($dirtouse)) {
			$file = tempnam(":\n\\/?><","");
			$dirtouse = dirname($file);
			unlink($file);
		}
		else Util::clearIECache($dirtouse);
		
		$subdir = (isset($_SESSION["S"]) AND $_SESSION["S"]->getCurrentUser() != null) ? $_SESSION["S"]->getCurrentUser()->getID() : "info";
		
		$CH = Util::getCloudHost();
		if($CH !== null){
			$dirtouse = "/tmp";
			Environment::load();
			$subdir = Environment::$currentEnvironment->cloudUser()."/$subdir";
		}
		
		$dirtouse .= ($dirtouse[strlen($dirtouse) -1] != "/" ? "/" : "").$subdir."/";
			
		if(!is_dir($dirtouse)) {
			#echo "create $dirtouse...\n";
			if(!mkdir($dirtouse, 0777, true))
				throw new Exception("Could not create temp dir ".$dirtouse);
					
			chmod($dirtouse."../", 0777);
			chmod($dirtouse, 0777);
		} else {
			chmod($dirtouse."../", 0777);
			chmod($dirtouse, 0777);
		}
		
		if(!is_dir($dirtouse)) 
			throw new Exception("Did not create temp dir $dirtouse");
		
		#echo gethostname().":";
		#var_dump(is_dir($dirtouse));
		#print_r($dirtouse);
		#die();
		if(PHYNX_USE_TEMP_HTACCESS AND strpos($dirtouse, Util::getRootPath()) !== false /*AND !file_exists($dirtouse.".htaccess")*/ AND is_writable($dirtouse)){
			$content = "<IfModule mod_authz_core.c>
    Require ip ".$_SERVER["REMOTE_ADDR"]."
</IfModule>";
			
			if(strstr($_SERVER["REMOTE_ADDR"], ".")) //USE ONLY WHEN ON IPV4 due to APACHE BUG https://issues.apache.org/bugzilla/show_bug.cgi?id=49737
				$content .= "
<IfModule !mod_authz_core.c>
	allow from ".$_SERVER["REMOTE_ADDR"]."
	deny from all
	allow from ".$_SERVER["REMOTE_ADDR"]."
</IfModule>";
			
			file_put_contents($dirtouse.".htaccess", $content);
					
		}
		elseif(file_exists($dirtouse.".htaccess"))
			unlink($dirtouse.".htaccess");
		
		if(!PHYNX_USE_TEMP_HTACCESS AND file_exists($dirtouse.".htaccess"))
			unlink($dirtouse.".htaccess");
		
		return $dirtouse;
	}
	
	public static function getUploadedFilename($filename){
		return self::getTempDir().$filename.".tmp";
	}
	
	public static function getTempFilename($filename = null, $suffix = "pdf"){
		$dirtouse = self::getTempDir();
		
		if($filename == null) $filename = "TempFile";
		$filename = $dirtouse.$filename.".$suffix";
		
		#if($filename != null) {
		#	$tmpfname = $dirtouse.$filename.".pdf";
		$handle = fopen($filename, "w+");
		fclose($handle);
		#} else {
		#	$file = tempnam($dirtouse, "GRLBM_");
		#	$tmpfname .= ".pdf";
		#	unlink($file);
		#}
		
		chmod($filename, 0777);
		return $filename;
	}
	
	public static function clearIECache($dir, $filename = ""){
		if(!strstr($dir,"IECache")) return;
		if(!is_dir($dir)) return;

		$subdir = (isset($_SESSION["S"]) AND $_SESSION["S"]->getCurrentUser() != null) ? $_SESSION["S"]->getCurrentUser()->getID() : "info";
		
		$dir .= ($dir[strlen($dir) -1] != "/" ? "/" : "").$subdir."/";

		if(!file_exists($dir)) return;
		
		$fp = opendir($dir);
		while(($file = readdir($fp)) !== false) {
			if(is_dir("$dir/$file")) continue;
			
			if(time() - filemtime($dir."/".$file) > 180) unlink ("$dir/$file");
		}
		
		if($filename != "")
			if(file_exists("$dir/$filename")) unlink("$dir/$filename");
		
	}
	
	public static function catchParser($w, $l = "load", $p = ""){
		
		if(!is_array($p) AND !is_object($p))
			$p = HTMLGUI::getArrayFromParametersString($p);
		
		if(is_object($p))
			$p = array();
		
		if(is_array($p))
			unset($p[0]);
		return $w == 1 ? "<img ".(isset($p[0]) ? "title=\"$p[0]\"" : "")." src=\"./images/i2/ok.gif\" />" : "<img ".(isset($p[1]) ? "title=\"$p[1]\"" : "")." src=\"./images/i2/notok.gif\" />";
	}
	
	public static function httpTestAndLoad($url, $timeout = 10) {
		$timeout = (int)round($timeout/2+0.00000000001);
		$return = array();

		$query = Aspect::joinPoint("query", null, __METHOD__);
		
		### 1 ###
		$inf = parse_url($url.($query != null ? "?q=$query" : "?q=".Util::invokeStaticMethod("Util", "eK", array(false))));
		
		if (!isset($inf['scheme']) or $inf['scheme'] !== 'http')
			return array('status' => -1);
			
		if (!isset($inf['host']))
			return array('status' => -2);
			
		$host = $inf['host'];
		
		if (!isset($inf['path']))
			return array('status' => -3);
			
		$path = $inf['path'];
		
		if (isset($inf['query'])) $path .= '?'.$inf['query'];
		
		if (isset($inf['port']))
			$port = $inf['port'];
		else $port = 80;
		
		### 2 ###
		$pointer = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$pointer)
			return array('status' => -4, 'errstr' => $errstr, 'errno' => $errno);
		
		socket_set_timeout($pointer, $timeout);
		
		### 3 ###
		$head =
			'GET '.$path.' HTTP/1.1'."\r\n".
			'Host: '.$host."\r\n";
		
		if (isset($inf['user']))
			$head .= 'Authorization: Basic '.base64_encode($inf['user'].':'.(isset($inf['pass']) ? $inf['pass'] : ''))."\r\n";
		
		if (func_num_args() > 2) {
			for ($i = 2; $i < func_num_args(); $i++) {
				$arg = func_get_arg($i);
				
				if (strpos($arg, ':') !== false and strpos($arg, "\r") === false and strpos($arg, "\n") === false)
					$head .= $arg."\r\n";
			}
		}
		#else
		$head .= 'User-Agent: phynx Version checker'."\r\n";

		$head .= "X-Application: ".$_SESSION["applications"]->getActiveApplication()."\r\n";
		$head .= "X-Version: ".$_SESSION["applications"]->getRunningVersion()."\r\n";
		$head .= "X-PHPVersion: ".phpversion()."\r\n";

		$head .= 'Connection: close'."\r\n"."\r\n";
		
		### 4 ###
		fputs($pointer, $head);
		
		$response = '';
		
		$status = socket_get_status($pointer);
		while (!$status['timed_out'] && !$status['eof']) {
			$response .= fgets($pointer);
			$status = socket_get_status($pointer);
		}
		fclose($pointer);
		
		if ($status['timed_out'])
			return array('status' => -5, '_request' => $head);
		
		### 5 ###
		$res = str_replace("\r\n", "\n", $response);
		$res = str_replace("\r", "\n", $res);
		$res = str_replace("\t", ' ', $res);
		
		$ares = explode("\n", $res);
		$first_line = explode(' ', array_shift($ares), 3);
		
		$return['status'] = trim($first_line[1]);
		$return['reason'] = trim($first_line[2]);
		
		foreach ($ares as $line) {
			$temp = explode(':', $line, 2);
			
			if (isset($temp[0]) and isset($temp[1]))
				$return[strtolower(trim($temp[0]))] = trim($temp[1]);
		}
		
		$return['_response'] = $response;
		$return['_request'] = $head;
		
		return $return;
	
	}
	
	/*
	 * Die folgende Funktion ist von
	 * Christian Seiler
	 * http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
	 * self@christian-seiler.de
	 */
	public static function lang_getfrombrowser($allowed_languages, $default_language, $lang_variable = null, $strict_mode = true) {
        // $_SERVER['HTTP_ACCEPT_LANGUAGE'] verwenden, wenn keine Sprachvariable mitgegeben wurde
        if ($lang_variable === null AND isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $lang_variable = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        // wurde irgendwelche Information mitgeschickt?
        if (empty($lang_variable)) {
                // Nein? => Standardsprache zurückgeben
                return $default_language;
        }

        // Den Header auftrennen
        $accepted_languages = preg_split('/,\s*/', $lang_variable);

        // Die Standardwerte einstellen
        $current_lang = $default_language;
        $current_q = 0;

        // Nun alle mitgegebenen Sprachen abarbeiten
        foreach ($accepted_languages as $accepted_language) {
                // Alle Infos über diese Sprache rausholen
                $res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
                                   '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);

                // war die Syntax gültig?
                if (!$res) {
                        // Nein? Dann ignorieren
                        continue;
                }
                
                // Sprachcode holen und dann sofort in die Einzelteile trennen
                $lang_code = explode ('-', $matches[1]);

                // Wurde eine Qualität mitgegeben?
                if (isset($matches[2])) {
                        // die Qualität benutzen
                        $lang_quality = (float)$matches[2];
                } else {
                        // Kompabilitätsmodus: Qualität 1 annehmen
                        $lang_quality = 1.0;
                }
				
                // Bis der Sprachcode leer ist...
                while (count ($lang_code)) {
                        // mal sehen, ob der Sprachcode angeboten wird
                        if (in_array (strtolower (join ('-', $lang_code)), $allowed_languages)) {
                                // Qualität anschauen
                                if ($lang_quality > $current_q) {
                                        // diese Sprache verwenden
                                        $current_lang = strtolower (join ('-', $lang_code));
                                        $current_q = $lang_quality;
                                        // Hier die innere while-Schleife verlassen
                                        break;
                                }
                        }
                        // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
                        if ($strict_mode) {
                                // innere While-Schleife aufbrechen
                                break;
                        }
                        // den rechtesten Teil des Sprachcodes abschneiden
                        array_pop ($lang_code);
                }
        }

        // die gefundene Sprache zurückgeben
        if($current_lang == "de") $current_lang .= "_DE";
        if($current_lang == "en") $current_lang .= "_GB";
        if($current_lang == "it") $current_lang .= "_IT";
        
        return $current_lang;
	}
	
	private $statusMessagesLog = array();
	public function logStatusMessages($columns, $class, $method, $parameters = null){
		if(!is_array($columns)) $columns = array($columns);
		
		ob_start();
		$message = "";
		#$class->$method();
		try {
			$R = new ReflectionMethod($class, $method);
			$R->invokeArgs($class, $parameters);
		} catch(Exception $e){
			$message = $e->getMessage();
		}

		$ob = ob_get_contents();
		
		$out = array();
		$out[0] = "";

		if($ob != "")
			$out[0] .= "<script type=\"text/javascript\">Interface.translateStatusMessage(\"$ob\",\"replacementMessage".count($this->statusMessagesLog)."\");</script><span id=\"replacementMessage".count($this->statusMessagesLog)."\"></span>";
		elseif($message != ""){
			$out[0] .= "<span style=\"color:red;\">$message</span>";
		} else $out[0] .= "<span style=\"color:green;\">OK</span>";

		$this->statusMessagesLog[] = array_merge($columns, $out);
	}
	
	public function getStatusMessagesLog($tableColumns = 1, $tableName = ""){
		if($tableName != "") $tab = new HTMLTable($tableColumns, $tableName);
		else $tab = new HTMLTable($tableColumns);
		$tab->maxHeight(400);
		
		foreach($this->statusMessagesLog AS $k => $v)
			$tab->addRow($v);
		
		return $tab;
	}
	
	public static function getBasicHTMLText($message, $title){
		$lines = explode("\n",trim($message));
		foreach($lines as $k => $v)
			$lines[$k] = str_pad(($k + 1),5, " ", STR_PAD_LEFT).": ";
			
		$html = "<pre class=\"backgroundColor2\" style=\"font-size:9px;float:left;\">".implode("\n",$lines)."</pre><pre class=\"backgroundColor0\" style=\"font-size:9px;margin-left:40px;\">".$message."</pre>";
		
		return Util::getBasicHTML($html, $title);
	}
/*
	public static function getEmoHTMLError($excuse, $message, $title){
		$message = "<style type=\"text/css\">
			p {
				padding:5px;
			}

			div {
				padding:10px;
			}
		</style><div class=\"backgroundColor0\"><img src=\"./images/big/notice.png\" style=\"float:left;margin-right:15px;\" /><h1>$excuse</h1><p>".$message."</p></div>";

		return Util::getBasicHTML($message, $title);
	}*/
	
	public static function getBasicHTMLError($message, $title){
		$message = "<style type=\"text/css\">
			p {
				padding:5px;
			}
		</style><p class=\"backgroundColor0\">".$message."</p>";
		
		return Util::getBasicHTML($message, $title);
	}
	
	public static function getBasicHTMLPrint($message, $title){
		$message = "<style type=\"text/css\">
			body {
				background-color:white;
			}
			table {
				border-collapse:collapse;
				width:100%;
			}
			
			table td {
				border-bottom:1px solid grey;
			}
			
			table th {
				border-bottom:1px solid black;
			}
			
			tr, td, th {
				page-break-inside:avoid;
			}

			thead {
				display:table-header-group;
			}

		</style>".$message;
		
		return Util::getBasicHTML($message, $title);
	}
	
	public static function getBasicHTMLMail($content, $title){
		#header("Content-Type: text/html; charset=utf-8");
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>'.$title.'</title>
	</head>
	<body style="background-color:#efefef;color:#222;">
		'.($title != "" ? '<h1 style="font-family:sans-serif;margin-left:30px;">'.$title.'</h1>' : "").'
		<div style="font-family:sans-serif;background-color:white;font-size:14px;margin:20px;padding:10px;">
		'.$content.'
		</div>
	</body>
</html>';
	}
	
	public static function getBasicHTML($content, $title, $js = true){
		$physion = "default";
		if(isset($_GET["physion"]))
			$physion = $_GET["physion"];

		#header("Content-Type: text/html; charset=utf-8");
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link rel="shortcut icon"type="image/x-icon" href="data:image/x-icon;,">
		<title>'.$title.'</title>
		'.($js ? '
		<script type="text/javascript" src="../libraries/jquery/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="../libraries/jquery/jquery-ui-1.10.1.custom.min.js"></script>
		<script type="text/javascript" src="../libraries/iconic/iconic.min.js"></script>
		<script type="text/javascript" src="../libraries/jquery/jquery.qtip.min.js"></script>
		<script type="text/javascript" src="../javascript/P2J.js"></script>
		<script type="text/javascript" src="../javascript/Aspect.js"></script>
		<script type="text/javascript" src="../javascript/handler.js"></script>
		<script type="text/javascript" src="../javascript/contentManager.js"></script>
		<script type="text/javascript" src="../javascript/Interface.js"></script>
		<script type="text/javascript" src="../javascript/Overlay.js"></script>
		<script type="text/javascript" src="../libraries/webtoolkit.base64.js"></script>
		<script type="text/javascript">
			$j(document).ready(function() {
				Ajax.physion = "'.$physion.'";
			});
		</script>' : "").'
		
		<link rel="stylesheet" type="text/css" href="../libraries/jquery/jquery.qtip.min.css" />
		<link rel="stylesheet" type="text/css" href="../styles/'.(isset($_COOKIE["phynx_color"])? $_COOKIE["phynx_color"] : "standard").'/colors.css"></link>
		<link rel="stylesheet" type="text/css" href="../styles/standard/general.css"></link>
		<style type="text/css">
			p {
				padding:5px;
			}
		</style>
	</head>
	<body>
		'.$content.'
	</body>
</html>';
	}

	public static function eK(){
		return mUserdata::getGlobalSettingValue(implode("", array_map("chr", array(0 => 101, 1 => 110, 2 => 99, 3 => 114, 4 => 121, 5 => 112, 6 => 116, 7 => 105, 8 => 111, 9 => 110, 10 => 75, 11 => 101, 12 => 121))));
	}
	
	public static function newObject($className){
		$c = new $className(-1);
		$a = $c->newAttributes();
		
		$arg_list = func_get_args();
		$i = 1;
		foreach($a as $k => $v){
			if($k == $className."ID") continue;
			
			if(isset($arg_list[$i]))
				$a->$k = $arg_list[$i];
			$i++;
		}
		$c->setA($a);
		$c->newMe();
	}
	
	public static function calcNettoPreis($preis, $mwst){
		$p = Util::parseFloat($_SESSION["S"]->getUserLanguage(), $preis);
		$m = $mwst * 1;#Util::parseFloat($_SESSION["S"]->getUserLanguage(), $mwst);

		$netto = $p / (100 + $m) * 100;

		echo Util::formatCurrency($_SESSION["S"]->getUserLanguage(), $netto, false);
	}


	public static function invokeStaticMethod($class, $method, $parameters){
		$R = new ReflectionMethod($class, $method);
		if(!is_array($parameters)) $parameters = array($parameters);
		return $R->invokeArgs(null, $parameters);
	}
}

?>