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

class T {
	public static $generate = false;
	public static $lastPath;
	private static $poFileContent = array();
	private static $localeSet = false;
	private static $currentDomain = "";
	public static $domainPaths = array();
	
	public static function D($domain){
		self::$currentDomain = $domain;
	}
	
	public static function _($text){
		if(!function_exists("bindtextdomain"))
			return $text;
		
		if(trim($text) == "" OR trim($text) == "&nbsp;")
			return $text;
		#echo self::$currentDomain.":".$text."<br />";
		if(self::$generate AND self::$domainPaths[self::$currentDomain] != null AND Session::getLanguage() != "de_DE"){
			if(!isset(self::$poFileContent[self::$currentDomain]))
				self::$poFileContent[self::$currentDomain] = file_get_contents(self::$domainPaths[self::$currentDomain]."/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po");
			#var_dump(self::$poFileContent);
			$putText = str_replace("\"", "\\\"", $text);
			if(strpos($putText, "\n") !== false){
				$putText = str_replace("\n", "\\n\"\n\"", $putText);
				$putText = "\"\n\"$putText";
			}
				
			if(strpos(self::$poFileContent[self::$currentDomain], "msgid \"$putText\"") === false){
				file_put_contents(self::$domainPaths[self::$currentDomain]."/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po", "msgid \"$putText\"\nmsgstr \"\"\n\n", FILE_APPEND);
				self::$poFileContent[self::$currentDomain] = file_get_contents(self::$domainPaths[self::$currentDomain]."/".Session::getLanguage()."/LC_MESSAGES/messages".self::$currentDomain.".po");
			}
		}
		
		$text = dgettext("messages".self::$currentDomain, $text);
		
		$args = func_get_args();
		if(count($args) > 1){
			for($i = count($args); $i > 1; $i--)
				$text = str_replace ("%".($i - 1), $args[$i - 1], $text);
			
		}
		
		$text = Aspect::joinPoint("translate", null, __METHOD__, $text, $text);
		
		return $text;
	}
	
	public static function load($pluginPath, $domain = ""){
		if(!function_exists("bindtextdomain"))
			return;
		
		self::$domainPaths[$domain] = $pluginPath."/locale";

		if(!self::$localeSet){
			setlocale(LC_MESSAGES, Session::getLanguage().".UTF-8");
			self::$localeSet = true;
		}
		self::D($domain);
		
		
		bindtextdomain("messages$domain", self::$domainPaths[$domain]);
		bind_textdomain_codeset("messages$domain", "UTF-8");
	}
}
?>