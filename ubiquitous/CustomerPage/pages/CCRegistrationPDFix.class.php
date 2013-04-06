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
class CCRegistrationPDFix implements iCustomContent {
	private $EC;
	
	function __construct() {
		$this->EC = new ExtConn(Util::getRootPath());
		$this->EC->useUser("birel");
	}
	
	function getLabel(){
		return "Registrierung";
	}
	
	function classes(){
		registerClassPath("Adresse", Util::getRootPath()."open3A/Adressen/Adresse.class.php");
		registerClassPath("AdresseAdapter", Util::getRootPath()."open3A/Adressen/AdresseAdapter.class.php");
		registerClassPath("Kunden", Util::getRootPath()."open3A/Kunden/Kunden.class.php");
		registerClassPath("Kappendix", Util::getRootPath()."open3A/Kunden/Kappendix.class.php");
		registerClassPath("mKappendix", Util::getRootPath()."open3A/Kunden/mKappendix.class.php");
		registerClassPath("mKappendixGUI", Util::getRootPath()."open3A/Kunden/mKappendixGUI.class.php");
		registerClassPath("Auftrag", Util::getRootPath()."open3A/Auftraege/Auftrag.class.php");
		registerClassPath("PDFixUser", Util::getRootPath()."open3A/PDFix/PDFixUser.class.php");
		registerClassPath("PDFixUserDB", Util::getRootPath()."open3A/PDFix/PDFixUserDB.class.php");
		
	}
	
	function getCMSHTML() {
		$this->classes();
		#registerClassPath("Adresse", Util::getRootPath()."open3A/Adressen/Adresse.class.php");
		#registerClassPath("Adresse", Util::getRootPath()."open3A/Adressen/AdresseAdapter.class.php");
		
		if(isset($_GET["activate"]) AND $_GET["activate"] != "")
			return $this->showActivation();
		
		if(isset($_GET["thankR"]))
			return "<h1>Danke</h1>
				<p>Vielen Dank für Ihre Registrierung. Ihre Firma erhält nun eine E-Mail an die eingegebene Adresse und kann Sie mit dem darin enthaltenen Link freischalten.</p>";
		
		if(isset($_GET["thankA"]))
			return "<h1>Danke</h1>
				<p>Vielen Dank für Ihre Aktivierung. Der Benutzer kann nun PDFix verwenden.</p>";
		
		return $this->showRegistration();
		
	}
	
	function showActivation(){
		$PU = anyC::getFirst("PDFixUser", "PDFixUserToken", $_GET["activate"]);
		
		if($PU == null)
			return "<p>Token unbekannt</p>";
		
		if($PU->A("PDFixUserIsActive") == "1")
			return "<p>Benutzer bereits aktiviert</p>";
		
		$Adresse = new Adresse($PU->A("PDFixUserAdresseID"));
		$Kappendix = new Kappendix($PU->A("PDFixUserKappendixID"));
		
		$html = "<script type=\"text/javascript\">
		$(function() {
			$('#activateUser').validate({
				rules: {
					acceptPayment: 'required'
				},
				messages: {
					acceptPayment: 'Sie müssen der Nutzung zustimmen.'
				}
			});
			
			$('#activateUser input[type=text]').css('background-color', 'transparent').css('color', 'grey').attr('disabled', 'disabled');
			
		});
		
		</script>";
		
		$html .= "<h1>Benutzer-Aktivierung für PDFix</h1>";
		
		$F = new HTMLForm("activateUser", array(
			"firma",
			"strasse",
			"nr",
			"plz",
			"ort",
			"email",
			"tel",
			"blz",
			"kontonummer",
			"zahlungsweise", 
			"userVorNachname",
			"acceptPayment",
			"action",
			"token"));
		
		$F->insertSpaceAbove("strasse", "Adresse");
		$F->insertSpaceAbove("email", "Kontakt");
		$F->insertSpaceAbove("blz", "Bank");
		$F->insertSpaceAbove("firma", "<h2>Abrechnungsdaten</h2>");
		$F->insertSpaceAbove("userVorNachname", "<h2 style=\"margin-top:20px;\">Benutzerdaten</h2>");
		$F->insertSpaceAbove("zahlungsweise", "<h2 style=\"margin-top:20px;\">Zahlungsweise</h2>");
		
		$kosten1Monat = Util::CLFormatCurrency(mUserdata::getUDValueS("PDFixKostenProMonat", "0") * 1, true);
		$kosten3Monat = Util::CLFormatCurrency(mUserdata::getUDValueS("PDFixKostenPro3Monat", "0") * 1, true);
		$kosten6Monat = Util::CLFormatCurrency(mUserdata::getUDValueS("PDFixKostenPro6Monat", "0") * 1, true);
		
		$F->setType("action", "hidden");
		$F->setType("token", "hidden");
		$F->setType("acceptPayment", "checkbox");
		$F->setType("zahlungsweise", "select", "1", array("1" => "Monatlich ($kosten1Monat + MwSt)", "3" => "Vierteljährlich ($kosten3Monat + MwSt)", "6" => "Halbjährlich ($kosten6Monat + MwSt)"));
		
		
		$F->setValue("action", "activate");
		
		$F->setLabel("email", "E-Mail");
		$F->setLabel("tel", "Telefon");
		$F->setLabel("strasse", "Straße");
		$F->setLabel("userVorNachname", "Name");
		$F->setLabel("acceptPayment", "Zustimmung");
		
		$F->setValue("firma", $Adresse->A("firma"));
		$F->setValue("strasse", $Adresse->A("strasse"));
		$F->setValue("nr", $Adresse->A("nr"));
		$F->setValue("plz", $Adresse->A("plz"));
		$F->setValue("ort", $Adresse->A("ort"));
		$F->setValue("email", $Adresse->A("email"));
		$F->setValue("tel", $Adresse->A("tel"));
		
		$F->setValue("blz", $Kappendix->A("KappendixBLZ"));
		$F->setValue("kontonummer", $Kappendix->A("KappendixKontonummer"));
		$F->setValue("userVorNachname", $PU->A("PDFixUserVorNachname"));
		$F->setValue("zahlungsweise", $PU->A("PDFixUserRate"));
		$F->setValue("token", $_GET["activate"]);
		
		$F->setDescriptionField("acceptPayment", "Hiermit stimme ich zu, dass dieser Benutzer die Anwendung PDFix für den oben genannten Betrag nutzen darf.");
		
		$F->setSaveCustomerPage("Jetzt aktivieren", null, true, "function(){ document.location.href='./index.php?CC=RegistrationPDFix&thankA=1'; }");
		
		$html .= $F;
		
		return $html;
	}
	
	function showRegistration(){
		$html = "<script type=\"text/javascript\">
		$(function() {
			$('#registrierungAdresse').validate({
				rules: {
					firma: 'required',
					strasse: 'required',
					nr: 'required',
					plz: 'required',
					ort: 'required',
					/*email: {
						required: true,
						email: true
					},*/
					blz: 'required',
					kontonummer: 'required',
					userVorNachname: 'required',
					userEmail: {
						required: true,
						email: true
					},
					userUsername: 'required',
					userPassword: {
						required: true,
						minlength: 5
					},
					confirmUserPassword: {
						required: true,
						minlength: 5,
						equalTo: '#registrierungAdresse input[name=userPassword]'
					}
				},
				messages: {
					firma: 'Bitte geben Sie den Namen Ihrer Firma ein',
					strasse: 'Bitte geben Sie die Straße Ihrer Firma ein',
					nr: 'Bitte geben Sie die Hausnummer Ihrer Firma ein',
					plz: 'Bitte geben Sie Ihre Postleitzahl Ihrer Firma  ein',
					ort: 'Bitte geben Sie Ihren Ort Ihrer Firma ein',
					//email: {required: 'Bitte geben Sie Ihre gültige E-Mail-Adresse ein', email: 'Bitte geben Sie Ihre gültige E-Mail-Adresse ein'},
					blz: 'Bitte geben Sie die Bankleitzahl Ihrer Firma ein',
					kontonummer: 'Bitte geben Sie die Kontonummer Ihrer Firma ein',
					userVorNachname: 'Bitte geben Sie Ihren Vor- und Nachnamen ein',
					userUsername: 'Bitte geben Sie einen Benutzernamen ein',
					userEmail: {required: 'Bitte geben Sie Ihre gültige E-Mail-Adresse ein', email: 'Bitte geben Sie Ihre gültige E-Mail-Adresse ein'},
					confirmUserPassword: {required: 'Bitte geben Sie Ihr Passwort ein', minlength: 'Bitte geben Sie mindestens fünf Zeichen ein', equalTo: 'Die Passwörter stimmen nicht überein'},
					userPassword: {required: 'Bitte geben Sie Ihr Passwort ein', minlength: 'Bitte geben Sie mindestens fünf Zeichen ein'}
				}
			});
			
				
			$('#registrierungAdresse input[name=userUsername]').focus(function() {
				var vorNachname = $('#registrierungAdresse input[name=userVorNachname]').val();
				if(vorNachname && !this.value) {
					this.value = vorNachname.replace(' ', '.').toLowerCase();
				}
			});
		});
		
		</script>";
		
		$html .= "<h1>Registrierung für PDFix</h1>";
		
		
		$F = new HTMLForm("registrierungAdresse", array(
			"firma",
			"strasse",
			"nr", 
			"plz", 
			"ort", 
			#"email", 
			"tel", 
			"blz", 
			"kontonummer", 
			"zahlungsweise", 
			"userVorNachname", 
			"userUsername", 
			"userPassword",
			"confirmUserPassword",
			"userEmail",
			"action"));
		
		#$F->setType("anrede", "select", "3", Adresse::getAnreden());
		
		$F->insertSpaceAbove("vorname");
		$F->insertSpaceAbove("strasse", "Adresse");
		$F->insertSpaceAbove("email", "Kontakt");
		$F->insertSpaceAbove("blz", "Bank");
		$F->insertSpaceAbove("firma", "<h2>Abrechnungsdaten</h2>");
		$F->insertSpaceAbove("userVorNachname", "<h2 style=\"margin-top:20px;\">Benutzerdaten</h2>");
		$F->insertSpaceAbove("zahlungsweise", "<h2 style=\"margin-top:20px;\">Zahlungsweise</h2>");
		
		$F->setSaveCustomerPage("Jetzt registrieren", null, true, "function(){ document.location.href='./index.php?CC=RegistrationPDFix&thankR=1'; }");
		
		$F->setLabel("email", "E-Mail");
		$F->setLabel("tel", "Telefon");
		$F->setLabel("strasse", "Straße");
		$F->setLabel("userVorNachname", "Name");
		$F->setLabel("userUsername", "Benutzername");
		$F->setLabel("userPassword", "Passwort");
		$F->setLabel("confirmUserPassword", "Passwort wiederholen");
		$F->setLabel("userEmail", "E-Mail");
		
		$kosten1Monat = Util::CLFormatCurrency(mUserdata::getUDValueS("PDFixKostenProMonat", "0") * 1, true);
		$kosten3Monat = Util::CLFormatCurrency(mUserdata::getUDValueS("PDFixKostenPro3Monat", "0") * 1, true);
		$kosten6Monat = Util::CLFormatCurrency(mUserdata::getUDValueS("PDFixKostenPro6Monat", "0") * 1, true);
		
		$F->setType("confirmUserPassword", "password");
		$F->setType("userPassword", "password");
		$F->setType("action", "hidden");
		$F->setType("zahlungsweise", "select", "1", array("1" => "Monatlich ($kosten1Monat + MwSt)", "3" => "Vierteljährlich ($kosten3Monat + MwSt)", "6" => "Halbjährlich ($kosten6Monat + MwSt)"));
		
		$F->setValue("action", "register");
		
		$html .= $F;
		
		return $html;
	}
	
	function handleForm($valuesAssocArray){
		$this->classes();
		
		switch($valuesAssocArray["action"]){
			case "register":
				$F = new Factory("Adresse");
				$F->sA("firma", $valuesAssocArray["firma"]);
				$F->sA("strasse", $valuesAssocArray["strasse"]);
				$F->sA("nr", $valuesAssocArray["nr"]);
				$F->sA("plz", $valuesAssocArray["plz"]);
				$F->sA("ort", $valuesAssocArray["ort"]);
				$F->sA("email", $valuesAssocArray["userEmail"]); //from email
				$F->sA("tel", $valuesAssocArray["tel"]);
				$AdresseID = $F->store(false, false);
				
				$K = new Kunden();
				$Kappendix = $K->createKundeToAdresse($AdresseID, false, true);
				
				$Kappendix->changeA("KappendixKontonummer", $valuesAssocArray["kontonummer"]);
				$Kappendix->changeA("KappendixBLZ", $valuesAssocArray["blz"]);
				$Kappendix->changeA("KappendixSameKontoinhaber", "1");
				
				$KappendixID = $Kappendix->newMe(false);
				
				$token = sha1($AdresseID.$KappendixID.microtime());
				
				$F = new Factory("PDFixUser");
				$F->sA("PDFixUserAdresseID", $AdresseID);
				$F->sA("PDFixUserKappendixID", $KappendixID);
				$F->sA("PDFixUserPDFixUserDBID", "0");
				$F->sA("PDFixUserIsActive", "0");
				$F->sA("PDFixUserVorNachname", $valuesAssocArray["userVorNachname"]);
				$F->sA("PDFixUserUsername", $valuesAssocArray["userUsername"]);
				$F->sA("PDFixUserPassword", $valuesAssocArray["userPassword"]);
				$F->sA("PDFixUserEmail", $valuesAssocArray["userEmail"]);
				$F->sA("PDFixUserRate", $valuesAssocArray["zahlungsweise"]);
				$F->sA("PDFixUserRegisteredDate", time());
				$F->sA("PDFixUserToken", $token);
				$F->store(false, false);
				
				$mail = new htmlMimeMail5();
				
				$mail->setFrom("info@pdfix.de");
				$mail->setSubject(utf8_decode("PDFix Registrierung ihres Mitarbeiters ".$valuesAssocArray["userVorNachname"]));
				$mail->setText(utf8_decode("Sehr geehrte Damen und Herren,
 
Sie selbst oder Ihr Mitarbeiter/Ihre Mitarbeiterin 
".$valuesAssocArray["userVorNachname"]." hat sich soeben bei www.pdfix.de angemeldet. 
 
Der Account wurde angelegt.
 
Im Anhang finden Sie unsere AGB und den Softwarelizenzvertrag.
Mit der Aktivierung des Accounts werden die AGB und Nutzungsbedingungen
annerkannt.

Sobald die Abbuchungserklärung bei uns eingegangen ist,
wird der Account innerhalb 24 Std. freigeschalten und
\"pdfix\" kann dann genutzt werden.
 
Wenn Sie die Kosten übernehmen, dann klicken Sie auf


nachstehenden Aktivierungslink.

http://".$_SERVER["HTTP_HOST"]."".str_replace("/ubiquitous/CustomerPage/index.php", "/ubiquitous/CustomerPage/?CC=RegistrationPDFix&activate=$token", $_SERVER["SCRIPT_NAME"])."
 

Ansonsten klären Sie bitte die Kostenübernahme mit Ihrem Mitarbeiter ab.

 
Mit freundlichen Grüßen
 
M. Tischler
Geschäftsführer
ETS-SÜD UG (haftungsbeschränkt)
DR. Michael Samer Ring 2a
86609 Donauwörth
AG Augsburg
HRB 26204
info@pdfix.de"));
				
/*
Zum Download von pdfix besuchen Sie bitte
http://www.meine-smn.de und klicken auf \"Login\"
 
melden Sie sich an mit:
 
Benutzername: ".$valuesAssocArray["userUsername"]."
Passwort: ".$valuesAssocArray["userPassword"]."
 
Bestätigen Sie jeweils am Ende der Seiten die AGB´s und Lizenzbedingungen und
laden \"pdfix\" down.
 
Sobald die Abbuchungserklärung bei uns eingegangen ist,
wird der Account innerhalb 24 Std. freigeschalten,und
\"pdfix\" kann dann genutzt werden.
 */
				#$mail->setTextCharset("UTF-8");
				
				#$mail->addAttachment(new fileAttachment(Util::getRootPath()."open3A/PDFix/abbuchungserkl.pdf", "application/pdf"));
				$mail->addAttachment(new fileAttachment(Util::getRootPath()."../download/Agbs.pdf", "application/pdf"));
				$mail->addAttachment(new fileAttachment(Util::getRootPath()."../download/Softwarelizenzvertrag.pdf", "application/pdf"));
				
				if(!$mail->send(array($valuesAssocArray["userEmail"]))) //from email
					Red::errorD("Die E-Mail konnte nicht verschickt werden, bitte versuchen Sie es erneut!");
				
			break;
			
			case "activate":
				$PU = anyC::getFirst("PDFixUser", "PDFixUserToken", $valuesAssocArray["token"]);
				
				$PU->changeA("PDFixUserActivatedDate", time());
				$PU->changeA("PDFixUserIsActive", "1");
				$PU->changeA("PDFixUserRate",  $valuesAssocArray["zahlungsweise"]);
				
				$PU->saveMe();
				
				$mail = new htmlMimeMail5();
				
				$mail->setFrom("info@pdfix.de");
				$mail->setSubject(utf8_decode("PDFix Registrierung ihres Mitarbeiters ".$valuesAssocArray["userVorNachname"]));
				$mail->setText(utf8_decode("Sehr geehrte Damen und Herren,
 
Ihr Account wurde soeben angelegt.
 
Benutzername: ".$PU->A("PDFixUserUsername")."
Passwort: ".$PU->A("PDFixUserPassword")."
 
Sobald die Abbuchungserklärung bei uns eingegangen ist,
wird der Account innerhalb 24 Std. freigeschalten und
\"pdfix\" kann dann genutzt werden.
	

Mit freundlichen Grüßen
 
M. Tischler
Geschäftsführer
ETS-SÜD UG (haftungsbeschränkt)
DR. Michael Samer Ring 2a
86609 Donauwörth
AG Augsburg
HRB 26204
info@pdfix.de"));
				
/*
Zum Download von pdfix besuchen Sie bitte
http://www.meine-smn.de und klicken auf \"Login\"
 
melden Sie sich an mit:
 
Bestätigen Sie jeweils am Ende der Seiten die AGB´s und Lizenzbedingungen und
laden \"pdfix\" down.
 */
				#$mail->setTextCharset("UTF-8");
				
				if(!$mail->send(array($PU->A("PDFixUserEmail"))))
					Red::errorD("Die E-Mail konnte nicht verschickt werden, bitte versuchen Sie es erneut!");
				
				$Adresse = new Adresse($PU->A("PDFixUserAdresseID"));
				
				$mail = new htmlMimeMail5();
				
				$mail->setFrom("info@pdfix.de");
				$mail->setSubject("PDFix Aktivierung");
				$mail->setText(utf8_decode("
Firma: ".$Adresse->A("firma")."
Benutzername: ".$PU->A("PDFixUserUsername").""));
				#$mail->setTextCharset("UTF-8");
				
				if(!$mail->send(array("info@pdfix.de")))
					Red::errorD("Die E-Mail konnte nicht verschickt werden, bitte versuchen Sie es erneut!");
				
			break;
		}
	}
}
?>