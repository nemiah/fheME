<?php
/**
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
class LoginDataGUI extends LoginData implements iGUIHTML2 {
	function getHTML($id){
		$gui = $this->getGUI($id);

		#$gui->setStandardSaveButton($this,"mLoginData");

		return $gui->getEditHTML();
	}

	private function getGUI($id){
		try {
		$this->loadMeOrEmpty();
		} catch (StorageException $e){
			die("<p>Bitte legen Sie zuerst die Datenbank-Tabellen an.</p>");
		}
		if($id == -1)
			$this->A->typ = "LoginData";

		$gui = new HTMLGUIX($this);
		$gui->name("LoginData");

		$gui->label("UserID", "Benutzer");
		$gui->label("name", "Typ");
		$gui->label("passwort", "Passwort");
		$gui->label("optionen", "Optionen");
		$gui->label("benutzername", "Benutzername");
		$gui->label("server", "Server");

		$gui->type("typ", "hidden");
		$gui->type("wert", "hidden");
		$gui->type("passwort", "password");



		$onkeyup = "$('editLoginDataGUI').wert.value = $('editLoginDataGUI').benutzername.value+'::::'+$('editLoginDataGUI').passwort.value+($('editLoginDataGUI').server.value != '' ? '::::s:'+$('editLoginDataGUI').server.value : '')+($('editLoginDataGUI').optionen.value != '' ? '::::o:'+$('editLoginDataGUI').optionen.value : '')";
		$gui->addFieldEvent("benutzername", "onKeyup", $onkeyup);
		$gui->addFieldEvent("server", "onKeyup", $onkeyup);
		$gui->addFieldEvent("passwort", "onKeyup", $onkeyup);
		$gui->addFieldEvent("optionen", "onKeyup", $onkeyup);


		$U = new Users();
		$U->addAssocV3("isAdmin", "=", "0");

		$Users = array();
		$Users[-1] = "alle Benutzer";
		while($t = $U->getNextEntry())
			$Users[$t->getID()] = $t->A("name");

		$gui->type("UserID", "select", $Users);

		$dataTypes = LoginData::getNames();
		$gui->type("name", "select", $dataTypes);

		return $gui;
	}

	public function getPopup(){
		$bps = $this->getMyBPSData();
		
		$gui = $this->getGUI($this->getID());

		$onSave = "Popup.close('LoginData', 'edit');";
		

		#$gui->setJSEvent("onSave", "function() { Popup.close('', 'mailServer'); contentManager.reloadFrame('contentRight'); }");

		#$gui->setStandardSaveButton($this,"mLoginData");

		$gui->displayMode("popup");

		$html = "";
		$html2 = "";
		if($bps != -1 AND isset($bps["preset"]) AND $bps["preset"] == "mailServer"){
			$BAbort = new Button("Abbrechen", "stop");
			$BAbort->onclick("Popup.close('LoginData', 'edit');");
			$BAbort->style("float:right;");
			
			$html = "<p style=\"padding:5px;\">{$BAbort}<small>Sie müssen hier nur Einstellungen vornehmen, wenn Sie diese Anwendung lokal auf einem Windows-Rechner betreiben oder direkt über einen SMTP-Server versenden möchten (z.B. Newsletter). Es kann auch notwendig sein, die E-Mail über den korrekten Server zu schicken, um die Ankunft beim Empfänger sicherzustellen.</small></p>";

			$gui->type("UserID", "hidden");
			$this->changeA("UserID", "-1");

			$gui->type("name", "hidden");
			$this->changeA("name", "MailServerUserPass");

			$gui->type("optionen", "hidden");
		}
		
		if($bps != -1 AND isset($bps["preset"]) AND $bps["preset"] == "jabberServer"){
			$BAbort = new Button("Abbrechen", "stop");
			$BAbort->onclick("Popup.close('LoginData', 'edit');");
			$BAbort->style("float:right;");
			
			#$html = "<p style=\"padding:5px;\">{$BAbort}<small>Sie müssen hier nur Einstellungen vornehmen, wenn Sie diese Anwendung lokal auf einem Windows-Rechner betreiben oder direkt über einen SMTP-Server versenden möchten (z.B. Newsletter). Es kann auch notwendig sein, die E-Mail über den korrekten Server zu schicken, um die Ankunft beim Empfänger sicherzustellen.</small></p>";

			$gui->type("UserID", "hidden");
			$this->changeA("UserID", "-1");

			$gui->type("name", "hidden");
			$this->changeA("name", "JabberServerUserPass");

			$gui->type("optionen", "hidden");
		}
		
		if($bps != -1 AND isset($bps["preset"]) AND strpos($bps["preset"], "mailServerAdditional") !== false){
			$Nr = str_replace("mailServerAdditional", "", $bps["preset"]);
			
			$BAbort = new Button("Abbrechen", "stop");
			$BAbort->onclick("Popup.close('LoginData', 'edit');");
			$BAbort->style("float:right;");
			
			$html = "<p style=\"padding:5px;\">{$BAbort}<small>Sie müssen hier nur Einstellungen vornehmen, wenn eine Absender-Domain nicht über den Standard-Mailserver verschickt werden kann.</small></p>";

			$gui->type("UserID", "hidden");
			$this->changeA("UserID", "-1");

			$gui->type("name", "hidden");
			$this->changeA("name", "MailServer{$Nr}UserPass");

			$gui->label("optionen", "Absender-Domain");
			
			$onSave .= OnEvent::reloadPopup("mInstallation");
			
			#$gui->type("optionen", "hidden");
		}
		
		
		if($bps != -1 AND isset($bps["preset"]) AND ($bps["preset"] == "remoteMailServer1" OR $bps["preset"] == "remoteMailServer2" OR $bps["preset"] == "remoteMailServer3")){
			$gui->type("UserID", "hidden");
			$this->changeA("UserID", "-1");

			$gui->type("name", "hidden");
			$this->changeA("name", "RemoteMailServer".substr($bps["preset"], -1)."APIKey");

			$gui->type("optionen", "hidden");
			$gui->type("passwort", "hidden");
			
			$gui->label("benutzername", "API key");
			$gui->descriptionField("server", "Der Pfad zur openMM-Installation inklusive Protokollangabe. Zum Beispiel https://www.meinMailserver.de/openMM");
			$onSave .= OnEvent::reloadPopup("mVUser");
		}
		
		if($bps != -1 AND isset($bps["preset"]) AND $bps["preset"] == "backupFTPServer"){
			$BAbort = new Button("Abbrechen", "stop");
			$BAbort->onclick("Popup.close('LoginData', 'edit');");
			$BAbort->style("float:right;");
			
			$html = "<p style=\"padding:5px;\">{$BAbort}<small>Sie müssen hier nur Einstellungen vornehmen, wenn Sie die Backups automatisch auf einen FTP-Server hochladen möchten.</small></p>";

			$gui->type("UserID", "hidden");
			$this->changeA("UserID", "-1");

			$gui->type("name", "hidden");
			$this->changeA("name", "BackupFTPServerUserPass");

			$gui->descriptionField("optionen", "Bitte geben Sie hier das Unterverzeichnis an, in das die Datei hochgeladen werden soll");
			$gui->label("optionen", "Verzeichnis");
			#$gui->type("optionen", "hidden");
		}

		if($bps != -1 AND isset($bps["preset"]) AND ($bps["preset"] == "googleData" OR $bps["preset"] == "GoogleAccountUserPass")){

			$html = "<p>Bitte beachten Sie: Es werden nur Ihre eigenen Termine synchronisiert.</p>";
			
			$gui->type("UserID", "hidden");
			$this->changeA("UserID", Session::currentUser()->getID());

			$gui->type("name", "hidden");
			$this->changeA("name", "GoogleAccountUserPass");
			$gui->type("optionen", "hidden");
			$gui->type("server", "hidden");
		}
		
		if($bps != -1 AND isset($bps["preset"]) AND $bps["preset"] == "klickTelAPIKey"){

			$html = "";
			
			$gui->type("UserID", "hidden");
			$this->changeA("UserID", "-1");
			$gui->label("benutzername", "API-Key");
			$gui->type("name", "hidden");
			$this->changeA("name", "klickTelAPIKey");
			$gui->type("optionen", "hidden");
			$gui->type("server", "hidden");
			$gui->type("passwort", "hidden");
		}

		#$gui->label("benutzername", "Benutzername");
		$gui->label("passwort", "Passwort");
		$gui->label("server", "Server");
		
		$gui->addToEvent("onSave", $onSave);
		
		echo $html.$gui->getEditHTML();
	}
}
?>