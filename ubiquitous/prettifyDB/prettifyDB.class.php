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
 *  2007 - 2018, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class prettifyDB extends PersistentObject {
	private static $prettifyerRules = array(
		"seriesEpisodeNameDownloaded" => array(
			"^OneDDL.com-|^1-3-3-8.com[_-]|Ddlsource.com_|DDLValley.Me_" => "",
			"-ctu|-immerse|[-.]2hd|-bia|-wasabi|-Hannibal|-FoV|.immerse|-EVOLVE|c4tv|-HoC|Repack|-compulsion|-ASAP|-SiNNERS|-ECI|BluRay|-AVS|-KILLERS|-LOL|-SPARKS" => "",
			"[-.]DIMENSION|-MADHACKER|-FEVER|[-.]PiLAF|.PROPER|WEBRip|AAC|-tbs|-convoy|-mtb" => "",
			"s([0-9]+)e([0-9]+)" => "S\\1E\\2",
			"^([a-z])" => "strtoupper",
			".hdtv|-orenji|[-.]x264|[-.]h264|[_.]WEB[-.]DL|[._]h.264|-kyr" => "",
			"([0-9]{1,2})×([0-9]{2})" => "S\\1E\\2",
			"(.[a-z])" => "strtoupper",
			".web" => "",
			"Mkv" => "",
			"Mp4" => "",
			".720p" => "",
			".1080p" => "",
			".(20[0-9]{2})." => " (\\1) ",
			"." => " "
		)
	);
	
	public static function rules($target){
		if(isset(self::$prettifyerRules[$target]))
			return self::$prettifyerRules[$target];
		
		return array();
	}
	
	public static function apply($target, $text){
		foreach(self::rules($target) AS $reg => $replace){
			if($replace != "strtoupper")
				$text = preg_replace("/".str_replace(".", "\.", $reg)."/i", str_replace(array("."), array("\."), $replace), $text);
			else
				$text = preg_replace_callback("/".str_replace(".", "\.", $reg)."/i", function ($treffer) { return strtoupper($treffer[0]); }, $text);
			
		}
		
		return $text;
	}
}
?>