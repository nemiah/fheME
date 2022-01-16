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
class Phynx {
	public static function build(){
		if(!file_exists(Util::getRootPath()."system/build.xml"))
			return false;
		
		try {
			$xml = new SimpleXMLElement(file_get_contents(Util::getRootPath()."system/build.xml"));
		
			return $xml->build->prefix."-".$xml->build->number;
		} catch(Exception $e){
			return false;
		}
	}
	
	public static function users(){
		if(!file_exists(Util::getRootPath()."system/build.xml"))
			return 1;
		
		try {
			$xml = new SimpleXMLElement(file_get_contents(Util::getRootPath()."system/build.xml"));
			if(!isset($xml->build->users))
				return 1;
			
			return $xml->build->users;
		} catch(Exception $e){
			return false;
		}
	}
	
	public static function abo(){
		if(!file_exists(Util::getRootPath()."system/build.xml"))
			return "0";
		
		try {
			$xml = new SimpleXMLElement(file_get_contents(Util::getRootPath()."system/build.xml"));
		
			return $xml->build->abo;
		} catch(Exception $e){
			return false;
		}
	}
	
	public static function customer(){
		if(!file_exists(Util::getRootPath()."system/build.xml"))
			return "0";
		
		try {
			$xml = new SimpleXMLElement(file_get_contents(Util::getRootPath()."system/build.xml"));
		
			return $xml->build->customer;
		} catch(Exception $e){
			return false;
		}
	}
	
	
}
?>