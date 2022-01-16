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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */

class T {
	public static $generate = false;
	public static $log = false;
	public static $lastPath;
	#private static $poFileContent = array();
	private static $localeSet = false;
	private static $currentDomain = "";
	public static $domainPaths = array();
	private static $domains = array();
	
	public static function D($domain){
		self::$currentDomain = $domain;
		self::$domains[] = $domain;
	}
	
	public static function _($text){
		if(!function_exists("bindtextdomain") OR strpos($_SERVER["DOCUMENT_ROOT"], "/Applications/MAMP") !== false){
			$args = func_get_args();
			if(count($args) > 1){
				for($i = count($args); $i > 1; $i--)
					$text = str_replace("%".($i - 1), $args[$i - 1], $text);

			}
			
			return $text;
		}
		
		if(!function_exists("bind_textdomain_codeset"))
			return $text;
		
		if(trim($text) == "" OR trim($text) == "&nbsp;")
			return $text;
		
		if(self::$currentDomain != "" AND self::$generate AND self::$domainPaths[self::$currentDomain] != null AND Session::getLanguage() != "de_DE"){
			$poFileContent = file_get_contents(self::$domainPaths[self::$currentDomain]."/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po");
			$poFileContentGlobal = file_get_contents(Util::getRootPath()."/libraries/locale/".Session::getLanguage()."/LC_MESSAGES/messages.po");
			
			$putText = str_replace("\"", "\\\"", $text);
			if(strpos($putText, "\n") !== false){
				$putText = str_replace("\n", "\\n\"\n\"", $putText);
				$putText = "\"\n\"$putText";
			}
			
			if(strpos($poFileContentGlobal, "msgid \"$putText\"") === false AND strpos($poFileContent, "msgid \"$putText\"") === false)
				file_put_contents(self::$domainPaths[self::$currentDomain]."/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po", "msgid \"$putText\"\nmsgstr \"\"\n\n", FILE_APPEND);				
			
		}
		
		if(self::$log AND Session::getLanguage() != "de_DE"){
			
			#if(self::$domainPaths[self::$currentDomain] != null){
			#}
			$poFileContent = file_get_contents(self::$domainPaths[self::$currentDomain]."/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po");
			$poFileContentGlobal = file_get_contents(Util::getRootPath()."/libraries/locale/".Session::getLanguage()."/LC_MESSAGES/messages.po");
			
			$putText = str_replace("\"", "\\\"", $text);
			if(strpos($putText, "\n") !== false){
				$putText = str_replace("\n", "\\n\"\n\"", $putText);
				$putText = "\"\n\"$putText";
			}
			
			
			$logFile = "/var/www/nubes/locale/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po";
			$poFileContentLog = file_get_contents($logFile);
			if(strpos($poFileContentGlobal, "msgid \"$putText\"") === false AND strpos($poFileContent, "msgid \"$putText\"") === false){
				if(strpos($poFileContentLog, "msgid \"$putText\"") === false)
					file_put_contents($logFile, "msgid \"$putText\"\nmsgstr \"\"\n\n", FILE_APPEND);
			
				#$json = file_get_contents("https://translation.googleapis.com/language/translate/v2?q=".urlencode($text)."&target=en&source=de&key={NOKEY}");
				#if($json){
				#	$t = json_decode($json);

				#	return $t->data->translations[0]->translatedText;
				#}
			}
		}

		$text2 = dgettext("messages".self::$currentDomain, $text);
		
		if($text2 == $text AND count(self::$domains) > 1){
			$search = array_reverse(self::$domains);
			$found = false;
			foreach($search AS $domain){
				if(self::$currentDomain == $domain)
					continue;
				
				$text3 = dgettext("messages".$domain, $text);
				if($text3 != $text){
					$text = $text3;
					$found = true;
					break;
				}
			}
			
			if(!$found)
				$text = $text2;
		} else
			$text = $text2;
		
		
		$args = func_get_args();
		if(count($args) > 1){
			for($i = count($args); $i > 1; $i--)
				$text = str_replace("%".($i - 1), $args[$i - 1], $text);
			
		}
		
		$text = Aspect::joinPoint("translate", null, __METHOD__, $text, $text);
		
		return $text;
	}
	
	public static function load($pluginPath, $domain = ""){
		if(!function_exists("bindtextdomain") OR strpos($_SERVER["SERVER_SOFTWARE"], "BitWebServer") !== false)
			return;
		
		if(!function_exists("bind_textdomain_codeset"))
			return;
		
		if(isset(self::$domainPaths[$domain]))
			return;
		
		self::$domainPaths[$domain] = $pluginPath."/locale";
		
		if(!self::$localeSet){
			setlocale(LC_MESSAGES, Session::getLanguage().".UTF-8");
			self::$localeSet = true;
			T::load(Util::getRootPath()."libraries");
		}
		self::D($domain);
		
		
		bindtextdomain("messages$domain", self::$domainPaths[$domain]);
		bind_textdomain_codeset("messages$domain", "UTF-8");
	}
}
?>