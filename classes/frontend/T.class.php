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
	private static $poFileContent;
	
	public static function _($text){
		if(self::$generate AND self::$lastPath != null){
			if(self::$poFileContent === null)
				self::$poFileContent = file_get_contents(self::$lastPath."/".Session::getLanguage()."/LC_MESSAGES/messages.po");
			
			$putText = $text;
			if(strpos($putText, "\n") !== false){
				$putText = str_replace("\n", "\\n\"\n\"", $putText);
				$putText = "\"\n\"$putText";
			}
				
			if(strpos(self::$poFileContent, "msgid \"$putText\"") === false){
				file_put_contents(self::$lastPath."/".Session::getLanguage()."/LC_MESSAGES/messages.po", "msgid \"$putText\"\nmsgstr \"\"\n\n", FILE_APPEND);
				self::$poFileContent = file_get_contents(self::$lastPath."/".Session::getLanguage()."/LC_MESSAGES/messages.po");
			}
		}
		
		$text = _($text);
		
		$args = func_get_args();
		if(count($args) > 1){
			for($i = count($args); $i > 1; $i--)
				$text = str_replace ("%".($i - 1), $args[$i - 1], $text);
			
		}
		
		return $text;
	}
	
	public static function load($pluginPath){
		self::$lastPath = $pluginPath."/locale";
		
		setlocale(LC_MESSAGES, Session::getLanguage().".UTF-8");
		bindtextdomain("messages", self::$lastPath);
		bind_textdomain_codeset("messages", "UTF-8");
	}
}
?>