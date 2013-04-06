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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
require_once(__DIR__."/CCTicketShop.class.php");

class CCTicketPOS extends CCTicketShop implements iCustomContent {
	function __construct() {
		parent::__construct();
		
		$this->fromPOS = true;
	}
	function classes(){
		registerClassPath("Bestellung", Util::getRootPath()."ubiquitous/Bestellungen/Bestellung.class.php");
		
		addClassPath(Util::getRootPath()."MMDB/Seminare/");
	}
	
	function getLabel(){
		return "Ticket-POS";
	}
	
	function getCMSHTML() {
		if (!isset($_SERVER['PHP_AUTH_USER']) OR $_SERVER['PHP_AUTH_USER'] == "") {
			header('WWW-Authenticate: Basic realm="Ticket POS"');
			header('HTTP/1.0 401 Unauthorized');
			
			die("Authentifikation fehlgeschlagen");
		}
		
		$EC = new ExtConn(Util::getRootPath());
		if(!$EC->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])){
			header('WWW-Authenticate: Basic realm="Ticket POS"');
			header('HTTP/1.0 401 Unauthorized');
			
			die("Authentifikation fehlgeschlagen");
		}
		
		$html = "<div style=\"width:200px;float:right;\">Angemeldet als<br /><b>".Session::currentUser()->A("name")."</b></div>";
		
		$html .= "<h1>Ticket POS</h1>";
		
		$AC = anyC::get("Seminar");
		$AC->addAssocV3("SeminarVon", ">=", time() - 3600 * 48);
		
		$Events = array();
		while($S = $AC->getNextEntry()){
			$Events[$S->getID()] = $S->A("SeminarName").", ".Util::CLFormatDate($S->A("SeminarVon"), true);
		}
		
		$I = new HTMLInput("currentEvent", "select", null, $Events);
		$I->style("font-size:20px;width:45%;");
		
		$html .= "<div style=\"margin-bottom:45px;\">$I</div>";
		
		$TS = new CCTicketShop();
		
		/*$count = array();
		for($i = 0; $i < 21; $i++)
			$count[$i] = $i;
		
		$I = new HTMLInput("ticketCount", "select", null, $count);
		$I->style("width:100%;font-size:20px;");
		
		$IC = new Button("Weiter", "");
		$IC->onclick("CustomerPage.rme('handleTicketSale', [$('select[name=currentEvent]').val(), $('select[name=ticketCount]').val()], function(){  })");
		$IC->className("submitFormButton");*/
		
		$html .= "
		<div style=\"float:right;width:45%;\">
			<h2 style=\"margin-bottom:10px;\">Ticketverkauf</h2>
			".$TS->getCMSHTML(false)."
			<!--<div style=\"border:1px dashed grey;padding:10px;margin-top:10px;\">
				Anzahl der Tickets:
				$I$IC
				<div style=\"clear:both;\"></div>
			</div>-->
		</div>";
		
		
		$I = new HTMLInput("ticketCheck");
		$I->style("width:98%;font-size:20px;");
		$I->onEnter("CustomerPage.rme('handleTicketCheck', [$('select[name=currentEvent]').val(), $(this).val()], function(transport){ $('#ticketValidInfo').html(transport); })");
		
		$html .= "
		<div style=\"width:45%;\">
			<h2 style=\"margin-bottom:10px;\">Einlass</h2>
			<div style=\"border:1px dashed #BBBBBB;padding:10px;\">
				Ticket-Nummer:
				$I
				<div id=\"ticketValidInfo\" style=\"font-size:20px;margin-top:20px;\">
		
				</div>
			</div>
		</div>";
		
		return $html;
	}
	
	public function handleTicketCheck($data){
		if(strpos($data["P1"], "TIC") !== 0)
			die("<span style=\"color:red;\">Eingabe $data[P1] ungültig.</span>");
		
		$ticketID = str_replace("TIC", "", $data["P1"]) * 1;
		
		$AC = anyC::get("STeilnehmerTicket", "STeilnehmerTicketSeminarID", $data["P0"]);
		$AC->addAssocV3("STeilnehmerTicketID", "=", $ticketID);
		
		$T = $AC->getNextEntry();
		if($T == null){
			$AC = anyC::get("STeilnehmerTicket", "STeilnehmerTicketID", $ticketID);
			$T = $AC->getNextEntry();
			
			$addHTML = "";
			if($T != null) {
				$S = new Seminar($T->A("STeilnehmerTicketSeminarID"));
				
				$addHTML = "<br /><br /><span style=\"color:grey;\">Dieses Ticket wurde für folgende Veranstaltung ausgestellt:<br />".$S->A("SeminarName").", ".$S->A("SeminarVon")."</span>";
			}
			
			die("<span style=\"color:red;\">Ticket $data[P1] ungültig.</span>".$addHTML);
		}
		
		if($T->A("STeilnehmerTicketFirstSeen") == "0"){
			$T->changeA("STeilnehmerTicketFirstSeen", time());
			$T->saveMe();
		}
		
		die("<span style=\"color:green;\">Ticket TIC$ticketID gültig für ".$T->A("STeilnehmerTicketVorname")." ".$T->A("STeilnehmerTicketNachname").".</span>");
	}
	
	/*public function handleTicketSale(){
		$_SESSION["ticketDataSelection"] = array($data["P0"], $data["P1"]);
		$_SESSION["ticketStep"] = 2;
	}*/
	
	function handleForm($valuesAssocArray){
		$this->classes();
		
		parent::handleForm($valuesAssocArray);
		
		switch($valuesAssocArray["action"]){
			case "handleAddress":
				$_SESSION["ticketDataPayment"] = array("via" => "cash");
				$_SESSION["ticketStep"] = 5;
			break;
		}
	}
}
?>