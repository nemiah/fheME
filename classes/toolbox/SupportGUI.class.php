<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class SupportGUI {
	
	public static $errorMessage;
	
	public static function mailAddress(){
		return array("Furtmeier Hard- und Software", "Support@Furtmeier.IT");
	}
	
	public function fatalError($error){
		$B = new Button("Fehler", "./images/big/sad.png", "icon");
		$B->style("float:left;margin-right:10px;margin-bottom:20px;");
		
		echo "<div id=\"questionContainer\"><h1>$B Es ist ein Fehler aufgetreten, das tut mir leid!</h1><p>Sie können mit diesem Modul nicht arbeiten,<br />möglicherweise funktionieren aber die anderen.</p>";
		echo "<pre style=\"padding:5px;font-size:10px;max-width:590px;overflow:auto;clear:both;color:grey;\">".preg_replace("/^\<br[ \/]*\>\s*/", "", stripslashes(trim($error)))."</pre>";
		
		$BJa = new Button("Ja", "bestaetigung");
		$BJa->style("float:right;margin:10px;");
		$BJa->onclick("\$j('#questionContainer').slideUp(); \$j('#mailContainer').slideDown();");
		
		$BNein = new Button("Nein,\ndanke", "stop");
		$BNein->style("margin:10px;");
		$BNein->onclick(OnEvent::closePopup("Support"));
		
		echo "<h2>Möchten Sie die Fehlermeldung an den Support übertragen?</h2>$BJa$BNein</div>";
		
		SupportGUI::$errorMessage = stripslashes(strip_tags($error));
		echo "<div style=\"display:none;\" id=\"mailContainer\">".UtilGUI::EMailPopup("SupportGUI", "-1", null, "function(transport){ \$j('#messageContainer').html(transport.responseText); \$j('#mailContainer').slideUp(); \$j('#messageContainer').slideDown(); }", OnEvent::closePopup("Support"), true)."</div><div style=\"display:none;\" id=\"messageContainer\"></div>";
	}
	
	public static function sendEmail($subject, $body, $recipient){
		$S = new SupportGUI();
		$data = $S->getEMailData();
		
		$mailfrom = $data["fromAddress"];
		$mailto = $data["recipients"][$recipient][1];
		
		$mimeMail2 = new PHPMailer(false, substr($mailfrom, stripos($mailfrom, "@") + 1));
		$mimeMail2->CharSet = "UTF-8";
		$mimeMail2->Subject = $subject;
		
		$mimeMail2->From = $mailfrom;
		$mimeMail2->Sender = $mailfrom;
		$mimeMail2->FromName = $data["fromName"];
		
		$mimeMail2->Body = wordwrap($body, 80);
		$mimeMail2->AddAddress($mailto);
		
		$B = new Button("Danke!", "./images/big/thanks.png", "icon");
		$B->style("float:left;margin-right:10px;margin-bottom:20px;");
		
		$BOK = new Button("OK", "bestaetigung");
		$BOK->onclick(OnEvent::closePopup("Support"));
		$BOK->style("float:right;margin:10px;");
		
		#if($mimeMail2->Send())
		#	echo "<h1>$B Danke für Ihre Unterstützung!</h1><p>Sie erhalten in Kürze eine Antwort per E-Mail.</p>$BOK<div style=\"clear:both;\"></div>";
		#else
			echo "<p style=\"padding:5px;color:red;\">Fehler beim Senden der E-Mail. Bitte überprüfen Sie Ihre Server-Einstellungen im Admin-Bereich.</p><p>Nachfolgend wird Ihre Nachricht angezeigt, falls Sie sie in die Zwischenablage kopieren (Strg + c) und manuell an <b>$mailto</b> möchten.</p><pre style=\"color:grey;max-height:300px;font-size:10px;padding:5px;width:590px;overflow:auto;\">".wordwrap(stripslashes($body), 80)."</pre>$BOK<div style=\"clear:both;\"></div>";
	}
	
	public function getEMailData(){
		return array(
			"fromName" => Session::currentUser()->A("name"),
			"fromAddress" => Session::currentUser()->A("UserEmail"),
			"recipients" => array(self::mailAddress()),
			"subject" => "Fehlermeldung in ".Applications::activeApplication()." ".Applications::activeVersion(),
			"body" => "Sehr geehrter Herr Furtmeier,

bei der Nutzung von ".Applications::activeApplication()." ist ein Fehler aufgetreten, während ich folgendes getan habe:





Weiter unten sende ich die Fehlermeldung sowie einige Informationen, die Ihnen beim finden des Problems helfen könnten.

Freundliche Grüße,
".Session::currentUser()->A("name")."


".self::$errorMessage."

Alle geladenen Module:
".implode(", ", get_loaded_extensions())."

Mein Browser:
".$_SERVER["HTTP_USER_AGENT"]."

Mein Server:
".$_SERVER["SERVER_SOFTWARE"]);
	
	}
}
?>