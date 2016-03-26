<?php
/**
 *  This file is part of FCalc.

 *  FCalc is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  FCalc is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class CCShopping implements iCustomContent {
	function getLabel(){
		return "Mobile Einkaufsliste";
	}
	
	function getTitle(){
		return "Einkaufsliste";
	}
	
	function getCMSHTML() {
		$key = substr(Util::eK(), 0, 5);
		if(!isset($_GET["key"]) OR $key != $_GET["key"])
			return "<span style=\"color:red;\">Bitte geben Sie den richtigen Schlüssel zum Aufrufen der Seite in der Adresszeile an.</span>";
		
		registerClassPath("Einkaufszettel", Util::getRootPath()."fheME/Einkaufszettel/Einkaufszettel.class.php");
		/*
		registerClassPath("GSRaumgruppe", Util::getRootPath()."FCalc/GSRaumgruppe/GSRaumgruppe.class.php");
		registerClassPath("ObjektL", Util::getRootPath()."personalKartei/ObjekteL/ObjektL.class.php");
		registerClassPath("mGSTaetigkeitGUI", Util::getRootPath()."FCalc/GSTaetigkeit/mGSTaetigkeitGUI.class.php");
		registerClassPath("GSTaetigkeit", Util::getRootPath()."FCalc/GSTaetigkeit/GSTaetigkeit.class.php");
		 * 
		 */
		$AC = anyC::get("Einkaufszettel");
		$AC->addAssocV3("EinkaufszettelBought", "=", "0");
		$AC->addOrderV3("EinkaufszettelName");
		
		$html = "<style type=\"text/css\">
				body {
					margin:0px;
					margin-top:10px;
				}
				
				.entry {
					padding:10px;
					border-top-width:1px;
					border-top-style:solid;
					border-bottom-width:1px;
					border-bottom-style:solid;
					font-size:2em;
					margin-bottom:10px;
					cursor:pointer;
					-webkit-touch-callout: none;
					-webkit-user-select: none;
					-khtml-user-select: none;
					-moz-user-select: none;
					-ms-user-select: none;
					user-select: none;
					-webkit-tap-highlight-color:rgba(255,255,255,0);
				}
				
				.entryRestore {
					border-top-width:1px;
					border-top-style:solid;
					border-bottom-width:1px;
					border-bottom-style:solid;
					padding:10px;
					font-size:2em;
					margin-bottom:10px;
					cursor:pointer;
					-webkit-touch-callout: none;
					-webkit-user-select: none;
					-khtml-user-select: none;
					-moz-user-select: none;
					-ms-user-select: none;
					user-select: none;
					color:#444;
					-webkit-tap-highlight-color:rgba(255,255,255,0);
				}
				
				.backgroundColor4 {
					background-color: #eee;
				}
				
				.entryTouch {
					background-color: #c5d674;
				}
				
				.borderColor0 {
					border-color:white;
				}
			</style>
			<div id=\"einkaufsliste\">";
		
		$B = new Button("Wiederherstellen", "undo", "iconicL");
		
		while($E = $AC->getNextEntry()){
			$html .= "
				<div
					id=\"entry".$E->getID()."\"
					ontouchend=\"CustomerPage.rme('setBought', [".$E->getID()."], function(){ $('#entry".$E->getID()."').hide(); $('#restoreEntry".$E->getID()."').show(); $('#emptyEntry').hide(); if($('#einkaufsliste .nonEmpty').length == 0) $('#emptyEntry').show(); });\"
					class=\"nonEmpty entry backgroundColor1 borderColor1\">
					
					".($E->A("EinkaufszettelMenge") > 1 ? $E->A("EinkaufszettelMenge")." x " : "").$E->A("EinkaufszettelName").($E->A("EinkaufszettelNameDetails") != "" ? "<br /><small style=\"color:grey;\">".$E->A("EinkaufszettelNameDetails")."</small>" : "")."
				</div>
				<div 
					class=\"nonEmpty entryRestore backgroundColor4 borderColor0\"
					ontouchend=\"CustomerPage.rme('setUnBought', [".$E->getID()."], function(){ $('#entry".$E->getID()."').show(); $('#restoreEntry".$E->getID()."').hide(); $('#emptyEntry').hide(); if($('#einkaufsliste .nonEmpty').length == 0) $('#emptyEntry').show(); });\"
					id=\"restoreEntry".$E->getID()."\" style=\"display:none;\">".$E->A("EinkaufszettelName")." $B</div>";
		}
		
		$html .= "<div style=\"".($AC->numLoaded() == 0 ? "" : "display:none;")."\" class=\"entry backgroundColor1 borderColor1\" id=\"emptyEntry\">Die Einkaufsliste enthält keine Einträge.<br /><small style=\"color:grey;\">".Util::CLDateParser(time())."</small></div>";
		
		$html .= "</div>";
		
		return $html.OnEvent::script("$('.entry, .entryRestore').bind('touchstart mousedown', function(){ $(this).addClass('entryTouch'); }); $('.entry, .entryRestore').bind('touchend mouseup', function(){ $(this).removeClass('entryTouch'); });");
	}
	
	public static function setBought($args){
		$E = new Einkaufszettel($args["P0"]);
		$E->setBought();
	}
	
	public static function setUnBought($args){
		$E = new Einkaufszettel($args["P0"]);
		$E->setUnBought();
	}
	
}

?>