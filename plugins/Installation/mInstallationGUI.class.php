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
class mInstallationGUI extends mInstallation implements iGUIHTML2 {

	/*function updateAllTables(){
		parent::updateAllTables();
	}*/
	
	function getHTML($id){
		#$showHelp = true;

		if($this->collector == null) $this->lCV3($id);
		$singularLanguageClass = $this->loadLanguageClass("Installation");
		$text = $singularLanguageClass != null ? $singularLanguageClass->getText() : "";
		
		
		if($id == -1)
			echo OnEvent::script(OnEvent::rme($this, "getActions", "", "function(transport){ contentManager.contentBelow(transport.responseText); }"));
		
		
		
		$hasDBConnection = false;
		try {
			mUserdata::getGlobalSettingValue("DBVersion", false);
			$hasDBConnection = true;
		} catch(Exception $e){}
		
		$g = "";
		$DBFilePath = Util::getRootPath()."system/DBData/Installation.pfdb.php";
		$writable = new HTMLTable(1);
		$File = new File($DBFilePath);
		$File->loadMe();
		if(!$File->A("FileIsWritable") AND !$hasDBConnection)
			return;
		
		if(!$File->A("FileIsWritable")){
			$writable->addRow("<img src=\"./images/navi/restrictions.png\" style=\"float:left;margin-right:10px;\"/>Die Datei ".$DBFilePath." ist nicht beschreibbar, Änderungen können nicht gespeichert werden.<br /><br />Machen Sie die Datei mit einem FTP-Programm beschreibbar. Klicken Sie dazu mit der rechten Maustaste auf die Datei auf dem Server, wählen Sie \"Eigenschaften\", und geben Sie den Modus 666 an, damit sie durch den Besitzer, die Gruppe und alle Anderen les- und schreibbar ist.");
			$g .= $writable->getHTML();
		}
		
		$gui = new HTMLGUI();
		$gui->setName("Datenbank-Zugangsdaten");
		if($this->collector != null) $gui->setAttributes($this->collector);
		
		$gui->setCollectionOf($this->collectionOf,"Datenbank-Zugangsdaten");
		$gui->hideAttribute("password");
		$gui->hideAttribute("httpHost");
		$gui->hideAttribute("InstallationID");

		if(strstr($_SERVER["SCRIPT_FILENAME"],"demo")) {
			$UA = $_SESSION["S"]->getCurrentUser()->getA();
			if($UA->name != "Installations-Benutzer"){
				$g = "In der Demo können keine Datenbank-Zugangsdaten geändert werden!";
				$gui->setIsDisplayMode(true);
			}
		}

		if(!Session::isPluginLoaded("multiInstall")){
			$gui->setIsDisplayMode(true);
			$gui->setEditInDisplayMode(true, "contentLeft");
		}
		#try {
			$g .= $gui->getBrowserHTML($id);
		#} catch (Exception $e){
		#	$t->addRow(array("Etwas stimmt nicht, eine ".get_class($e)." wurde abgefangen!"));
		#	$t->addRow(array("<span style=\"font-size:8px;\">".nl2br(str_replace("#","\n#", $e->getTraceAsString()))."</span>"));
		#}

		/*	$help = "
	<script type=\"text/javascript\">
		contentManager.rmePCR('mInstallation','','getHelp','true','if(checkResponse(transport)) { Popup.create(\'123\', \'Installation\', \'Hilfe\'); Popup.update(transport, \'123\', \'Installation\'); }');
	</script>";*/

		$ST = new HTMLSideTable("left");
		
		try {
			#$MailServer = LoginData::get("MailServerUserPass");

			#$MailServerID = $MailServer == null ? -1 : $MailServer->getID();
			$BMail = $ST->addButton("Mail-Server", "./plugins/Installation/serverMail.png");
			#$BMail->popup("edit", "Mail-Server", "LoginData", $MailServerID, "getPopup", "", "LoginDataGUI;preset:mailServer");
			$BMail->popup("edit", "Mail-Server", "mInstallation", -1, "manageMailservers");

			$BTestMail = $ST->addButton("Mailversand\ntesten", "mail");
			$BTestMail->popup("mailTest", "Mailversand testen", "mInstallation", "-1", "testMailGUI");
			
			if(Session::isPluginLoaded("mJabber")){
				$JabberServer = LoginData::get("JabberServerUserPass");
				$JabberServerID = $JabberServer == null ? -1 : $JabberServer->getID();
				
				$BJabber = $ST->addButton("Jabber-Server", "./plugins/Installation/serverMail.png");
				$BJabber->popup("edit", "Jabber-Server", "LoginData", $JabberServerID, "getPopup", "", "LoginDataGUI;preset:jabberServer");
			}
			
			$BackupButton = $ST->addButton("Daten-\nsicherungen","disk");
			$BackupButton->onclick("contentManager.loadFrame('contentLeft','BackupManager');");
		} catch(Exception $e){}
		
		return $ST.$g;#.$t->getHTML();
			
			
		$t = new HTMLTable(1);
		try {
			$user = new User(1);
			$user->loadMe();
		}
		catch (DatabaseNotSelectedException $e) {
			if(BPS::getProperty("mInstallationGUI", "showErrorText", false)){
				$t->addRow(isset($text["noDatabase"]) ? $text["noDatabase"] : "Es wurde kein korrekter Datenbankname angegeben.<br /><br />Bitte geben Sie eine existierende Datenbank an, sie wird nicht automatisch erzeugt.");
				$t->addRowClass("backgroundColor0");
				$t->addRowStyle("color:red;");
			}
			
			return $g.$t->getHTML();#.$help;
		}
		catch (NoDBUserDataException $e) { 
			if(BPS::getProperty("mInstallationGUI", "showErrorText", false)){
				$t->addRow(isset($text["wrongData"]) ? $text["wrongData"] : "Mit den angegebenen Datenbank-Zugangsdaten kann keine Verbindung aufgebaut werden.<br /><br />Wenn sie korrekt sind, werden hier weitere Möglichkeiten angezeigt angezeigt.");
				$t->addRowClass("backgroundColor0");
				$t->addRowStyle("color:red;");
			}
			
			if(PHYNX_MAIN_STORAGE == "MySQL") {
				try {
					$DB1 = new DBStorageU();
					
					$B = new Button("Hinweis", "notice", "icon");
					$B->style("float:left;margin-right:10px;");
					
					$File = new File(Util::getRootPath()."system/connect.php");
					
					$BR = new Button("DB-Verbindung\numstellen", "lieferschein");
					$BR->style("float:right;margin-left:10px;");
					$BR->rmePCR("mInstallation", "-1", "switchDBToMySQLo", "", "Installation.reloadApp();");
					
					$BR = "Verwenden Sie den nebenstehenden Knopf, um die Verbindungsart auf die ältere Version umzustellen.<br />$BR Sie müssen sich anschließend erneut anmelden.";
					
					$BReload = new Button("Ansicht\naktualisieren","refresh");
					$BReload->onclick("contentManager.emptyFrame('contentLeft'); contentManager.loadFrame('contentRight', 'mInstallation', -1, 0, 'mInstallationGUI;-');Popup.closeNonPersistent();");
					$BReload->style("float:right;margin:10px;");
					
					if(!$File->A("FileIsWritable"))
						$BR = "Bitte machen Sie die Datei /system/connect.php für den Webserver beschreibbar, damit phynx auf die ältere Verbindungsart umstellen kann.<br /><br />Verwenden Sie dazu Ihr FTP-Programm. Klicken Sie mit der rechten Maustaste auf die Datei auf dem Server, wählen Sie \"Eigenschaften\", und geben Sie den Modus 666 an, damit sie durch den Besitzer, die Gruppe und alle Anderen les- und schreibbar ist.$BReload";
					$t->addRow(array("$B <b>Möglicherweise ist die MySQLi-Erweiterung auf Ihrem Server nicht korrekt konfiguriert.</b><br /><br />$BR"));
					$t->addRowClass("backgroundColor0");
					
				} catch (Exception $e){
					#echo "MySQL geht auch nicht!";
				}
			}
			
			return $g.$t->getHTML();#.$help;
		}
		
		catch (TableDoesNotExistException $e) {}
		catch (StorageException $e) {}

			/*$help = "
	<script type=\"text/javascript\">
		rme('mInstallation','','getHelp','false','if(checkResponse(transport)) { Popup.create(\'123\', \'Installation\', \'Hilfe\'); Popup.update(transport, \'123\', \'Installation\'); }');
	</script>";*/

			
		/*if(false AND $id == -1) {
			$BackupTab = new HTMLTable(1);

			$BackupButton = new Button("Backup-\nManager","disk");
			$BackupButton->style("float:right;");
			$BackupButton->onclick("contentManager.loadFrame('contentLeft','BackupManager');");

			$BackupTab->addRow($BackupButton);

			$BUT = new Button((isset($text["alle Tabellen aktualisieren"]) ? $text["alle Tabellen aktualisieren"] : "alle Tabellen\naktualisieren"), "update");
			$BUT->rmePCR("mInstallation", "", "updateAllTables", "", "$('contentLeft').update(transport.responseText);");

			$g .= "
	<div style=\"height:30px;\"></div>
	$BackupTab
	<div class=\"Tab backgroundColor1\"><p>Plugins</p></div>
	<table>
		<colgroup>
			<col style=\"width:100px;\" class=\"backgroundColor2\" />
			<col class=\"backgroundColor3\" />
		</colgroup>
		<tr>
			<td colspan=\"3\">
				<span style=\"float:right;\">".Installation::getReloadButton()."</span>
				$BUT
			</td>
		</tr>
		<tr>
			<td style=\"background-color:white;\"></td>
		</tr>";

			$p = array_flip($_SESSION["CurrentAppPlugins"]->getAllPlugins());
			
			
			foreach($p as $key => $value){
				try {
					if(method_exists($_SESSION["CurrentAppPlugins"], "isPluginGeneric") AND $_SESSION["CurrentAppPlugins"]->isPluginGeneric($key)){
						$c = new mGenericGUI('', $key);
					} else {
						$c = new $key();
					}
				} catch (ClassNotFoundException $e){
					$key2 = $key."GUI";
					
					try {
						$c = new $key2();
					} catch (ClassNotFoundException $e2){
						continue;
					}
				}
				if($key == "CIs") continue;
				

				if($c->checkIfMyTableExists() AND $c->checkIfMyDBFileExists()) $showHelp = false;

				if(!$c->checkIfMyDBFileExists())
					continue;

				$g .= "
		<tr>
			<td style=\"font-weight:bold;text-align:right;\">".($value != -1 ? $value : $key )."</td>
			<td>".(!$c->checkIfMyTableExists() ? ($c->checkIfMyDBFileExists() ? "<input type=\"button\" value=\"".(isset($text["Tabelle anlegen"]) ? $text["Tabelle anlegen"] : "Tabelle anlegen")."\" onclick=\"installTable('$key');\" />" : "keine DB-Info-Datei" ) : ($c->checkIfMyDBFileExists() ? "<input type=\"button\" onclick=\"checkFields('$key');\" value=\"Tabellenupdate\" style=\"float:right;width:140px;\" />".(isset($text["Tabelle existiert"]) ? $text["Tabelle existiert"] : "Tabelle existiert") : (isset($text["keine DB-Info-Datei"]) ? $text["keine DB-Info-Datei"] : "keine DB-Info-Datei"))."")."</td>
		</tr>";
			}

			$g .= "
	</table>";
		}*/
		#$showHelp = false;
		#if(!$showHelp)
		#	$help = OnEvent::script(OnEvent::closePopup("123", "Installation"));


		return $ST.$g;#.$help;
	}
	
	public function manageMailservers(){
		$MailServer = LoginData::get("MailServerUserPass");

		$MailServerID = $MailServer == null ? -1 : $MailServer->getID();
		$BMail = new Button("Standard", "./plugins/Installation/serverMail.png");
		$BMail->popup("edit", "Mail-Server", "LoginData", $MailServerID, "getPopup", "", "LoginDataGUI;preset:mailServer");
		$BMail->style("margin:10px;float:left;");
		
		echo $BMail."<p><small style=\"color:grey;\">Über diesen Server werden alle Mails verschickt, wenn weiter unten kein eigener Server für eine Absender-Domain eingetragen wird.</small></p>";
		
		echo "<div style=\"clear:both;height:30px;\"></div><div class=\"Tab backgroundColor1\"><p>Weitere Mailserver</p></div>";
			
		echo "<p><small style=\"color:grey;\">Erfassen Sie einen zusätzlichen Server für eine bestimmte Absender-Domain, wenn der Standard-Server diese nicht verschicken kann.</small></p>";
		echo "<p><small style=\"color:grey;\">Ein Beispiel: Sie möchten E-Mails verschicken von den Adressen max.mustermann@<strong>gmx.de</strong> und erika.hatnachname@<strong>web.de</strong>. Über GMX können Sie die web.de-Mails nicht verschicken und web.de verschickt die GMX-E-Mails ebenfalls nicht. Sie müssen daher für jeden Anbieter seinen eigenen Server eintragen.</small></p>";
		
		$MailServer = 1;
		for($i = 2; $i <= 5; $i++){
			if($MailServer == null)
				break;
			
			$MailServer = LoginData::get("MailServer{$i}UserPass");

			$MailServerID = $MailServer == null ? -1 : $MailServer->getID();
			$BMail = new Button("Server $i", "./plugins/Installation/serverMail.png");
			$BMail->popup("edit", "Mail-Server", "LoginData", $MailServerID, "getPopup", "", "LoginDataGUI;preset:mailServerAdditional$i");
			$BMail->style("margin:10px;display:inline-block;");

			echo $BMail;
			
			if($MailServer != null)
				echo $MailServer->A("optionen");
			
			echo "<br />";

		}
		
		echo "<div style=\"clear:both;\"></div>";
	}
	
	public function getActions(){
		$DBFilePath = Util::getRootPath()."system/DBData/Installation.pfdb.php";
		$File = new File($DBFilePath);
		$File->loadMe();
		
		$ASetup = "contentManager.loadPlugin('contentRight', 'mInstallation');";

		$B = new Button("Erneut prüfen", "./plugins/Installation/recheck.png", "icon");
		$B->onclick($ASetup);
		$B->id("recheckButton");
		
		$hasDBConnection = false;
		try {
			$Version = mUserdata::getGlobalSettingValue("DBVersion", false);
			$hasDBConnection = true;
		} catch(Exception $e){}
		
		if(!$File->A("FileIsWritable") AND !$hasDBConnection){
			$message = "<p style=\"padding:20px;font-size:20px;color:#555;text-align:center;\">".$_SESSION["applications"]->getActiveApplication()." ist auf diesem Server noch nicht installiert.</p>";
			$html = "<div style=\"width:600px;margin:auto;line-height:1.5;\">
				<p>
				<img src=\"./images/navi/restrictions.png\" style=\"float:left;margin-right:10px;\"/>
				Die Datei <code>/system/DBData/Installation.pfdb.php</code> ist <b>nicht beschreibbar</b>.</p>
				
				<p style=\"margin-top:20px;\">Machen Sie die Datei mit einem FTP-Programm beschreibbar.<br />
				Klicken Sie dazu mit der rechten Maustaste auf die Datei auf dem Server, wählen Sie <b>\"Eigenschaften\"</b>, und geben Sie den Modus <b>666</b> an, damit sie durch den Besitzer, die Gruppe und alle Anderen les- und beschreibbar ist.</p>
				<div style=\"width:350px;margin:auto;padding-top:20px;padding-bottom:20px;\">
				".$this->box($B, $ASetup, "Erneut<br />prüfen")."
				</div>
			</div>";
			die($message.$html);
		}
		
		$containers = 0;
		$message = "";
		$html = "";
		$hidden = "<a class=\"hiddenLink\" href=\"#\" onclick=\"".OnEvent::popup("Fehlerausgabe", "mInstallation", "-1", "updateAllTables", array("'1'"))."return false;\">&nbsp;</a>";
		
		try {
			$Version = mUserdata::getGlobalSettingValue("DBVersion", false);
			
			if($Version !== false AND $Version == Phynx::build()){
				$message = "<p style=\"padding:20px;font-size:20px;color:#555;text-align:center;\">Ihre Datenbank ist auf dem aktuellen Stand.$hidden</p>";
			}
			
			if($Version === false OR $Version != Phynx::build()){
				$message = "<p style=\"padding:10px;font-size:20px;color:#555;margin-bottom:40px;text-align:center;\">Ihre Datenbank muss aktualisiert werden.</p>";
				
				$ASetup = "\$j('#updateButton').attr('src', './plugins/Installation/bigLoader.png'); ".OnEvent::rme($this, "updateAllTables", "", "function(transport){ contentManager.contentBelow(transport.responseText); }");

				$B = new Button("Datenbank aktualisieren", "./plugins/Installation/aktualisieren.png", "icon");
				$B->onclick($ASetup);
				$B->id("updateButton");
				
				
				
				$html = $this->box($B, $ASetup, "Die Datenbank<br />aktualisieren", "", $hidden);

				$html = "<div style=\"width:350px;margin:auto;padding-bottom:40px;\">".$html."</div>";
			}
			
		} catch (NoDBUserDataException $e) {
			$message = "<p style=\"padding:10px;font-size:20px;color:#555;text-align:center;color:red;\">Mit den angegebenen Datenbank-Zugangsdaten kann keine Verbindung aufgebaut werden.</p>";
			
			echo OnEvent::script("contentManager.loadFrame('contentLeft','Installation','1');");
			
			if(PHYNX_MAIN_STORAGE == "MySQL") {
				try {
					$DB1 = new DBStorageU();
					
					$BN = new Button("Hinweis", "notice", "icon");
					$BN->style("float:left;margin-right:10px;");
					
					$File = new File(Util::getRootPath()."system/connect.php");
					
					$B = new Button("Verbindungsart umstellen", "./plugins/Installation/changedb.png", "icon");
					$B->onclick($A);
					$B->id("changedbButton");
					
					$A = OnEvent::rme($this, "switchDBToMySQLo", "", "function(){ Installation.reloadApp(); }");
					
					
					$BR = "<p style=\"margin-top:20px;\">Verwenden Sie den Knopf unten, um die Verbindungsart auf die ältere Version umzustellen.<br />
						Sie müssen sich anschließend erneut anmelden.</p>
						<div style=\"width:350px;margin:auto;padding-top:20px;padding-bottom:20px;\">".$this->box($B, $A, "Verbindungsart<br />umstellen")."</div>";
					

					
					$A = "contentManager.loadPlugin('contentRight', 'mInstallation');";

					$B = new Button("Erneut prüfen", "./plugins/Installation/recheck.png", "icon");
					$B->onclick($A);
					$B->id("recheckButton");
					
					if(!$File->A("FileIsWritable"))
						$BR = "<p style=\"margin-top:20px;\">Bitte machen Sie die Datei /system/connect.php für den Webserver beschreibbar, damit phynx auf die ältere Verbindungsart umstellen kann.<br /><br />Verwenden Sie dazu Ihr FTP-Programm. Klicken Sie mit der rechten Maustaste auf die Datei auf dem Server, wählen Sie \"Eigenschaften\", und geben Sie den Modus 666 an, damit sie durch den Besitzer, die Gruppe und alle Anderen les- und schreibbar ist.
							</p><div style=\"width:350px;margin:auto;padding-top:20px;padding-bottom:20px;\">".$this->box($B, $A, "Erneut<br />prüfen")."</div>";

					
					$html = "<p style=\"margin-top:30px;\">$BN Möglicherweise ist die MySQLi-Erweiterung auf Ihrem Server nicht korrekt konfiguriert.</p>$BR";
					
					$html = "<div style=\"width:700px;margin:auto;padding-bottom:40px;\">".$html."</div>";
				} catch (Exception $e){
					#echo "MySQL geht auch nicht!";
				}
			}
		} 
		catch (DatabaseNotFoundException $e) {
			$message = "<p style=\"padding:10px;font-size:20px;color:#555;text-align:center;color:red;\">Die angegebene Datenbank konnte nicht gefunden werden.</p>";
			
			echo OnEvent::script("contentManager.loadFrame('contentLeft','Installation','1');");
		}
		catch (TableDoesNotExistException $e){
			$message = "<p style=\"padding:10px;font-size:20px;color:#555;margin-bottom:40px;text-align:center;\">Ihre Datenbank hat derzeit noch keinen Inhalt.</p>";
			
			$ASetup = "\$j('#setupButton').attr('src', './plugins/Installation/bigLoader.png'); ".OnEvent::rme($this, "setupAllTables", "", "function(transport){ contentManager.contentBelow(transport.responseText); }");
			
			$BSetup = new Button("Datenbank einrichten", "./plugins/Installation/setup.png", "icon");
			$BSetup->onclick($ASetup);
			$BSetup->id("setupButton");
			
			$hidden = "<a class=\"hiddenLink\" href=\"#\" onclick=\"".OnEvent::popup("Fehlerausgabe", "mInstallation", "-1", "setupAllTables", array("'1'"))."return false;\">&nbsp;</a>";
			
			$html = $this->box($BSetup, $ASetup, "Die Datenbank<br />einrichten", "", $hidden);
			$containers = 1;
			
			
			$BM = new BackupManagerGUI();
			$list = $BM->getBackupsList();
			
			$ARestore = OnEvent::popup("Backup-Manager", "BackupManager", "-1", "inPopup");
			
			$BRestore = new Button("Datenbank wiederherstellen", "./plugins/Installation/restore.png", "icon");
			$BRestore->onclick($ARestore);
			$BRestore->id("setupButton");
			
			$html .= $this->box($BRestore, $ARestore, "Eine Sicherung<br />wiederherstellen", count($list) == 0 ? "color:grey;" : "");
			
			$containers++;
			
			$html = "<div style=\"width:".($containers * 360)."px;margin:auto;padding-bottom:40px;\">".$html."</div>";
		}
		
		echo "$message$html";
	}
	
	private function box(Button $B, $action, $text, $styles = "", $hidden = ""){
		$B->style("float:left;margin-right:10px;");
			
		$html = "
			<div style=\"height:75px;width:350px;display:inline-block;\">
				$B
				<p style=\"padding-top:8px;font-size:25px;\">
					<a href=\"#\" style=\"$styles\" onclick=\"$action return false;\">$text</a>$hidden
				</p>
			</div>";
		
		return $html;
	}
	
	/*public function getHelp($loadInstallation = "false"){
	}*/

	public function testMailGUI(){
		$F = new HTMLForm("mailTest", array("mailfrom","mailto"));

		$F->setSaveRMEPCR("Mailversand testen", "./images/i2/save.gif", "mInstallation", -1, "testMail", "function(transport){ $('mailTestDetailsContent').update(transport.responseText); }");

		$F->setLabel("mailfrom", "Absender");
		$F->setDescriptionField("mailfrom", "E-Mail-Adresse");
		$F->setLabel("mailto", "Empfänger");
		$F->setDescriptionField("mailto", "E-Mail-Adresse");

		echo $F."<div id=\"mailTestDetailsContent\"></div>";
	}

	public function testMail($mailfrom, $mailto){
		#parse_str($data, $out);
		#print_r($out);

		if($mailfrom == "")
			Red::errorD("Bitte geben Sie einen Absender ein!");

		if($mailto == "")
			Red::errorD("Bitte geben Sie einen Empfänger ein!");
		try {
			$mail = new htmlMimeMail5(substr($mailfrom, stripos($mailfrom, "@") + 1));
		} catch(Exception $e){
			die("<p style=\"padding:5px;color:red;\">Fehler beim Übergeben der E-Mail. ".$e->getMessage()."</p>");
		}
		$mail->setFrom("phynx Mailtest <".$mailfrom.">");
		if(!ini_get('safe_mode')) $mail->setReturnPath($mailfrom);
		$mail->setSubject("phynx Mailtest");

		$mail->setText(wordwrap("Diese Nachricht wurde vom phynx Mailtester erzeugt. Ihre E-Mail-Einstellungen sind korrekt.", 80));
		$adressen = array();
		$adressen[] = $mailto;
		if($mail->send($adressen))
			echo "<p style=\"padding:5px;color:green;\">E-Mail erfolgreich übergeben.</p>";
		else
			echo "<p style=\"padding:5px;color:red;\">Fehler beim Übergeben der E-Mail. Bitte überprüfen Sie Ihre Server-Einstellungen.<br />Fehler: ".nl2br(print_r($mail->errors, true))."</p>";

		/*$mimeMail2 = new PHPMailer(false, substr($mailfrom, stripos($mailfrom, "@") + 1));
		$mimeMail2->CharSet = "UTF-8";
		$mimeMail2->Subject = "phynx Mailtest V2";
		
		$mimeMail2->From = $mailfrom;
		$mimeMail2->Sender = $mailfrom;
		$mimeMail2->FromName = "phynx Mailtest";
		
		$mimeMail2->Body = wordwrap("Diese Nachricht wurde vom phynx Mailtester erzeugt. Ihre E-Mail-Einstellungen sind korrekt.", 80);
		$mimeMail2->AddAddress($mailto);
		
		if($mimeMail2->Send())
			echo "<p style=\"padding:5px;color:green;\">E-Mail 2 erfolgreich übergeben.</p>";
		else
			echo "<p style=\"padding:5px;color:red;\">Fehler beim Übergeben der E-Mail 2. Bitte überprüfen Sie Ihre Server-Einstellungen.<br />Fehler: ".nl2br(print_r($mimeMail2->ErrorInfo, true))."</p>";*/
	}

	public function setupAllTables($echoStatus = false){
		$return = parent::setupAllTables();
		if($echoStatus){
			#ksort($return);
			echo "<pre style=\"font-size:10px;padding:5px;overflow:auto;max-height:400px;\">";
			foreach($return AS $plugin => $status){
				echo phynx_mb_str_pad($plugin, 20).": $status\n";
			}
			echo "</pre>";
		}
		
		$message = "<p style=\"padding:10px;font-size:20px;color:green;margin-bottom:40px;text-align:center;\">Ihre Datenbank wurde erfolgreich eingerichtet.</p>";
		
		$action = "contentManager.loadPlugin('contentRight', 'Users');";
		
		$B = new Button("Benutzer anlegen", "./plugins/Installation/benutzer.png", "icon");
		$B->onclick($action);
		
		$html = $this->box($B, $action, "Einen Benutzer<br />anlegen");
		
		
		echo "$message<div style=\"width:350px;margin:auto;padding-bottom:40px;\">".$html."</div>";
	}
	
	public function updateAllTables($echoStatus = false){
		$return = parent::updateAllTables();
		if($echoStatus){
			ksort($return);
			echo "<pre style=\"font-size:10px;padding:5px;overflow:auto;max-height:400px;\">";
			foreach($return AS $plugin => $status){
				echo phynx_mb_str_pad($plugin, 20).": $status\n";
			}
			echo "</pre>";
		}
		$message = "<p style=\"padding:10px;font-size:20px;color:green;margin-bottom:40px;text-align:center;\">Ihre Datenbank wurde erfolgreich aktualisiert.</p>";
		
		$action = "userControl.doLogout();";
		
		$B = new Button("Benutzer abmelden", "./plugins/Installation/abmelden.png", "icon");
		$B->onclick($action);
		
		$html = $this->box($B, $action, "Jetzt normal<br />weiterarbeiten");
		
		
		echo "$message<div style=\"width:350px;margin:auto;padding-bottom:40px;\">".$html."</div>";
	}
}
?>
