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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class CCShopping implements iCustomContent {
	function getLabel(){
		return "Mobile Einkaufsliste";
	}
	
	function getTitle(){
		return "Einkaufsliste";
	}
	
	#function getViewport(){
	#	return "width=1000, initial-scale=1";
	#}
	
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
		$AC->addJoinV3("EinkaufszettelKategorie", "EinkaufszettelEinkaufszettelKategorieID", "=", "EinkaufszettelKategorieID");
		$AC->addOrderV3("EinkaufszettelKategorieName");
		$AC->addOrderV3("EinkaufszettelName");
		
		$html = "<style type=\"text/css\">
				body {
					margin:0px;
					margin-top:10px;
				}
				
				@media only screen and (max-width: 360px) {
					html {
						zoom: .8;
					}
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
					width:100%;
					box-sizing:border-box;
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
					width:100%;
					box-sizing:border-box;
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
		
		$last = null;
		while($E = $AC->getNextEntry()){
			if($last != $E->A("EinkaufszettelKategorieName") AND $last !== null){
				$html .= "<h1 style=\"padding-left:10px;padding-top:15px;padding-bottom:7px;\">".$E->A("EinkaufszettelKategorieName")."</h1>";
			}
			
			$html .= "
				<div
					id=\"entry".$E->getID()."\"
					ontouchend=\"if(Touch.cancelNext) return;  CustomerPage.rme('setBought', [".$E->getID()."], function(){ $('#entry".$E->getID()."').hide(); $('#restoreEntry".$E->getID()."').show(); $('#emptyEntry').hide(); if($('#einkaufsliste .nonEmpty').length == 0) $('#emptyEntry').show(); });\"
					class=\"nonEmpty entry backgroundColor1 borderColor1\">
					
					".($E->A("EinkaufszettelMenge") > 1 ? $E->A("EinkaufszettelMenge")." " : "").$E->A("EinkaufszettelName").($E->A("EinkaufszettelNameDetails") != "" ? "<br /><small style=\"color:grey;\">".$E->A("EinkaufszettelNameDetails")."</small>" : "")."
				</div>
				<div 
					class=\"nonEmpty entryRestore backgroundColor4 borderColor0\"
					ontouchend=\"if(Touch.cancelNext) return; CustomerPage.rme('setUnBought', [".$E->getID()."], function(){ $('#entry".$E->getID()."').show(); $('#restoreEntry".$E->getID()."').hide(); $('#emptyEntry').hide(); if($('#einkaufsliste .nonEmpty').length == 0) $('#emptyEntry').show(); });\"
					id=\"restoreEntry".$E->getID()."\" style=\"display:none;\">".$E->A("EinkaufszettelName")." $B</div>";
			
			$last = $E->A("EinkaufszettelKategorieName");
		}
		
		$html .= "<div style=\"".($AC->numLoaded() == 0 ? "" : "display:none;")."\" class=\"entry backgroundColor1 borderColor1\" id=\"emptyEntry\">Die Einkaufsliste enthält keine Einträge.<br /><small style=\"color:grey;\">".Util::CLDateParser(time())."</small></div>";
		
		$html .= "</div>";
		
		return $html.OnEvent::script("
		var Touch = {};
		
		$(document).on('touchstart mousedown', '[ontouchend]', function(ev){
			$(this).addClass('entryTouch'); 
			
			Touch.startPos = [ev.clientX, ev.clientY];
			Touch.cancelNext = false;
			Touch.inAction = this;
		
		}); 
		
		$(document).on('touchend mouseup', '[ontouchend]', function(ev){
			$(this).removeClass('entryTouch');
			Touch.inAction = false;
		});
			
		$(document).on('touchmove mousemove', '[ontouchend]', function(ev){
			if(!Touch.inAction)
				return;

			if(Math.abs(ev.clientX - Touch.startPos[0]) < 15 && Math.abs(ev.clientY - Touch.startPos[1]) < 15)
				return;

			Touch.cancelNext = true;
			$('.entryTouch').removeClass('entryTouch');
			
			//$(ev.target).trigger('touchend');
		});
	");
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