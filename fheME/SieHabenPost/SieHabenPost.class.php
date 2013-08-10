<?php
/**
 *  This file is part of Demo.

 *  Demo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  Demo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class SieHabenPost extends PersistentObject {
	public function process(Fhem $F, $xml){
		$state = $xml->attributes()->state;
		$stateText = "";
		$event = "\$j('#FhemControlID_".$F->getID()."').removeClass('highlight');";
		$FS = new Button("", "./images/i2/empty.png", "icon");
		$FS->style("float:left;margin-right:5px;width:32px;height:32px;");

		if($state != "off" && $state != "aus"){
			$FS->image("./fheME/SieHabenPost/SieHabenPost.png");
			$stateText = "Sie haben Post!";
			$event = "\$j('#FhemControlID_".$F->getID()."').addClass('highlight');";
		}
					
		return "$FS<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b><br /><small style=\"color:grey;\">$stateText</small><div style=\"clear:both;\"></div>".OnEvent::script($event);
	}
}
?>