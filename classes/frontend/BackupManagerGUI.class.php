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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
function BackupManagerGUIFatalErrorShutdownHandler() {
	$last_error = error_get_last();
	if ($last_error['type'] !== E_ERROR) 
		return;

	if(strpos($last_error['message'], "Allowed memory size of") !== false)
		echo "<p style=\"color:red;\">Ihrer PHP-Installation steht nicht genügend Arbeitsspeicher zur Verfügung,
um die Datensicherung abzuschließen. Bitte erhöhen Sie den Speicher in der
PHP-Konfiguration oder führen Sie die Datenbank-Sicherung mit einer externen Anwendung durch.</p>";
}

class BackupManagerGUI implements iGUIHTML2 {
	public function getHTML($id){
		if($_SESSION["S"]->isUserAdmin() == "0")
			throw new AccessDeniedException();

		$TB = new HTMLTable(3, "Backup wählen");
		$TB->addColStyle(2, "text-align:right;");
		$TB->setColWidth(2, "80px");
		$TB->setColWidth(3, "20px");
		#$TB->setColWidth(4, "20px");

		$gesamt = 0;

		$list = $this->getBackupsList();
		
		foreach($list AS $name => $size){
			$RB = new Button("Backup wiederherstellen","./images/i2/okCatch.png", "icon");
			$RB->onclick("if(confirm('Sind Sie sicher, dass dieses Backup wiederhergestellt werden soll? Es werden dabei alle Daten in der Datenbank überschrieben!')) ");
			$RB->rmePCR("BackupManager", "", "restoreBackup", $name, OnEvent::rme(new mInstallationGUI(), "getActions", "", "function(transport){ contentManager.contentBelow(transport.responseText); }").OnEvent::closePopup("BackupManager")." Popup.displayNamed('BackupManagerGUI','Backup-Manager', transport);");

			$RD = new Button("Backup anzeigen","./images/i2/search.png", "icon");
			$RD->windowRme("BackupManager", "", "displayBackup", $name);

			$TB->addRow(array($name,Util::formatByte($size, 2),$RD, $RB));
			$gesamt += $size;
		}

		$TB->addRow("");
		$TB->addRowClass("backgroundColor0");

		$TB->addRow(array("<b>Gesamt:</b>","<b>".Util::formatByte($gesamt,2)."</b>"));
		$TB->addCellStyle(1, "text-align:right");

		#$ST = new HTMLSideTable("right");
		
		$FTPServer = null;
		try {
			$FTPServer = LoginData::get("BackupFTPServerUserPass");
		} catch(TableDoesNotExistException $e){
			
		}
		$FTPsServer = null;
		try {
			$FTPsServer = LoginData::get("BackupFTPsServerUserPass");
		} catch(TableDoesNotExistException $e){
			
		}
		
		$SFTPServer = null;
		try {
			$SFTPServer = LoginData::get("BackupSFTPServerUserPass");
		} catch(TableDoesNotExistException $e){
			
		}
		$ST = new HTMLSideTable("right");
		
		$B = $ST->addButton("Neue Sicherung\nerstellen", "new");
		$B->popup("", "Backup-Manager", "BackupManager", "-1", "getWindow", array("0", "'Left'"));
		
		$B = $ST->addButton("Sicherungsverz.\nändern", "bericht");
		$B->popup("", "Sicherungsverzeichnis", "BackupManager", "-1", "backupDirChangePopup");
		
		#contentManager.rmePCR('BackupManager', '', 'getWindow', '', 'Popup.displayNamed(\'BackupManagerGUI\',\'Backup-Manager\',transport);');
		$FTPServerID = $FTPServer == null ? -1 : $FTPServer->getID();
		$BFTP = $ST->addButton("FTP-Server\neintragen", "./plugins/Installation/serverMail.png");
		$BFTP->popup("edit", "FTP-Server", "LoginData", $FTPServerID, "getPopup", "", "LoginDataGUI;preset:backupFTPServer");
		
		$FTPsServerID = $FTPsServer == null ? -1 : $FTPsServer->getID();
		$BFTP = $ST->addButton("FTPs-Server\neintragen", "./plugins/Installation/serverMail.png");
		$BFTP->popup("edit", "FTPs-Server", "LoginData", $FTPsServerID, "getPopup", "", "LoginDataGUI;preset:backupFTPsServer");
		
		if(extension_loaded("ssh2")){
			$SFTPServerID = $SFTPServer == null ? -1 : $SFTPServer->getID();
			$BSFTP = $ST->addButton("SFTP-Server\neintragen", "./plugins/Installation/serverMail.png");
			$BSFTP->popup("edit", "SFTP-Server", "LoginData", $SFTPServerID, "getPopup", "", "LoginDataGUI;preset:backupSFTPServer");
		}
		
		$B = $ST->addButton("Einstellungen\nzurücksetzen", "clear");
		$B->rmePCR("BackupManager", "-1", "clearSettings");
		

		#$BRestore = $ST->addButton("Datenbank wiederherstellen", "./plugins/Installation/restore.png");
		#$BRestore->onclick(OnEvent::popup("Backup-Manager", "BackupManager", "-1", "inPopup"));
			
		if(count($list) == 0)
			return "$ST<p class=\"highlight\">Es wurden noch keine Sicherungen angelegt.</p>";
		
		return $ST.$TB;
	}

	public function backupDirChangePopup(){
		echo "<p>Bitte geben Sie das Verzeichnis an, in dem die Datensicherungen abgespeichert werden sollen. <span style=\"color:red;\">Stellen Sie unbedingt sicher, dass das Verzeichnis nicht öffentlich erreichbar ist!</span></p>";
		
		$F = new HTMLForm("dir", array("verzeichnis"));
		$F->getTable()->setColWidth(1, 120);
		$F->setValue("verzeichnis", mUserdata::getGlobalSettingValue("BackupManagerDir", self::getBackupDir()));
		$F->setSaveRMEPCR("Speichern", "", "BackupManager", -1, "backupDirChangeSave", OnEvent::closePopup("BackupManager"));
		$F->setDescriptionField("verzeichnis", "Standard: ".Util::getRootPath()."system/Backup/");
		echo $F;
	}
	
	public function backupDirChangeSave($verzeichnis){
		$verzeichnis = str_replace("\\", "/", $verzeichnis);
		if(trim($verzeichnis) != "")
			$verzeichnis = rtrim($verzeichnis, "/")."/";
		
		
		if(Util::getRootPath()."system/Backup/" == $verzeichnis OR trim($verzeichnis) == ""){
			$U = new mUserdata();
			$U->delUserdata("BackupManagerDir", -1);
			
			Red::messageSaved ();
		}
		
		mUserdata::setUserdataS("BackupManagerDir", $verzeichnis, "", -1);
		Red::messageSaved ();
	}
	
	public static function getBackupDir(){
		try {
			return mUserdata::getGlobalSettingValue("BackupManagerDir", Util::getRootPath()."system/Backup/");
		} catch (Exception $e){
			return Util::getRootPath()."system/Backup/";
		}
	}
	
	public function clearSettings(){
		$AC = anyC::get("Userdata", "name", "noBackupManager");
		while($U = $AC->n())
			$U->deleteMe ();
		
		$AC = anyC::get("Userdata", "name", "disableBackupManager");
		while($U = $AC->n())
			$U->deleteMe ();
		
		Red::messageD("Einstellungen zurückgesetzt");
	}
	
	public function inPopup(){
		if($_SESSION["S"]->isUserAdmin() == "0")
			throw new AccessDeniedException();

		$TB = new HTMLTable(3);
		$TB->addColStyle(2, "text-align:right;");
		$TB->setColWidth(2, "80px");
		$TB->setColWidth(3, "32px");

		$gesamt = 0;

		$list = $this->getBackupsList();
		
		foreach($list AS $name => $size){
			$RB = new Button("Diese Sicherung wiederherstellen","bestaetigung", "icon");
			$RB->onclick("if(confirm('Sind Sie sicher, dass dieses Backup wiederhergestellt werden soll? Es werden dabei alle Daten in der Datenbank überschrieben!')) ");
			$RB->rmePCR("BackupManager", "", "restoreBackup", $name, OnEvent::rme(new mInstallationGUI(), "getActions", "", "function(transport){ contentManager.contentBelow(transport.responseText); }").OnEvent::closePopup("BackupManager")." Popup.displayNamed('BackupManagerGUI','Backup-Manager', transport);");
			
			$RD = new Button("Backup anzeigen","./images/i2/search.png", "icon");
			$RD->windowRme("BackupManager", "", "displayBackup", $name);
			$RD->style("float:left;margin-right:5px;");
			
			$TB->addRow(array($RD.$name,Util::formatByte($size, 2), $RB));
			$gesamt += $size;
		}

		
		if(count($list) == 0)
			die("<p class=\"highlight\">Es wurden noch keine Sicherungen angelegt.</p>
				<p>Wenn Sie eine Sicherung wiederherstellen möchten, dann gehen Sie bitte wie folgt vor:</p>
				<p class=\"confirm\">Kopieren Sie die Dateien im Verzeichnis <strong>/system/Backup</strong> Ihrer alten Installation in das gleiche Verzeichnis in dieser Installation.</p>
				<p>Unter Windows finden Sie das Verzeichnis /system/Backup in der Regel unter C:/Programme (x86)/open3A/htdocs oder C:/open3A/htdocs.</p>");
		
		echo $TB;
	}
	
	public function displayBackup($name){
		if($_SESSION["S"]->isUserAdmin() == "0")
			throw new AccessDeniedException();
		
		header('Content-Type: application/octet-stream');
		#header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"" . basename($name) . "\"");
		header("Content-Length: ". filesize(self::getBackupDir().$name));
		#$html = Util::getBasicHTML("", $name);
		#$html = str_replace("</html>","", $html);
		#$html = str_replace("</body>","", $html);
		#echo "$html<pre>";
		readfile(self::getBackupDir().$name);
		#echo "</pre></body></html>";
	}

	private function noBackupButton(){

			$GoAway = new HTMLInput("BackupGoAway", "checkbox");
			$GoAway->id("BackupGoAway");
			$GoAway->style("float:left;margin-right:5px;");

			$BSave = new Button("Einstellung\nspeichern","save");
			$BSave->style("float:right;");
			$BSave->onclick("if($('BackupGoAway').checked) ");
			$BSave->rmePCR("BackupManager", "", "GoAway", "", "Popup.close('','BackupManagerGUI'); contentManager.reloadFrame('contentScreen');");

			return $BSave.$GoAway." <label for=\"BackupGoAway\" style=\"float:none;text-align:left;width:auto;\">Diese Meldung nicht mehr anzeigen, ich erstelle meine Backups selbst.</label>";
	}

	public function getWindow($redo = false, $reload = "none"){
		register_shutdown_function('BackupManagerGUIFatalErrorShutdownHandler');
		
		$F = new File(self::getBackupDir());
		
		if(!$F->A("FileIsWritable")){
			$B = new Button("Achtung","restrictions");
			$B->type("icon");
			$B->style("float:left;margin-right:10px;");

			$T = new HTMLTable(1);

			$T->addRow($B."Es können keine Backups von Ihrer Datenbank angelegt werden, da das Verzeichnis ".self::getBackupDir()." nicht durch den Webserver beschreibbar ist.");
			$T->addRow("Machen Sie das Verzeichnis mit einem FTP-Programm beschreibbar. Klicken Sie dazu mit der rechten Maustaste auf das Verzeichnis auf dem Server, wählen Sie \"Eigenschaften\", und geben Sie den Modus 777 an, damit es durch den Besitzer, die Gruppe und alle Anderen les- und schreibbar ist.");

			$BRefresh = new Button("Aktualisieren","refresh");
			$BRefresh->rmePCR("BackupManager", "", "getWindow", "", "Popup.displayNamed('BackupManagerGUI','Backup-Manager',transport);");
			$BRefresh->style("float:right;");
			$T->addRow($BRefresh);

			$T->addRow("");
			$T->addRowClass("backgroundColor0");


			$T->addRow($this->noBackupButton());
			die($T);
		}

		$html = "";

		if(!BackupManagerGUI::checkForTodaysBackup() OR $redo){
			$T = new HTMLTable(1);
			
			if($redo)
				unlink(BackupManagerGUI::getNewBackupName());
			
			$BOK = $this->makeBackupOfToday();
			$F = new File(BackupManagerGUI::getNewBackupName());
			$F->loadMe();
			
			if($BOK === basename(BackupManagerGUI::getNewBackupName()) AND $F->A("FileSize") > 100) {
				$B = new Button("Backup abgeschlossen","okCatch");
				$B->type("icon");
				$B->style("float:left;margin-right:10px;");

				$T->addRow($B."Das Backup wurde erfolgreich abgeschlossen!<br>Die Größe der Sicherungsdatei beträgt <strong>".Util::formatByte($F->A("FileSize"), 2)."</strong>");
				$T->addRowClass("backgroundColor0");
				
				try {
					$ftpUpload = $this->FTPUpload(self::getBackupDir().$BOK);
					if($ftpUpload === true){
						$B = new Button("FTP-Upload erfolgreich","okCatch");
						$B->type("icon");
						$B->style("float:left;margin-right:10px;");

						$T->addRow(array($B."Das Backup wurde erfolgreich auf den FTP-Server hochgeladen"));
					}
					
					$ftpsUpload = $this->FTPsUpload(self::getBackupDir().$BOK);
					if($ftpsUpload === true){
						$B = new Button("FTP-Upload erfolgreich","okCatch");
						$B->type("icon");
						$B->style("float:left;margin-right:10px;");

						$T->addRow(array($B."Das Backup wurde erfolgreich auf den FTPs-Server hochgeladen"));
					}
					
					
					$sftpUpload = $this->SFTPUpload(self::getBackupDir().$BOK);
					if($sftpUpload === true){
						$B = new Button("SFTP-Upload erfolgreich","okCatch");
						$B->type("icon");
						$B->style("float:left;margin-right:10px;");

						$T->addRow(array($B."Das Backup wurde erfolgreich auf den SFTP-Server hochgeladen"));
					}
				} catch (Exception $e){
					$B->image("warning");
					$T->addRow(array($B.$e->getMessage()));
				}
				$html .= $T;
			} else {
				$B = new Button("Es ist ein Fehler aufgetreten","stop");
				$B->type("icon");
				$B->style("float:left;margin-right:10px;");

				$T->addRow($B."Beim Erstellen des Backups ist ein Fehler aufgetreten: $BOK");
				$html .= $T;
			}
			$html .= OnEvent::script(OnEvent::frame("Screen", "Desktop"));#"<script type=\"text/javascript\">contentManager.reloadFrame('contentLeft');</script>";
		}


		$gesamt = 0;
		$data = $this->getBackupsList();

		$i = 0;
		$maxD = 5;
		if(count($data) < $maxD) $maxD = count($data);

		$TF = new HTMLTable(2, "Backups ($maxD/".count($data).")");
		$TF->addColStyle(2, "text-align:right;");
		$TF->setColWidth(2, "80px");

		foreach ($data as $name => $size) {
			if($i < 5){
				if($name == basename(BackupManagerGUI::getNewBackupName())) $name = "<span style=\"color:green;\">$name</span>";
				$TF->addRow(array($name, Util::formatByte($size,2)));
			}
			$i++;
			$gesamt += $size;
		}

		$TF->addRow("");
		$TF->addRowClass("backgroundColor0");

		$TF->addRow(array("<b>Gesamt:</b>","<b>".Util::formatByte($gesamt,2)."</b>"));
		$TF->addCellStyle(1, "text-align:right");

		$TF->addRow(array("Diese Backups können als Admin-Benutzer im Installation-Plugin heruntergeladen und wiederhergestellt werden."));
		$TF->addRowColspan(1, 2);

		$TF->addRow("");
		$TF->addRowClass("backgroundColor0");

		$TF->addRow(array($this->noBackupButton()));
		$TF->addRowColspan(1, 2);

		$BC = new Button("Fenster\nschließen", "bestaetigung");
		$BC->onclick(OnEvent::closePopup("", "BackupManagerGUI").OnEvent::closePopup("", "BackupManager"));
		$BC->style("float:right;margin:10px;");
		
		$BD = new Button("Details\nanzeigen", "down");
		$BD->onclick("\$j('#BMMoreDetails').slideToggle();");
		$BD->style("margin:10px;");
		$BD->className("backgroundColor0");
		
		
		if($reload != "none")
			echo OnEvent::script (OnEvent::reload ($reload));
		
		echo $html.$BC.$BD."<div style=\"clear:both;\"></div><div id=\"BMMoreDetails\" style=\"display:none;\">".$TF."</div>";
	}

	public function deleteOldBackups(){
		$Backups = $this->getBackupsList();
		$i = 0;

		foreach($Backups AS $fileName => $size){
			$i++;

			if($i > 27){
				unlink(self::getBackupDir().$fileName);
				#$F->deleteMe();
			}
		}
	}

	public function getBackupsList(){
		$data = array();
		$dir = dirname(BackupManagerGUI::getNewBackupName());
		if(!file_exists($dir))
			return $data;

		$dir = new DirectoryIterator($dir);

		foreach($dir as $value){
			if ($value->isDot())
				continue;
			
			if($value->isDir())
				continue;
			
			if(strpos($value->getFilename(), ".") === 0)
				continue;
			
			if(strpos($value->getFilename(), ".sql.gz") === false)
				continue;
			
			$data[$value->getFilename()] = $value->getSize();
		}

		krsort($data);

		return $data;
	}
	
	public static function getNewBackupName(){
		return self::getBackupDir().$_SESSION["DBData"]["datab"].".".date("Ymd")."_utf8.sql.gz";
	}

	public function makeBackupOfToday(){
		$this->deleteOldBackups();
		
		$F = new File(self::getBackupDir().".htaccess");
		$F->loadMe();

		if($F->getA() == null AND self::getBackupDir() == Util::getRootPath()."system/Backup/"){
			file_put_contents($F->getID(), "<IfModule mod_authz_core.c>
    Require all denied
</IfModule>

<IfModule !mod_authz_core.c>
    Order Allow,Deny
    Deny from all
</IfModule>");
			/*file_put_contents($F->getID(), "AuthUserFile ".Util::getRootPath()."system/Backup/.htpasswd
AuthGroupFile /dev/null
AuthName \"Restricted\"
AuthType Basic
<Limit GET>
require valid-user
</Limit>");

			file_put_contents(Util::getRootPath()."system/Backup/.htpasswd", "Restricted:kV.RuW/ox2sc2".mt_rand(0, 20000000));*/
		}

		require Util::getRootPath()."libraries/PMBP.inc.php";

		$CONF = array();
		$CONF['sql_host'] = $_SESSION["DBData"]["host"];
		$CONF['sql_user'] = $_SESSION["DBData"]["user"];
		$CONF['sql_passwd'] = $_SESSION["DBData"]["password"];
		$CONF['date'] = "d.m.Y";
		$CONF['sql_db'] = $_SESSION["DBData"]["datab"];

		define("PMBP_EXPORT_DIR", self::getBackupDir());
		define('PMBP_VERSION',"v.2.1");
		define('PMBP_WEBSITE',"http://www.phpMyBackupPro.net");

		$PMBP_SYS_VAR = array();
		$PMBP_SYS_VAR["except_tables"] = "";

		$filename = PMBP_dump($CONF, $PMBP_SYS_VAR, $_SESSION["DBData"]["datab"], true, true, false, false, "");
		
		if(file_exists($filename))
			chmod(self::getBackupDir().$filename, 0666);
		return $filename;
	}

	public function FTPsUpload($filename){
		$FTPServer = LoginData::get("BackupFTPsServerUserPass");
		
		if($FTPServer == null OR $FTPServer->A("server") == "")
			return null;
		
		$ftp_server = $FTPServer->A("server");
		$benutzername = $FTPServer->A("benutzername");
		$passwort = $FTPServer->A("passwort");

		$port = 21;
		$ex = explode(":", $ftp_server);
		if(isset($ex[1])){
			$port = $ex[1];
			$ftp_server = $ex[0];
		}
		
		$connection_id = ftp_ssl_connect($ftp_server, $port);
		
		if (!$connection_id) 
			throw new Exception("Verbindung mit FTPs-Server $ftp_server nicht möglich!");

		$login_result = ftp_login($connection_id, $benutzername, $passwort);
		
		if (!$login_result) 
			throw new Exception("Anmeldung als Benutzer $benutzername nicht möglich!");
		
		
		$subDir = $FTPServer->A("optionen");
		if($subDir != "" AND $subDir[strlen($subDir) - 1] != "/")
			$subDir .= "/";
		
		$zieldatei = $subDir.basename($filename);
		$lokale_datei = $filename;

		$upload = ftp_put($connection_id, $zieldatei, $lokale_datei, FTP_ASCII);
		
		if (!$upload){
			ftp_pasv($connection_id, true);
			$upload = ftp_put($connection_id, $zieldatei, $lokale_datei, FTP_ASCII);
		}
		
		if (!$upload)
		  throw new Exception("Beim FTP-Upload ist ein Fehler aufgetreten");
		
		ftp_quit($connection_id);
		
		return true;
	}

	public function FTPUpload($filename){
		$FTPServer = LoginData::get("BackupFTPServerUserPass");
		
		if($FTPServer == null OR $FTPServer->A("server") == "")
			return null;
		
		$ftp_server = $FTPServer->A("server");
		$benutzername = $FTPServer->A("benutzername");
		$passwort = $FTPServer->A("passwort");

		$port = 21;
		$ex = explode(":", $ftp_server);
		if(isset($ex[1])){
			$port = $ex[1];
			$ftp_server = $ex[0];
		}
		
		$connection_id = ftp_connect($ftp_server, $port);
		
		if (!$connection_id) 
			throw new Exception("Verbindung mit FTP-Server $ftp_server nicht möglich!");

		$login_result = ftp_login($connection_id, $benutzername, $passwort);
		
		if (!$login_result) 
			throw new Exception("Anmeldung als Benutzer $benutzername nicht möglich!");
		
		
		$subDir = $FTPServer->A("optionen");
		if($subDir != "" AND $subDir[strlen($subDir) - 1] != "/")
			$subDir .= "/";
		
		$zieldatei = $subDir.basename($filename);
		$lokale_datei = $filename;

		$upload = ftp_put($connection_id, $zieldatei, $lokale_datei, FTP_ASCII);
		
		if (!$upload){
			ftp_pasv($connection_id, true);
			$upload = ftp_put($connection_id, $zieldatei, $lokale_datei, FTP_ASCII);
		}
		
		if (!$upload)
		  throw new Exception("Beim FTP-Upload ist ein Fehler aufgetreten");
		
		ftp_quit($connection_id);
		
		return true;
	}
	
	public function SFTPUpload($filename){
		$FTPServer = LoginData::get("BackupSFTPServerUserPass");
		
		if($FTPServer == null OR $FTPServer->A("server") == "")
			return null;
		
		$subDir = $FTPServer->A("optionen");
		if($subDir != "" AND $subDir[strlen($subDir) - 1] != "/")
			$subDir .= "/";
		
		$zieldatei = $subDir.basename($filename);
		
		$connection = ssh2_connect($FTPServer->A("server"), 22);
		$login = ssh2_auth_password($connection, $FTPServer->A("benutzername"), $FTPServer->A("passwort"));
		
		if (!$login)
			throw new Exception("Anmeldung an SFTP-Server fehlgeschlagen!");
		
		$upload = ssh2_scp_send($connection, $filename, $zieldatei, 0644);
		
		if (!$upload)
			throw new Exception("Beim SFTP-Upload ist ein Fehler aufgetreten");
		
		return true;
	}
	
	public function restoreBackup($name){
		if($_SESSION["S"]->isUserAdmin() == "0")
			throw new AccessDeniedException();

		require Util::getRootPath()."libraries/PMBP.inc.php";

		$DB = new DBStorage();
		$con = $DB->getConnection();
		if(strpos($name, "_utf8"))
			mysqli_set_charset($con, "utf8");
		else
			mysqli_set_charset($con, "latin1");
		
		if(substr($name, -3, 3) == ".gz")
			$file = gzopen(self::getBackupDir().$name, "r");
		else
			$file = fopen(self::getBackupDir().$name, "r");

		$return = PMBP_exec_sql($file, $con);

		$Tab = new HTMLTable(2);
		$Tab->setColWidth(1, "120px");
		$Tab->addLV("Tabellen", $return["insertQueries"]);
		$Tab->addLV("Datensätze", $return["tableQueries"]);
		$Tab->addLV("Befehle gesamt", $return["totalqueries"]);
		$Tab->addLV("Zeilen", $return["linenumber"]);
		$Tab->addLV("Fehler", $return["error"]);

		echo $Tab.OnEvent::script("\$j('.installHiddenTab').fadeIn();");
	}

	public static function checkForTodaysBackup(){
		$BF = BackupManagerGUI::getNewBackupName();
		$F = new File($BF);
		$F->loadMe();
		return $F->getA() != null;
	}

	public function GoAway(){
		$UD = new mUserdata();
		$UD->setUserdata("noBackupManager", 1);
	}
}
?>
