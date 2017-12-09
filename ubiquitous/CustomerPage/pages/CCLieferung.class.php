<?php
/**
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

ini_set('session.gc_maxlifetime', 24 * 60 * 60);

class CCLieferung implements iCustomContent {
	private $loggedIn = true;
	function __construct() {
		$this->loggedIn = Session::currentUser() != null;
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getLabel(){
		return "Lieferungs-Erfassung";
	}
	
	function getCMSHTML() {
		if(!$this->loggedIn){
			$T = new HTMLForm("login", array("benutzer", "password", "action"), "Anmeldung");
			
			$T->setValue("action", "login");
			$T->setType("action", "hidden");
			$T->setType("password", "password");
			
			$T->setLabel("password", "Passwort");
			
			$T->setSaveCustomerPage("Anmelden", "", false, "function(){ document.location.reload(); }");
			
			return $T;
			
		}
		
		$BRL = new Button("Aktualisieren");
		$BRL->className("submitFormButton");
		$BRL->onclick("document.location.reload();");
		$BRL->style("float:right;margin-top:0px;");
		
		$BRA = new Button("Abmelden");
		$BRA->className("submitFormButton");
		$BRA->onclick("CustomerPage.rme('logout', {}, function(transport){ document.location.reload(); });");
		$BRA->style("background-color:#DDD;color:grey;margin-top:0px;float:right;margin-right:20px;");
		
		return "
		<div style=\"max-width:1200px;\">
			<div style=\"display:inline-block;width:48%;vertical-align:top;margin-right:3%;\" id=\"contentLeft\">
				<h1>Auftrag</h1>
				<div class=\"content\" style=\"overflow:auto;\">
					".$this->getAuftrag(array("GRLBMID" => 0))."
				</div>
			</div>
			<div style=\"display:inline-block;width:48%;vertical-align:top;\" id=\"contentRight\">
				<h1>{$BRL}{$BRA}Lieferscheine</h1>
				<div class=\"content\" style=\"overflow:auto;\">
					".$this->getLieferscheine(array(/*"KategorieID" => "", "query" => "", "GRLBMID" => $GRLBMID*/))."
				</div>
			</div>".OnEvent::script("
				\$('#contentRight .content .tableForSelection ').parent().css('max-height', $(window).height() - $('h1').outerHeight() - 40 - $('#contentRight .Tab').outerHeight())
				\$('#contentLeft .content ').css('height', $(window).height() - $('h1').outerHeight() - 25)");

	}

	public function getAuftrag($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$html = "";
		
		if($data["GRLBMID"] == 0){
			$html .= "<p class=\"highlight\" style=\"margin-top:10px;\">Bitte wählen Sie rechts einen Lieferschein.</p>";
			
			return $html;
		}
		
		$Beleg = new GRLBM($data["GRLBMID"]);#$this->createAuftrag(new Adresse(1), "W");
		$Auftrag = new Auftrag($Beleg->A("AuftragID"));
		$Adresse = new Adresse($Auftrag->A("AdresseID"));
		
		#$TAdresse = new HTMLTable(2, "Kundenadresse");
		#$TAdresse->setColWidth(1, 26);
		#$TAdresse->setTableStyle("width:100%;");
		#$TAdresse->addRow(array(new Button("Adresse", "home", "iconic"), $Adresse->getHTMLFormattedAddress()));
		#$TAdresse->setColStyle(1, "vertical-align:top;");
		
		
		$TPosten = new HTMLTable(3, "Lieferung");
		$TPosten->setTableStyle("width:100%;");
		$TPosten->setColWidth(1, 26);
		$TPosten->setColWidth(2, 80);
		$TPosten->setColWidth(3, "100%");
		
		$ACP = anyC::get("Posten", "GRLBMID", $Beleg->getID());
		$ACP->addOrderV3("PostenID");
		$ACP->addAssocV3("useForLieferung", "=", "1");
		$i = 0;
		$O = new Button("Positionen", "list", "iconic");
		/*while($P = $AC->getNextEntry()){
			$I = new HTMLInput("mwst", "text", Util::CLNumberParser($P->A("menge")));
			$I->style("text-align:right;width:80px;font-size:15px;padding:7px;padding-right:20px;");
			#$I->onEnter("\$j(this).trigger('blur');");
			$I->onblur("CustomerPage.rme('setMenge', {PostenID: '".$P->getID()."', menge: this.value}, function(){ CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ if(transport == 'TIMEOUT') { document.location.href='?CC=Lieferschein&page=login'; return; } $('#contentLeft .content').html(transport); noty({text: 'Menge gespeichert', type: 'success'}); }); });");
			$I->onfocus("this.select();");
			
			$TPosten->addRow(array(
				$i == 0 ? $O : "",
				$I, 
				"<span style=\"font-size:15px;\">".$P->A("name")."</span>"));
			
			$i++;
		}*/
		
		$AC = anyC::get("Artikel");
		$AC->addOrderV3("name");
		$AC->addAssocV3("useForLieferung", "=", "1");
		while($A = $AC->getNextEntry()){
			$menge = 0;
			while($P = $ACP->n()){
				if($P->A("oldArtikelID") == $A->getID())
					$menge = abs($P->A("menge"));
			}
			$ACP->resetPointer();
			
			$I = new HTMLInput("Artikel_".$A->getID(), "text", Util::CLNumberParser($menge));
			$I->style("text-align:right;width:80px;font-size:15px;padding:7px;padding-right:20px;");
			#$I->onEnter("\$j(this).trigger('blur');");
			#$I->onblur("CustomerPage.rme('setMenge', {PostenID: '".$P->getID()."', menge: this.value}, function(){ CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ if(transport == 'TIMEOUT') { document.location.href='?CC=Lieferschein&page=login'; return; } $('#contentLeft .content').html(transport); noty({text: 'Menge gespeichert', type: 'success'}); }); });");
			$I->onfocus("this.select();");
			
			$TPosten->addRow(array(
				$i == 0 ? $O : "",
				$I, 
				"<span style=\"font-size:15px;\">".$A->A("name")."</span>"));
			
			$i++;
		}
		
		if($AC->numLoaded() == 0){
			$TPosten->addRow(array($O, "Keine Artikel"));
			$TPosten->addRowColspan(2, 6);
			$TPosten->setColWidth(2, "100%");
			$TPosten->setColStyle(2, "text-align:left;");
		}
		
		
		
		$IOK = new Button("Speichern");
		$IOK->className("submitFormButton");
		$IOK->onclick("CustomerPage.rme('handleLieferung', $('#posten').serialize(), function(transport){ $('#contentLeft h1').html('Auftrag'); $('.selected').removeClass('selected'); $('#contentLeft .content').html(transport); })");
		#$IOK->onclick("$('#contentLeft h1').html('Auftrag'); $('.selected').removeClass('selected'); CustomerPage.rme('getAuftrag', {GRLBMID: 0}, function(transport){ $('#contentLeft .content').html(transport); }, function(){}, 'POST');");
		
		$IC = new Button("Abbrechen");
		$IC->className("submitFormButton");
		$IC->style("background-color:#DDD;color:grey;float:none;");
		$IC->onclick("$('#contentLeft h1').html('Auftrag'); $('.selected').removeClass('selected'); CustomerPage.rme('getAuftrag', {GRLBMID: 0}, function(transport){ $('#contentLeft .content').html(transport); }, function(){}, 'POST');");
		
		#$TZahlungsart = new HTMLTable(2);
		#$TZahlungsart->setTableStyle("width:100%;margin-top:50px;");
		#$TZahlungsart->setColWidth(1, 26);
		#$TZahlungsart->addRow(array("", $IOK));
		
		$IA = new HTMLInput("action", "hidden", "handleLieferung");
		$IA = new HTMLInput("GRLBMID", "hidden", $Beleg->getID());
		$html .= "
			<form id=\"posten\" style=\"border:0px;padding:0px;width:100%;\" >
				$TPosten
				$IOK$IC
				$IA
			</form>";
		
		return $html.OnEvent::script("$('#contentLeft h1').html('Auftrag ".$Beleg->A("prefix").$Beleg->A("nummer")."');");
	}
	
	public function getLieferscheine($data){
		if(!$this->loggedIn)
			return "TIMEOUT";

		$html = "";
		
		$D = new Datum();
		$D->normalize();
		$D->subMonth();
		
		$T = new HTMLTable(2);#, "Bitte wählen Sie einen Lieferschein");
		$T->setTableStyle("width:100%;margin-top:10px;");
		$T->setColWidth(1, 130);
		$T->useForSelection(false);
		$T->maxHeight(400);
		
		$AC = anyC::get("GRLBM", "isL", "1");
		$AC->addJoinV3("Auftrag", "AuftragID", "=", "AuftragID");
		$AC->addAssocV3("UserID", "=", Session::currentUser()->getID());
		$AC->addAssocV3("status", "=", "delivered");
		#$AC->addOrderV3("datum", "DESC");
		$AC->addOrderV3("nummer", "DESC");
		$AC->addAssocV3("datum", ">", $D->time());
		#$AC->setLimitV3(100);
		#$AC->addJoinV3("Adresse", "t2.AdresseID", "=", "AdresseID");
		$i = 0;
		while($B = $AC->n()){
			$Adresse = new Adresse($B->A("AdresseID"));
			$T->addRow(array("<span style=\"font-size:20px;font-weight:bold;\">".$B->A("prefix").$B->A("nummer")."</span><br><span style=\"color:grey;\">".Util::CLDateParser($B->A("datum"))."</span>", $Adresse->getHTMLFormattedAddress()));
			$T->addCellStyle(1, "vertical-align:top;");
			
			$T->addRowStyle("cursor:pointer;border-bottom:1px solid #ccc;");
			
			#if($i % 2 == 1)
			#	$T->addRowStyle ("background-color:#eee;");
			
			$T->addRowEvent("click", "$('.selected').removeClass('selected'); $(this).addClass('selected'); CustomerPage.rme('getAuftrag', {GRLBMID: ".$B->getID()."}, function(transport){ if(transport == 'TIMEOUT') { document.location.reload(); return; } $('#contentLeft .content').html(transport); }, function(){}, 'POST');");
			
			$i++;
		}
		
		$html .= $T;
		
		return $html;
	}
	
	/*public function setMenge($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$Posten = new Posten($data["PostenID"]);
		$Posten->recalcNetto = false;
		$Posten->changeA("menge", Util::CLNumberParser($data["menge"], "store"));
		$Posten->saveMe();
	}*/
	
	public function getStyle(){
		return ".selected {
			background-color:#ddd;
		}";
	}
	
	function logout(){
		$U = new Users();
		$U->doLogout();
	}
	
	function handleLieferung($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$GRLBMID = $data["GRLBMID"];
		unset($data["GRLBMID"]);
		
		$ACP = anyC::get("Posten", "GRLBMID", $GRLBMID);
		$ACP->addOrderV3("PostenID");
		$ACP->addAssocV3("useForLieferung", "=", "1");
		while($P = $ACP->n())
			$P->deleteMe();
		
		$G = new GRLBM($GRLBMID, false);
		$A = new Auftrag($G->A("AuftragID"));
		foreach($data AS $Artikel => $menge){
			if($menge == "0")
				continue;
			
			if(strpos($Artikel, "Artikel_") === false)
				continue;
			
			$ex = explode("_", $Artikel);
			
			$G->addArtikel($ex[1], $menge, null, $A->A("kundennummer"));
		}
		die("<p class=\"confirm\" style=\"margin-top:10px;\">Lieferung gespeichert!</p>");
	}
	
	function handleForm($valuesAssocArray){
		switch($valuesAssocArray["action"]){
			case "login":
				if(!Users::login($valuesAssocArray["benutzer"], sha1($valuesAssocArray["password"]), "open3A"))
					Red::errorD("Benutzer/Passwort unbekannt");
			break;
		}
	}
}
?>