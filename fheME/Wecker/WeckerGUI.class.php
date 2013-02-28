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
class WeckerGUI extends Wecker implements iGUIHTML2 {
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->setParser("WeckerTime", "Util::CLTimeParser");
	}
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Wecker");
	
		$gui->type("WeckerDeviceID", "select", anyC::get("Device"), "DeviceName");
		$gui->type("WeckerIsActive", "checkbox");
		$gui->type("WeckerRepeat", "checkbox");
		
		$gui->type("WeckerMo", "checkbox");
		$gui->type("WeckerDi", "checkbox");
		$gui->type("WeckerMi", "checkbox");
		$gui->type("WeckerDo", "checkbox");
		$gui->type("WeckerFr", "checkbox");
		$gui->type("WeckerSa", "checkbox");
		$gui->type("WeckerSo", "checkbox");
		
		$gui->space("WeckerTime");
		$gui->space("WeckerRepeat");
		
		#$gui->parser("WeckerMo", "WeckerGUI::parserTage");
		
		return $gui->getEditHTML();
	}
	
	public static function parserTage($w, $E){
		
	}
}
?>