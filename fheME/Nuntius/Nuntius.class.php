<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class Nuntius extends PersistentObject {
	function message(){
			$BS = new Button("Nachricht", "comment_alt2_stroke", "iconicL");
			$BS->style("float:left;");

			$read = false;
			$message = $this->messageParser();#$this->A("NuntiusMessage");
			if($this->A("NuntiusRead") > "0"){
				$read = true;
				$BS = new Button("Nachricht", "check", "iconicL");
				$BS->style("float:left;color:darkgreen;");
				
				$ex = explode("\n", $message);
				$message = "<span style=\"color:grey;\">".$ex[0];
				if(isset($ex[1]))
					$message .= " …";
				
				$message .= "</span>";
				
			}
			
			$from = "";
			$ex = explode(":", $this->A("NuntiusSender"));
			if($ex[0] == "Device"){
				$D = new Device($ex[1]);
				$from = $D->A("DeviceName");
			}
			
			if($ex[0] == "FritzBox")
				$from = $this->A("NuntiusSender");
			
			
			return "
			<div id=\"Nuntius".$this->getID()."\" style=\"cursor:pointer;".($read ? "" : "background-color:#efefef;")."min-height:40px;margin-bottom:10px;margin-top:10px;\" onclick=\"".OnEvent::rme($this, "read", "", "function(t){ \$j('#Nuntius".$this->getID()."').replaceWith(t.responseText); fheOverview.loadContent('mNuntiusGUI::getOverviewContent'); }")."\">
				$BS
				<div style=\"margin-left:40px;\" class=\"\">
					<p><small style=\"float:right;color:grey;\">Von $from, ".Util::CLDateTimeParser($this->A("NuntiusTime"))."</small>".nl2br($message)."</p>
				</div>
			</div>";
	}
	
	function read(){
		$this->changeA("NuntiusRead", $this->A("NuntiusRead") > 0 ? "0" : time());
		$this->saveMe();
		
		echo $this->message();
	}
	
	function messageParser(){
		switch($this->A("NuntiusSender")){
			case "FritzBox":
				$ex = explode(",", $this->A("NuntiusMessage"));
				$caller = $ex[1];
				if(isset($ex[3]) AND trim($ex[3]) != "")
					$caller = $ex[3]."<br /><small style=\"color:grey;\">$ex[1]</small>";
				
				
				return "<p>Anruf von</p><p class=\"prettyTitle highlight\" style=\"text-align:center;\">$caller</p><p>An $ex[2]</p>";
				
			break;
		
			default:
				return $this->A("NuntiusMessage");
		}
	}
}
?>