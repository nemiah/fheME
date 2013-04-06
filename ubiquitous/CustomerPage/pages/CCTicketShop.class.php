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
class CCTicketShop implements iCustomContent {
	private $EC;
	protected $fromPOS = false;
	private $paymentMethods = array("debit" => "Lastschrift", "transfer" => "Überweisung", "paypal" => "PayPal");
	
	function __construct() {
		$this->EC = new ExtConn(Util::getRootPath());
		$this->EC->useUser();
		
		$this->classes();
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getLabel(){
		return "Ticket-Shop";
	}
	
	function classes(){
		#registerClassPath("Seminar", Util::getRootPath()."MMDB/Seminare/Seminar.class.php");
		#registerClassPath("SeminarGUI", Util::getRootPath()."MMDB/Seminare/SeminarGUI.class.php");
		#registerClassPath("STeilnehmer", Util::getRootPath()."MMDB/Seminare/STeilnehmer.class.php");
		registerClassPath("Bestellung", Util::getRootPath()."ubiquitous/Bestellungen/Bestellung.class.php");
		
		addClassPath(Util::getRootPath()."MMDB/Seminare/");
		addClassPath(Util::getRootPath()."open3A/Adressen/");
		addClassPath(Util::getRootPath()."open3A/Kunden/");
		addClassPath(Util::getRootPath()."open3A/Auftraege/");
		addClassPath(Util::getRootPath()."open3A/Stammdaten/");
		addClassPath(Util::getRootPath()."open3A/Textbausteine/");
		addClassPath(Util::getRootPath()."open3A/Kategorien/");
		addClassPath(Util::getRootPath()."open3A/Brief/");
	}
	
	function getCMSHTML($header = true) {
		$head = "<h1>Ticket-Shop</h1>";
		
		if(!$header)
			$head = "";
		
		$back = "<p><a href=\"#\" onclick=\"CustomerPage.rme('handleBack', [], function(){ document.location.reload(); }); return false;\">Zurück</a></p>";
		
		if(isset($_SESSION["ticketStep"]) AND $_SESSION["ticketStep"] == 6)
			return $head.$this->showPayPal();#.$back;
		
		if(isset($_SESSION["ticketStep"]) AND $_SESSION["ticketStep"] == 5)
			return $head.$this->showOverview().$back;
		
		if(isset($_SESSION["ticketStep"]) AND $_SESSION["ticketStep"] == 4)
			return $head.$this->showPayment().$back;
		
		if(isset($_SESSION["ticketStep"]) AND $_SESSION["ticketStep"] == 3)
			return $head.$this->showAddress().$back;
		
		if(isset($_SESSION["ticketStep"]) AND $_SESSION["ticketStep"] == 2)
			return $head.$this->showTickets().$back;
		
		return $head.$this->showWarenkorb();
		
	}
	
	function showOverview(){
		$html = "";
		
		$T = new HTMLTable(4, "Events");
		$T->setTableStyle("width:100%;");
		$T->addHeaderRow(array("Event", "Anzahl", "Preis", "Gesamt"));
		
		$gesamtpreis = 0;
		$mwst = 0;
		foreach($_SESSION["ticketDataSelection"] AS $SeminarID => $anzahl){
			if($anzahl == 0)
				continue;
			$S = new Seminar($SeminarID);
			
			$T->addRow(array("".$S->A("SeminarName").", ".$S->A("SeminarVon")." ab ".Util::CLTimeParser($S->A("SeminarStart"))." Uhr", $anzahl, $S->A("SeminarPreisErwachsene"), Util::formatCurrency("de_DE", $anzahl * $S->A("SeminarPreisErwachsene"), true)));
			$T->addColStyle(1, "text-align:left;");
			$T->addColStyle(2, "text-align:right;");
			$T->addColStyle(3, "text-align:right;");
			$T->addColStyle(4, "text-align:right;");
			
			$gesamtpreis += $anzahl * $S->A("SeminarPreisErwachsene");
			$mwst = $S->A("SeminarMwSt");
			#$T->addCellID(4, "PreisGesamt".$S->getID());
		}
		
		$T->addRow(array("<label style=\"width:auto;\">Zu zahlender Betrag:</label>", "", "", "<b>".Util::formatCurrency("de_DE", $gesamtpreis, true)."</b>"));
		$T->addRowColspan(1, 3);
		$T->addRowStyle("border-top:1px solid black;");
		
		$calcMwst = Util::kRound($gesamtpreis / (100 + $mwst) * $mwst);
		
		$T->addRow(array("MwSt (".Util::CLNumberParser($mwst)."%):", "", "", Util::formatCurrency("de_DE", $calcMwst, true)));
		$T->addRowColspan(1, 3);
		$T->addCellStyle(1, "text-align:right;");
		
		$T->addRow(array("Netto-Betrag:", "", "", Util::formatCurrency("de_DE", $gesamtpreis - $calcMwst, true)));
		$T->addRowColspan(1, 3);
		$T->addCellStyle(1, "text-align:right;");
		
		$html .= $T;
		
		
		$T = new HTMLTable(2, "Rechnungsdaten");
		$T->setTableStyle("width:100%;");
		$T->addLV("Name:", $_SESSION["ticketDataAddress"]["vorname"]." ".$_SESSION["ticketDataAddress"]["nachname"]);
		$T->addLV("Firma:", $_SESSION["ticketDataAddress"]["firma"]);
		$T->addRow(array("&nbsp;", ""));
		
		$T->addLV("E-Mail:", $_SESSION["ticketDataAddress"]["email"]);
		$T->addLV("Telefon:", $_SESSION["ticketDataAddress"]["tel"]);
		$T->addRow(array("&nbsp;", ""));
		
		$T->addLV("Straße:", $_SESSION["ticketDataAddress"]["strasse"]." ".$_SESSION["ticketDataAddress"]["nr"]);
		$T->addLV("Ort:", $_SESSION["ticketDataAddress"]["plz"]." ".$_SESSION["ticketDataAddress"]["ort"]);
		$T->addLV("Land:", ISO3166::getCountryToCode($_SESSION["ticketDataAddress"]["land"]));
		
		$html .= $T;
		
		$this->paymentMethods["cash"] = "Bar";
		
		$T = new HTMLTable(2, "Zahlung");
		$T->addLV("Zahlungsart:", $this->paymentMethods[$_SESSION["ticketDataPayment"]["via"]]);
		$T->setTableStyle("width:100%;");
		
		if($_SESSION["ticketDataPayment"]["via"] == "debit"){
			$T->addRow(array("&nbsp;", ""));
			$T->addLV("Inhaber:", $_SESSION["ticketDataPayment"]["debitInhaber"]);
			$T->addLV("Kontonummer:", $_SESSION["ticketDataPayment"]["debitKontonummer"]);
			$T->addLV("BLZ:", $_SESSION["ticketDataPayment"]["debitBlz"]);
			#$T->addLV("Name der Bank:", $_SESSION["ticketDataPayment"]["debitBankName"]);
		}
		/*
		$T->addRow(array("&nbsp;", ""));
		
		$T->addLV("E-Mail:", $_SESSION["ticketDataAddress"]["email"]);
		$T->addLV("Telefon:", $_SESSION["ticketDataAddress"]["tel"]);
		$T->addRow(array("&nbsp;", ""));
		
		$T->addLV("Straße:", $_SESSION["ticketDataAddress"]["strasse"]." ".$_SESSION["ticketDataAddress"]["nr"]);
		$T->addLV("Ort:", $_SESSION["ticketDataAddress"]["plz"]." ".$_SESSION["ticketDataAddress"]["ort"]);
		$T->addLV("Land:", ISO3166::getCountryToCode($_SESSION["ticketDataAddress"]["land"]));*/
		
		
		
		$I = new Button("Tickets kaufen", "");
		$I->onclick("CustomerPage.rme('handleOrder', [], function(){ document.location.reload(); })");
		$I->className("submitFormButton");
		
		$T->addRow($I);
		$T->addRowColspan(1, 2);
		
		$html .= $T;
		
		return "<form>".$html."</form>";
	}
	
	function showPayment(){
		$html = "<script type=\"text/javascript\">
		$(function() {
			jQuery.validator.addMethod('debit', function(value, element, params) {
				if($('select[name=via]').val() != 'debit')
					return true;
				
				if($('input[name=debitInhaber]').val() == '')
					return false;
				
				if($('input[name=debitKontonummer]').val() == '')
					return false;
				
				if($('input[name=debitBlz]').val() == '')
					return false;
				
				/*if($('input[name=debitBankName]').val() == '')
					return false;*/
				
				return true;
			}, 'Bitte geben Sie unten Ihre Kontodaten ein.');
				

			$('#ticketPayment').validate({
				rules: {
					via: { debit : true }
				},
				messages: {
					
				}
			});
		});
		
		</script>";
		
		#echo ini_get("session.gc_maxlifetime") / 60;
				
		$F = new HTMLForm("ticketPayment", array("via", "debitInhaber", "debitKontonummer", "debitBlz", "action"), "Zahlung");
		
		$F->setType("via", "select", "debit", $this->paymentMethods);
		$F->setType("action", "hidden");
		
		$F->setLabel("via", "Zahlungsart");
		$F->setLabel("debitInhaber", "Inhaber");
		$F->setLabel("debitKontonummer", "Kontonummer");
		$F->setLabel("debitBlz", "BLZ");
		#$F->setLabel("debitBankName", "Name der Bank");
		
		$F->setValue("action", "handlePayment");
		
		if(isset($_SESSION["ticketDataPayment"]))
			foreach($_SESSION["ticketDataPayment"] AS $k => $v)
				$F->setValue ($k, $v);
		
		$F->hideIf("via", "!=", "debit", "onchange", array("debitInhaber", "debitKontonummer", "debitBlz"));#, "debitBankName"));
		
		$F->setSaveCustomerPage("Weiter", "", true, "function(){ document.location.reload(); }");
		
		return $html.$F;
	}
	
	function showAddress(){
		$html = "<script type=\"text/javascript\">
		$(function() {
			jQuery.validator.addMethod('firmOrName', function(value, element, params) {
				
				if($('input[name=firma]').val() != '')
					return true;
				
				if($('input[name=nachname]').val() == '')
					return false;
				
				if($('input[name=vorname]').val() == '')
					return false;
				
				return true;
			}, 'Bitte geben Sie Ihren Firmennamen <b>oder</b> Ihren Vor- und Nachnamen ein.');
		});
		$(function() {
			$('#ticketAddress').validate({
				rules: {
					nachname: {firmOrName: true},
					/*vorname: {firmOrName: true},
					firma: {firmOrName: true},*/
					strasse: 'required',
					nr: 'required',
					plz: 'required',
					ort: 'required',
					email: {
						required: true,
						email: true
					}
				},
				groups: {
					strasseNr: 'nr strasse',
					plzOrt: 'ort plz'
				},

				messages: {
					strasse: 'Bitte geben Sie Straße und Hausnummer ein',
					nr: 'Bitte geben Sie Straße und Hausnummer ein',
					plz: 'Bitte geben Sie Postleitzahl und Ort ein',
					ort: 'Bitte geben Sie Postleitzahl und Ort ein',
					email: {required: 'Bitte geben Sie Ihre E-Mail-Adresse ein', email: 'Bitte geben Sie Ihre gültige E-Mail-Adresse ein'}
				},
				
				errorPlacement: function(error, element) {
					var name = element.attr('name');
					if (name === 'strasse' || name === 'nr') {
						error.insertAfter('input[name=nr]');
						return;
					} 
					
					if (name === 'plz' || name === 'ort') {
						error.insertAfter('input[name=ort]');
						return
					}
					
					error.insertAfter(element);
					
				}

			});
		});
		
		</script>";
		
		
		$F = new HTMLForm("ticketAddress", array(
			"firma",
			"vorname",
			"nachname",
			"email",
			#"tel",
			
			"strasse",
			"plz",
			"land",
			"action"), "Rechnungsdaten");
		
		$F->setType("land", "select", "DE", ISO3166::getCountries());
		$F->setType("strasse", "parser", null, array("CCTicketShop::strasseParser"));
		$F->setType("plz", "parser", null, array("CCTicketShop::plzParser"));
		$F->setType("action", "hidden");
		
		$F->insertSpaceAbove("strasse", "Adresse");
		$F->insertSpaceAbove("email", "Kontakt");
		
		
		$F->setLabel("email", "E-Mail");
		$F->setDescriptionField("email", "An diese Adresse werden die Rechnung und die Tickets verschickt.");
		#$F->setLabel("tel", "Telefon");
		$F->setLabel("strasse", "Straße/Nr");
		$F->setLabel("plz", "PLZ/Ort");

		$F->setValue("action", "handleAddress");
		
		if(isset($_SESSION["ticketDataAddress"]))
			foreach($_SESSION["ticketDataAddress"] AS $k => $v)
				$F->setValue($k, $v);
		
		$F->setSaveCustomerPage("Weiter", null, true, "function(){ document.location.reload(); }");
		
		$html .= $F;
		
		return $html;
	}
	
	// <editor-fold defaultstate="collapsed" desc="strasseParser">
	public static function strasseParser(){
		$IS = new HTMLInput("strasse");
		$IS->style("width:65%;");
		if(isset($_SESSION["ticketDataAddress"]))
			$IS->setValue ($_SESSION["ticketDataAddress"]["strasse"]);
		
		$IN = new HTMLInput("nr");
		$IN->style("width:20%;text-align:right;margin-left:17px;");
		if(isset($_SESSION["ticketDataAddress"]))
			$IN->setValue($_SESSION["ticketDataAddress"]["nr"]);
		
		return $IS.$IN;
	}
	// </editor-fold>
	
	// <editor-fold defaultstate="collapsed" desc="plzParser">
	public static function plzParser(){
		$IS = new HTMLInput("plz");
		$IS->style("width:20%;text-align:right;");
		if(isset($_SESSION["ticketDataAddress"]))
			$IS->setValue ($_SESSION["ticketDataAddress"]["plz"]);
		
		$IN = new HTMLInput("ort");
		$IN->style("width:65%;margin-left:17px;");
		if(isset($_SESSION["ticketDataAddress"]))
			$IN->setValue($_SESSION["ticketDataAddress"]["ort"]);
		
		return $IS.$IN;
	}
	// </editor-fold>
	
	function showTickets(){
		
		$requiredFields = array();
		$html = "<form id=\"ticketDaten\">";
		foreach($_SESSION["ticketDataSelection"] AS $SeminarID => $anzahl){
			$S = new Seminar($SeminarID);
			$Adresse = new Adresse($S->A("SeminarAdresseID"));
			$T = new HTMLTable(2, "<b>".$S->A("SeminarName")."</b>, ".$S->A("SeminarVon")." ab ".Util::CLTimeParser($S->A("SeminarStart"))." Uhr, ".$Adresse->A("ort"));
			
			for($i = 0; $i < $anzahl; $i++){
				$T->addRow(array("Ticket ".($i+1)));
				$T->addRowColspan(1, 2);
				
				$requiredFields[] = "Vorname_{$SeminarID}_$i";
				$requiredFields[] = "Nachname_{$SeminarID}_$i";
				$requiredFields[] = "Email_{$SeminarID}_$i";
				$requiredFields[] = "Unternehmen_{$SeminarID}_$i";
				$requiredFields[] = "Position_{$SeminarID}_$i";
				
				$T->addLV("Vorname:", new HTMLInput("Vorname_{$SeminarID}_$i", "text", isset($_SESSION["ticketDataTickets"]["Vorname_{$SeminarID}_$i"]) ? $_SESSION["ticketDataTickets"]["Vorname_{$SeminarID}_$i"] : ""));
				$T->addLV("Nachname:", new HTMLInput("Nachname_{$SeminarID}_$i", "text", isset($_SESSION["ticketDataTickets"]["Nachname_{$SeminarID}_$i"]) ? $_SESSION["ticketDataTickets"]["Nachname_{$SeminarID}_$i"] : ""));
				$T->addLV("Unternehmen:", new HTMLInput("Unternehmen_{$SeminarID}_$i", "text", isset($_SESSION["ticketDataTickets"]["Unternehmen_{$SeminarID}_$i"]) ? $_SESSION["ticketDataTickets"]["Unternehmen_{$SeminarID}_$i"] : ""));
				$T->addLV("Position:", new HTMLInput("Position_{$SeminarID}_$i", "text", isset($_SESSION["ticketDataTickets"]["Position_{$SeminarID}_$i"]) ? $_SESSION["ticketDataTickets"]["Position_{$SeminarID}_$i"] : ""));
				$T->addLV("E-Mail:", new HTMLInput("Email_{$SeminarID}_$i", "text", isset($_SESSION["ticketDataTickets"]["Email_{$SeminarID}_$i"]) ? $_SESSION["ticketDataTickets"]["Email_{$SeminarID}_$i"] : ""));

			}
			
			$html .= $T;
		}
		
		$I = new Button("Weiter", "");
		$I->onclick("if($('#ticketDaten').valid()) CustomerPage.rme('handleForm', $('#ticketDaten').serialize(), function(){ document.location.reload(); })");
		$I->className("submitFormButton");
		
		$T = new HTMLTable(1);
		$T->setTableStyle("width:100%;");
		$T->addRow($I);
		
		$IA = new HTMLInput("action", "hidden", "handleTickets");
		
		$html .= "$IA$T</form>";
		
		
		
		$html .= "<script type=\"text/javascript\">
		$(function() {
			$('#ticketDaten').validate({
				rules: {";
		
		foreach($requiredFields AS $fieldName){
			$html .= "
					$fieldName: 'required',";
		}
		
		$html .= "
				},
				messages: {
				
				}
			});
		});
		
		</script>";
		
		return $html;
	}
	
	function showWarenkorb(){
		$AC = anyC::get("Seminar");
		$AC->addAssocV3("SeminarVon", ">=", Util::parseDate("de_DE", Util::formatDate("de_DE", time())));
		
		$count = array();
		for($i = 0; $i < 21; $i++)
			$count[$i] = $i;
		
		$T = new HTMLTable(3, "Events");
		$T->setTableStyle("width:100%;");
		
		while($S = $AC->getNextEntry()){
			$I = new HTMLInput("AnzahlKarten".$S->getID(), "select", "0", $count);
			$I->style("width:80px;text-align:right;");
			$I->onchange("CustomerPage.rme('recalc', ['".$S->getID()."', this.value], function(response){ $('#PreisGesamt".$S->getID()."').html(response) });");
			
			if(isset($_SESSION["ticketDataSelection"]))
				$I->setValue($_SESSION["ticketDataSelection"][$S->getID()]);
			
			$Adresse = new Adresse($S->A("SeminarAdresseID"));
			
			$T->addRow(array("<b>".$S->A("SeminarName")."</b>, ".Util::CLDateParser($S->A("SeminarVon"))." ab ".Util::CLTimeParser($S->A("SeminarStart"))." Uhr, ".$Adresse->A("ort")));
			$T->addRowColspan(1, 3);
			$T->addRow(array($I, Util::formatCurrency("de_DE", $S->A("SeminarPreisErwachsene") * 1, true), Util::formatCurrency("de_DE", $S->A("SeminarPreisErwachsene") * $I->getValue(), true)));
			$T->addCellStyle(1, "text-align:right;");
			$T->addCellStyle(2, "text-align:right;");
			$T->addCellStyle(3, "text-align:right;");
			
			$T->addCellID(3, "PreisGesamt".$S->getID());
			
			$T->addRow(array("&nbsp;"));
			$T->addRowColspan(1, 3);
		}
		
		$I = new Button("Weiter", "");
		$I->onclick("CustomerPage.rme('handleForm', $('#ticketShop').serialize(), function(){ document.location.reload(); })");
		$I->className("submitFormButton");
		
		$T->addRow($I);
		$T->addRowColspan(1, 3);
		
		$IA = new HTMLInput("action", "hidden", "handleSelection");
		
		return "<form id=\"ticketShop\">".$T.$IA."</form>";
	}
	
	function showPayPal(){
		$new = "<p><a href=\"#\" onclick=\"CustomerPage.rme('handleRestart', [], function(){ document.location.reload(); }); return false;\">Weitere Karten kaufen</a></p>";
		
		switch ($_SESSION["ticketDataPayment"]["via"]){
			#default:
			case "paypal":
		#www.paypal.com
				$paypalHTML = '<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="payPalForm">
					<p>Vielen Dank für Ihren Einkauf!<br />Sie erhalten die Rechnung in Kürze per E-Mail.<br /><br /><b>Um die Zahlung abzuschließen, klicken Sie bitte auf nachfolgenden Knopf:</b></p>
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="currency_code" value="EUR" />
	<input type="hidden" name="charset" value="utf-8" />
	<input type="hidden" name="invoice" value="'.  implode(";", $_SESSION["ticketDataOrderIDs"]).'" />
	<input type="hidden" name="business" value="nemi_1341164850_biz@2sins.de" />';
				$i = 1;
				foreach($_SESSION["ticketDataSelection"] AS $SeminarID => $anzahl){
					if($anzahl == 0)
						continue;
					$S = new Seminar($SeminarID, false);

					$paypalHTML .= '
			<input type="hidden" name="item_name_'.$i.'" value="'.$S->A("SeminarName").", ".$S->A("SeminarVon")." ab ".Util::CLTimeParser($S->A("SeminarStart"))." Uhr".'" />
			<input type="hidden" name="amount_'.$i.'" value="'.($anzahl * $S->A("SeminarPreisErwachsene")).'" />';

					$i++;

				}
				$paypalHTML .= '
	<p>
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc.gif" style="width:auto;border:0px;" name="submit" />
	</p>
	'.$new.'
</form>
<script type="text/javascript">
$(document).ready(function() {
    $("#payPalForm").submit(function() {
        window.open("", "payPalpopup", "width=1000,height=600,resizeable,scrollbars");
        this.target = "payPalpopup";
    });
	//$("#payPalForm").submit();
});

</script>';
				
				$html .= $paypalHTML;
			break;
			
			case "debit":
				$html = "<form><p>Vielen Dank für Ihren Einkauf!<br />Sie erhalten die Rechnung in Kürze per E-Mail.<br /><br /><b>Der Rechnungsbetrag wird von uns von Ihrem Konto abgebucht.</b></p>$new</form>";
			break;
			
			case "transfer":
				$S = mStammdaten::getActiveStammdaten();
				$T = new HTMLTable(2);
				$T->addLV("Kontoinhaber:", $S->A("firmaLang"));
				$T->addLV("BLZ:", $S->A("blz"));
				$T->addLV("Konto:", $S->A("ktonr"));
				
				if($S->A("IBAN") != "" AND $S->A("SWIFTBIC") != ""){
					$T->addRow(array("", ""));
					$T->addLV("IBAN", $S->A("IBAN"));
					$T->addLV("BIC", $S->A("SWIFTBIC"));
				}
				
				$T->addRow(array("", ""));
				$T->addLV("Verw. zweck:", "Auftrag ".implode(", ", $_SESSION["ticketDataOrderIDs"]));
				
				$html = "<form><p>Vielen Dank für Ihren Einkauf!<br />Sie erhalten die Rechnung in Kürze per E-Mail.<br /><br /><b>Bitte überweisen Sie den Rechnungsgetrag auf folgendes Konto:</b></p>$T<p>Diese Daten finden Sie auch auf der Rechnung.</p>$new</form>";
			break;
			
			default:
				$html = "<form><p>Vielen Dank für Ihren Einkauf!<br />Sie erhalten die Rechnung in Kürze per E-Mail.</p>$new</form>";
			break;
		}
		
		return $html;
	}
	
	public function recalc($data){
		$S = new Seminar($data["P0"]);
		
		return Util::formatCurrency("de_DE", $S->A("SeminarPreisErwachsene") * $data["P1"], true);
	}
	
	function handleOrder(){
		$values = $_SESSION["ticketDataAddress"];
		
		$F = new Factory("Adresse");
		$values["land"] = ISO3166::getCountryToCode($values["land"]);
		$F->fill($values);

		$exists = $F->exists(true);
		if(!$exists){
			$AdresseID = $F->store(false, false);
		
			$K = new Kunden();
			$Kappendix = $K->createKundeToAdresse($AdresseID, false, true);
		} else {
			$AdresseID = $exists->getID();
			
			$Kappendix = Kappendix::getKappendixToAdresse($AdresseID);
		}
		
		if($_SESSION["ticketDataPayment"]["via"] == "debit"){
			$Kappendix->changeA("KappendixKontonummer", $_SESSION["ticketDataPayment"]["debitKontonummer"]);
			$Kappendix->changeA("KappendixBLZ", $_SESSION["ticketDataPayment"]["debitBlz"]);
			$Kappendix->changeA("KappendixKontoinhaber", $_SESSION["ticketDataPayment"]["debitInhaber"]);
			$Kappendix->changeA("KappendixEinzugsermaechtigung", "1");
			$Kappendix->changeA("KappendixEinzugsermaechtigungAltZBTB", "5");
			$Kappendix->changeA("KappendixSameKontoinhaber", "0");
		}
		
		if(!$exists)
			$Kappendix->newMe(false);
		else 
			$Kappendix->saveMe();
		
		$zahlungsart = 6;
		if($_SESSION["ticketDataPayment"]["via"] == "debit")
			$zahlungsart = 1;
		
		if($_SESSION["ticketDataPayment"]["via"] == "transfer")
			$zahlungsart = 5;
		
		if($_SESSION["ticketDataPayment"]["via"] == "paypal")
			$zahlungsart = 7;
		
		$orderIDs = array();
		foreach($_SESSION["ticketDataSelection"] AS $SeminarID => $anzahl){
			if($anzahl == 0)
				continue;
			
			$F = new Factory("STeilnehmer");
			
			$F->sA("STeilnehmerSeminarID", $SeminarID);
			$F->sA("STeilnehmerAdresseID", $AdresseID);
			$F->sA("STeilnehmerAngemeldetAm", time());
			$F->sA("STeilnehmerErwachsene", $anzahl);
			$F->sA("STeilnehmerZahlungsart", $zahlungsart);
			
			$STeilnehmerID = $F->store();
			
			$Tickets = array();
			foreach($_SESSION["ticketDataTickets"] AS $k => $v){
				$ex = explode("_", $k);
				if(count($ex) != 3)
					continue;
				
				if($ex[1] != $SeminarID)
					continue;
				
				if(!isset($Tickets[$ex[2]]))
					$Tickets[$ex[2]] = array();
				
				$Tickets[$ex[2]][$ex[0]] = $v;
			}
			
			foreach ($Tickets AS $ticket){
				$F = new Factory("STeilnehmerTicket");
				
				$F->sA("STeilnehmerTicketSeminarID", $SeminarID);
				$F->sA("STeilnehmerTicketSTeilnehmerID", $STeilnehmerID);
				$F->sA("STeilnehmerTicketVorname", $ticket["Vorname"]);
				$F->sA("STeilnehmerTicketNachname", $ticket["Nachname"]);
				$F->sA("STeilnehmerTicketPosition", $ticket["Position"]);
				$F->sA("STeilnehmerTicketUnternehmen", $ticket["Unternehmen"]);
				$F->sA("STeilnehmerTicketEMail", $ticket["Email"]);
				if($this->fromPOS)
					$F->sA("STeilnehmerTicketFirstSeen", time());
				
				$F->store();
			}
			
			$S = new Seminar($SeminarID);
			$S->createRechnungen($STeilnehmerID);
			
			foreach($S->createdGRLBMs AS $GRLBM){
				$Auftrag = new Auftrag($GRLBM->A("AuftragID"));
				$Auftrag->sendViaEmail($GRLBM->getID(), "", "", "", false);
				
				$B = new Bestellung(-1);
				$orderIDs[] = $B->createFromInvoice($GRLBM->A("AuftragID"), $GRLBM, "MMDB/Seminare/STeilnehmer", $STeilnehmerID);
			}
		}
		
		$_SESSION["ticketStep"] = 6;
		$_SESSION["ticketDataOrderIDs"] = $orderIDs;
	}
	
	function handleForm($valuesAssocArray){
		$this->classes();
		
		switch($valuesAssocArray["action"]){
			case "handleTickets":
				$_SESSION["ticketDataTickets"] = $valuesAssocArray;
				$_SESSION["ticketStep"] = 3;
			break;
		
			case "handleAddress":
				$_SESSION["ticketDataAddress"] = $valuesAssocArray;
				$_SESSION["ticketStep"] = 4;
			break;
			
			case "handlePayment":
				$_SESSION["ticketDataPayment"] = $valuesAssocArray;
				$_SESSION["ticketStep"] = 5;
				
			break;
		
			case "handleSelection":
				$allZero = true;
				foreach($valuesAssocArray AS $k => $v){
					if($v > 0)
						$allZero = false;
				}

				if($allZero)
					Red::alertD ("Bitte wählen Sie bei mindestens einer Veranstaltung Karten aus.");

				$SeminarData = array();
				foreach ($valuesAssocArray AS $k => $v){
					if(stripos($k, "AnzahlKarten") === false)
						continue;

					$SeminarData[str_replace("AnzahlKarten", "", $k)] = $v;
				}
				
				if(count($SeminarData)){
					$_SESSION["ticketDataSelection"] = $SeminarData;
					$_SESSION["ticketStep"] = 2;
				}
			break;
		}
	}
	
	public function handleBack(){
		if(isset($_SESSION["ticketStep"]) AND $_SESSION["ticketStep"] > 1)
			$_SESSION["ticketStep"]--;
	}
	
	public function handleRestart(){
		unset($_SESSION["ticketDataSelection"]);
		unset($_SESSION["ticketDataTickets"]);
		$_SESSION["ticketStep"] = 1;
	}
}
?>