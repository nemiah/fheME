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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class Red {
	
	public static function alertD($message, $die = true){
		if($die)
			die("alert:'".addslashes(T::_($message))."'");
		else
			throw new Exception($message);
	}

	public static function redirect($JSFunction){
		die("redirect:$JSFunction");
	}
	
	public static function errorD($message, $exception = false){
		if(!$exception)
			die("error:'".addslashes(T::_($message))."'");
		else 
			throw new Exception ($message);
	}

	public static function errorC($class, $message){
		$ac = new anyC();
		$Lang = $ac->loadLanguageClass($class)->getText();

		die("error:'".addslashes($Lang[$message])."'");
	}

	public static function alertC($class, $message){
		$ac = new anyC();
		$Lang = $ac->loadLanguageClass($class)->getText();

		die("alert:'".addslashes($Lang[$message])."'");

	}

	public static function messageC($class, $message){
		$ac = new anyC();
		$Lang = $ac->loadLanguageClass($class)->getText();

		die("message:'".addslashes($Lang[$message])."'");
	}

	public static function messageD($message, array $data = null){
		if($data != null){
			$value = array("type" => "message", "message" => T::_($message));
			foreach($data AS $k => $v)
				$value[$k] = $v;
			
			if(defined("JSON_UNESCAPED_UNICODE"))
				die(json_encode($value, JSON_UNESCAPED_UNICODE));

			die(json_encode($value));
		}
		
		die("message:'".addslashes(T::_($message))."'");
	}
	
	public static function messageSaved(array $data = null){
		if($data != null){
			$value = array("type" => "message", "message" => T::_("Daten gespeichert"));
			foreach($data AS $k => $v)
				$value[$k] = $v;
			
			if(defined("JSON_UNESCAPED_UNICODE"))
				die(json_encode($value, JSON_UNESCAPED_UNICODE));

			die(json_encode($value));
		}
		
		die("message:'".T::_("Daten gespeichert")."'");
	}
	
	public static function messageCreated(array $data = null){
		if($data != null){
			$value = array("type" => "message", "message" => T::_("Datensatz erstellt"));
			foreach($data AS $k => $v)
				$value[$k] = $v;
			
			if(defined("JSON_UNESCAPED_UNICODE"))
				die(json_encode($value, JSON_UNESCAPED_UNICODE));

			die(json_encode($value));
		}
		
		die("message:'".T::_("Datensatz erstellt")."'");
	}
	
	public static function errorUpdate($e = null){
		self::errorD("Bitte aktualisieren Sie über das Installations-Plugin im Administrationsbereich die Tabelle des Plugins".($e != null ? " (die Spalte ".$e->getField()." fehlt in der Datenbank)" : "").".");
	}
	
	public static function errorClass($n){
		self::errorD("Die Klasse $n konnte nicht gefunden werden. Die Anfrage wird abgebrochen.");
	}
	
	public static function errorDuplicate($value){
		self::errorD("Der Wert $value wurde bereits vergeben");
	}
	
	public static function errorExtension($ext){
		self::errorD("Die PHP-Erweiterung '$ext' wurde nicht geladen. Anfrage wird abgebrochen.");
	}
}
?>
