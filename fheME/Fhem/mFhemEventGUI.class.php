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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mFhemEventGUI extends anyC implements iGUIHTML2 {
	public function getHTML($id){
		$gui = new HTMLGUI();
		$this->addOrderV3("FhemEventID");
		$this->lCV3($id);
		
		$gui->setName("Presets");
		$gui->setObject($this);
		
		$gui->setShowAttributes(array("FhemName","FhemEventAction"));
		$gui->setIsDisplayMode(true);
		$gui->setDeleteInDisplayMode(true);
		$gui->setJSEvent("onDelete","function(){ contentManager.reloadFrameLeft(); }");
		
		#$gui->addColStyle("FhemEventNightOnly","width:20px;");

		$gui->setParser("FhemEventAction","mFhemEventGUI::ActionParser",array("\$FhemEventID","\$FhemID","\$FhemEventFhemID"));
		$gui->setParser("FhemName","mFhemEventGUI::nameParser",array("\$FhemEventFhemID"));
		#$gui->setParser("FhemEventNightOnly","mFhemEventGUI::NightParser",array("\$FhemEventKategorieID"));
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}

	public static function nameParser($w, $l, $p){
		$s = HTMLGUI::getArrayFromParametersString($p);

		if($s[0] == "-1") return "custom";

		return $w;
	}

	public static function ActionParser($w, $l, $p){
		$s = HTMLGUI::getArrayFromParametersString($p);
		
		if($s[2] == "-1") {
			$I = new HTMLInput("FhemEventAction", "multiInput", $w, array("FhemEvent", $s[0], "FhemEventAction"));
			$I->style("width:95%;text-align:left;");
			
			return $I;
		}

		$Fhem = new Fhem($s[1]);
		$Fhem->loadMe();
		$op = $Fhem->getAvailableOptions();
		$o = "<option value=\"\">select...</option>";
		foreach($op as $k => $v)
			$o .= "<option ".($w == $v ? "selected=\"selected\"" : "")." value=\"$v\">$k</option>";
			
		return "<select onchange=\"rme('FhemEvent', '$s[0]', 'saveMultiEditField', Array('FhemEventAction',this.value), 'checkResponse(transport);');\">$o</select>";
	}
}
?>
