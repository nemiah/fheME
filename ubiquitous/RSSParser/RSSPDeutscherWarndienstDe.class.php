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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class RSSPDeutscherWarndienstDe implements iFileBrowser, iRSSParser {
	private $title = null;
	private $ok = false;
	
	public function getLabel() {
		return "deutscher-warndienst.de RSS-Feed";
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
		if($this->ok)
			return "";
		
		$description = trim(str_replace(array($this->title, "DWD / RZ München ", "ausgegeben vom Deutschen Wetterdienst "), "", $description));
		
		#$description = nl2br(trim(str_replace("<br />", "\n", $description)));
		$description = preg_replace("/^[\t\n]*<br \/>[\t\n]*/", "", $description);
		$description = preg_replace("/<br \/>[\t\n]*<br \/>[\t\n]*<br \/>[\t\n]*$/", "", $description);
		
		return preg_replace("/<br \/>[\t\n]*<br \/>[\t\n]*<br \/>/", "", $description);
	}
	
	public function getIcon(){
		if($this->ok)
			return new Button("OK", "check", "iconicL");
		
		$B = new Button("Warnung", "bolt", "iconicL");
		$B->style("color:rgb(220, 0, 0);");
		
		return $B;
	}
}

?>