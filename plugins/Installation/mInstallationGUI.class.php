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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mInstallationGUI extends mInstallation implements iGUIHTML2 {

	/*function updateAllTables(){
		parent::updateAllTables();
	}*/
	
	function getHTML($id){
		$showHelp = true;

		if($this->collector == null) $this->lCV3($id);
		$singularLanguageClass = $this->loadLanguageClass("Installation");
		$text = $singularLanguageClass != null ? $singularLanguageClass->getText() : "";
		
		$t = new HTMLTable(1);
		
		$g = "";
		$DBFilePath = Util::getRootPath()."system/DBData/Installation.pfdb.php";
		$writable = new HTMLTable(1);
		$File = new File($DBFilePath);
		$File->loadMe();
		if(!$File->getA()->FileIsWritable){
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

			$help = "
	<script type=\"text/javascript\">
		contentManager.rmePCR('mInstallation','','getHelp','true','if(checkResponse(transport)) { Popup.create(\'123\', \'Installation\', \'Hilfe\'); Popup.update(transport, \'123\', \'Installation\'); }');
	</script>";

		try {
			$user = new User(1);
			$user->loadMe();
		}
		catch (DatabaseNotSelectedException $e) {
			$t->addRow(isset($text["noDatabase"]) ? $text["noDatabase"] : "Es wurde kein korrekter Datenbankname angegeben.<br /><br />Bitte geben Sie eine existierende Datenbank an, sie wird nicht automatisch erzeugt.");
			return $g.$t->getHTML().$help;
		}
		catch (NoDBUserDataException $e) { 
			$t->addRow(isset($text["wrongData"]) ? $text["wrongData"] : "Mit den angegebenen Datenbank-Zugangsdaten kann keine Verbindung aufgebaut werden.<br /><br />Wenn sie korrekt sind, wird hier eine Liste der Plugins angezeigt.");
			
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
			
			return $g.$t->getHTML().$help;
		}
		catch (DatabaseNotFoundException $e) {
			$t->addRow(isset($text["noDatabase"]) ? $text["noDatabase"] : "Es wurde kein korrekter Datenbankname angegeben.<br /><br />Bitte geben Sie eine existierende Datenbank an, sie wird nicht automatisch erzeugt.");
			return $g.$t->getHTML().$help;
		}
		catch (TableDoesNotExistException $e) {}
		catch (StorageException $e) {}

			$help = "
	<script type=\"text/javascript\">
		rme('mInstallation','','getHelp','false','if(checkResponse(transport)) { Popup.create(\'123\', \'Installation\', \'Hilfe\'); Popup.update(transport, \'123\', \'Installation\'); }');
	</script>";

		if($id == -1) {
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
				
				/*$e = explode(", ",$_SESSION["CurrentAppPlugins"]->getDepsOfPlugin($key));
				for($i=0;$i<count($e);$i++) 
					if($e[$i] != "none") 
						$e[$i] = (($p[$e[$i]] != -1) ? $p[$e[$i]] : $e[$i]);*/

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
		}
		$showHelp = false;
		if(!$showHelp)
			$help = "";

		$ST = new HTMLSideTable("left");
		
		try {
			$BTestMail = $ST->addButton("Mailversand\ntesten", "mail");
			$BTestMail->popup("mailTest", "Mailversand testen", "mInstallation", "-1", "testMailGUI");

			$MailServer = LoginData::get("MailServerUserPass");
			$MailServerID = $MailServer == null ? -1 : $MailServer->getID();
			$BMail = $ST->addButton("Mail-Server\neintragen", "./plugins/Installation/serverMail.png");
			$BMail->popup("edit", "Mail-Server", "LoginData", $MailServerID, "getPopup", "", "LoginDataGUI;preset:mailServer");
		} catch(Exception $e){}

		return (!$showHelp ? $ST : "").$g.$help;
	}
	
	public function getHelp($loadInstallation = "false"){
		$DBFilePath = Util::getRootPath()."system/DBData/Installation.pfdb.php";
		$File = new File($DBFilePath);
		$File->loadMe();
		
		
		$BH = new Button("Hilfe","hilfe");
		$BH->style("float: left; margin-right: 10px;");
		$BH->type("icon");

		$BReload = new Button("Ansicht\naktualisieren","refresh");
		$BReload->onclick("contentManager.emptyFrame('contentLeft'); contentManager.loadFrame('contentRight', 'mInstallation', -1, 0, 'mInstallationGUI;-');Popup.closeNonPersistent();");

		echo "<p style=\"padding:5px;\">
		$BH
		<b>".$_SESSION["applications"]->getActiveApplication()." ist auf diesem Server noch nicht installiert.</b>
		</p>
		".(!$File->A("FileIsWritable") ? "<p style=\"padding:5px;\">
		Bitte machen Sie zunächst die Datei Installation.pfdb.php beschreibbar, wie rechts oben angegeben.<br /><br />$BReload
		</p>" : "
		<p style=\"padding:5px;\">
		Tragen Sie links die Datenbank-Zugangsdaten ein, die Sie von Ihrem Webspace-Provider erhalten und anschließend auf 'Installation speichern'.
		</p>
		<p style=\"padding:5px;\">
		Wenn die Zugangsdaten richtig sind, erscheinen weitere Zeilen auf der rechten Seite. Klicken Sie für jedes Plugin auf 'Tabelle anlegen'.
		</p>
		<p style=\"padding:5px;\">
		Wenn Sie alle Tabellen angelegt haben, legen Sie im Benutzer-Reiter noch einen </b>Benutzer ohne Admin-Rechte</b> an und ändern Sie das Passwort des Admin-Benutzers.
		</p>
		".($loadInstallation == "true" ? "<script type=\"text/javascript\">
			contentManager.loadFrame('contentLeft','Installation','1');
		</script>" : ""))."";
	}

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

		$mail = new htmlMimeMail5();
		$mail->setFrom("phynx Mailtest <".$mailfrom.">");
		if(!ini_get('safe_mode')) $mail->setReturnPath($mailfrom);
		$mail->setSubject("phynx Mailtest");

		$mail->setText(wordwrap("Diese Nachricht wurde vom phynx Mailtester erzeugt. Ihre E-Mail-Einstellungen sind korrekt.",80));
		$adressen = array();
		$adressen[] = $mailto;
		if($mail->send($adressen))
			echo "<p style=\"padding:5px;color:green;\">E-Mail erfolgreich übergeben.</p>";
		else
			echo "<p style=\"padding:5px;color:red;\">Fehler beim Übergeben der E-Mail. Bitte überprüfen Sie Ihre Server-Einstellungen.<br />Fehler: ".nl2br(print_r($mail->errors, true))."</p>";

	}

}
?>
