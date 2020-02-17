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
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */
class RSSPWettwarnDe implements iFileBrowser, iRSSParser {
	private $title = null;
	private $ok = false;
	
	public function getLabel() {
		return "wettwarn.de RSS-Feed";
	}
	
	public function parseTitle($title){
		$this->title = $title;
		if($this->title == "Keine Warnungen vorhanden."){
			$this->ok = true;
			
			return "<span style=\"color:grey;\">Keine Warnungen vorhanden.</span>";
		}
		
		return $title;
	}
	
	public function parseDescription($description){
		$description = str_replace("in Donau-Ries<br />", "", $description);
		$description = str_replace("Quelle: Deutscher Wetterdienst<br />
Details: http://wettwarn.de/DON", "", $description);
		
		$ex = explode("<br />", $description);
		$style = "";
		if(strpos($ex[2], "Stufe 1") !== false)
				$style = "color:grey;";
		
		if(strpos($ex[2], "Stufe 2") !== false)
				$style = "color:green;";
		
		if(strpos($ex[2], "Stufe 3") !== false)
				$style = "color:orange;";
		
		if(strpos($ex[2], "Stufe 4") !== false)
				$style = "color:red;";
				
		$ex[0] = "<strong>". str_replace("DWD ", "", $ex[0])."</strong>";
		return "<span style=\"$style\">".implode("<br>", $ex)."</span>";
	}
	
	public function getIcon(){
		return null;
	}
}

?>