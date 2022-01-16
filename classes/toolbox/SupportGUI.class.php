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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
class SupportGUI {
	
	public static $errorMessage;
	
	public static function mailAddress(){
		return array("open3A GmbH", "Support@Furtmeier.IT");
	}
	
	public function getID(){
		return -1;
	}
	
	public function fatalError($error, $request = "", $return = false, $inWindow = false){
		
		$error = trim($error."\n\nZeit:\n".Util::CLDateTimeParser(time())."\n\nBrowser-Anfrage:\n".htmlentities($request));
		
		$B = new Button("Fehler", ($inWindow ? "." : "")."./images/big/sad.png", "icon");
		$B->style("float:left;margin-right:10px;margin-bottom:20px;");
		
		$r = "<div id=\"questionContainer\"><h1>$B Es ist ein Fehler aufgetreten, das tut mir leid!</h1><p>Sie können mit diesem Modul nicht arbeiten,<br />möglicherweise funktionieren aber die Anderen.</p>";
		$r .= "<pre style=\"padding:5px;font-size:10px;max-width:590px;overflow:auto;clear:both;color:grey;\">".preg_replace("/^\<br[ \/]*\>\s*/", "", stripslashes(trim($error)))."</pre>";
		
		$BJa = new Button("Ja", ($inWindow ? "." : "")."./images/navi/bestaetigung.png");
		$BJa->style("float:right;margin:10px;");
		$BJa->onclick("\$j('#questionContainer').slideUp(); \$j('#mailContainer').slideDown();");
		
		$BNein = new Button("Nein,\ndanke", ($inWindow ? "." : "")."./images/navi/stop.png");
		$BNein->style("margin:10px;");
		if(!$inWindow)
			$BNein->onclick(OnEvent::closePopup("Support"));
		else
			$BNein->onclick("window.close();");
		
		$r .= "<h2>Möchten Sie die Fehlermeldung an den Support übertragen?</h2>$BJa$BNein</div>";
		
		SupportGUI::$errorMessage = stripslashes(strip_tags($error));
		$r .= "<div style=\"display:none;\" id=\"mailContainer\">".UtilGUI::EMailPopup("SupportGUI", "-1", $inWindow ? "1" :"0", "function(transport){ \$j('#messageContainer').html(transport.responseText); \$j('#mailContainer').slideUp(); \$j('#messageContainer').slideDown(); }", !$inWindow ? OnEvent::closePopup("Support") : "window.close();", true, true)."</div><div style=\"display:none;\" id=\"messageContainer\"></div>";
		
		if($return)
			return $r;
		
		echo $r;
	}
	
	
	public static function sendEmail($subject, $body, $recipient, $inWindow, $files, $cc, $sender){
		$S = new SupportGUI();
		$data = $S->getEMailData();
		
		$mailfrom = $data["fromAddress"];
		if(trim($mailfrom) == "" AND $sender == "")
			Red::errorD("Bitte geben Sie eine Absender-Adresse ein!");
		
		if(trim($mailfrom) == "" AND $sender != "")
			$mailfrom = $sender;
		
		$mailto = $data["recipients"][$recipient][1];
		
		$CH = Util::getCloudHost();
		$skipOwn = false;
		if($CH)
			$skipOwn = true;
		
		$mimeMail2 = new PHPMailer(false, substr($mailfrom, stripos($mailfrom, "@") + 1), $skipOwn);
		$mimeMail2->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		$mimeMail2->CharSet = "UTF-8";
		$mimeMail2->Subject = $subject;
		
		$mimeMail2->From = $mailfrom;
		$mimeMail2->Sender = $mailfrom;
		$mimeMail2->FromName = $data["fromName"];

		$mimeMail2->Body = wordwrap($body, 80);
		$mimeMail2->AddAddress($mailto);
		
		$B = new Button("Danke!", ($inWindow ? "." : "")."./images/big/thanks.png", "icon");
		$B->style("float:left;margin-right:10px;margin-bottom:20px;");
		
		$BOK = new Button("OK", ($inWindow ? "." : "")."./images/navi/bestaetigung.png");
		if(!$inWindow)
			$BOK->onclick(OnEvent::closePopup("Support"));
		else
			$BOK->onclick ("window.close();");
		$BOK->style("float:right;margin:10px;");

		if($mimeMail2->Send())
			echo "<h1>$B Danke für Ihre Unterstützung!</h1><p>Sie erhalten in Kürze eine Antwort per E-Mail.</p>$BOK<div style=\"clear:both;\"></div>";
		else
			echo "<p style=\"padding:5px;color:red;\">Fehler beim Senden der E-Mail. Bitte überprüfen Sie Ihre Server-Einstellungen im Admin-Bereich.</p><p>Nachfolgend wird Ihre Nachricht angezeigt, falls Sie sie in die Zwischenablage kopieren (Strg + c) und manuell an <b>$mailto</b> möchten.</p><pre style=\"color:grey;max-height:300px;font-size:10px;padding:5px;width:590px;overflow:auto;\">".wordwrap(stripslashes($body), 80)."</pre>$BOK<div style=\"clear:both;\"></div>";
	}
	
	public function getEMailData(){
		return array(
			"fromName" => Session::currentUser()->A("name"),
			"fromAddress" => Session::currentUser()->A("UserEmail"),
			"recipients" => array(self::mailAddress()),
			"subject" => "Fehlermeldung in ".Applications::activeApplication()." ".Applications::activeVersion(),
			"body" => "Hallo Support-Team,

bei der Nutzung von ".Applications::activeApplication()." ".Applications::activeVersion()." ist ein Fehler aufgetreten, während ich folgendes getan habe:





Weiter unten sende ich die Fehlermeldung sowie einige Informationen, die Ihnen beim Finden des Problems helfen könnten.

Freundliche Grüße,
".Session::currentUser()->A("name")."


".self::$errorMessage."

URL:
".str_replace("interface/rme.php", "", $_SERVER["SCRIPT_URI"])."

PHP-Version:
". phpversion()."

Alle geladenen Module:
".implode(", ", get_loaded_extensions())."

Mein Browser:
".$_SERVER["HTTP_USER_AGENT"]."

Mein Server:
".$_SERVER["SERVER_SOFTWARE"]);
	
	}
}
?>