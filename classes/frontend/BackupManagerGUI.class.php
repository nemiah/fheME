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
function BackupManagerGUIFatalErrorShutdownHandler() {
	$last_error = error_get_last();
	if ($last_error['type'] !== E_ERROR) 
		return;

	if(strpos($last_error['message'], "Allowed memory size of") !== false)
		echo "<p style=\"color:red;\">Ihrer PHP-Installation steht nicht genügend Speicher zur Verfügung, um die Datensicherung abzuschließen. Bitte erhöhen Sie den Speicher in der PHP-Konfiguration oder führen Sie die Datenbank-Sicherung mit einer externen Anwendung durch.</p>";
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

			$TB->addRow(array($name,Util::formatByte($size, 2),$RD/*,$RB*/));
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
		$ST = new HTMLSideTable("right");
		
		$FTPServerID = $FTPServer == null ? -1 : $FTPServer->getID();
		$BFTP = $ST->addButton("FTP-Server\neintragen", "./plugins/Installation/serverMail.png");
		$BFTP->popup("edit", "FTP-Server", "LoginData", $FTPServerID, "getPopup", "", "LoginDataGUI;preset:backupFTPServer");
		
		
		
		if(count($list) == 0)
			return "$ST<p>Es wurden noch keine Sicherungen angelegt.</p>";
		
		return $ST.$TB;
	}

	public function inPopup(){
		echo $this->getHTML(-1);
	}
	
	public function displayBackup($name){
		if($_SESSION["S"]->isUserAdmin() == "0")
			throw new AccessDeniedException();

		$html = Util::getBasicHTML("", $name);
		$html = str_replace("</html>","", $html);
		$html = str_replace("</body>","", $html);
		echo "$html<pre>";
		readfile(Util::getRootPath()."system/Backup/$name");
		echo "</pre></body></html>";
	}

	private function noBackupButton(){

			$GoAway = new HTMLInput("BackupGoAway", "checkbox");
			$GoAway->id("BackupGoAway");
			$GoAway->style("float:left;margin-right:5px;");

			$BSave = new Button("Einstellung\nspeichern","save");
			$BSave->style("float:right;");
			$BSave->onclick("if($('BackupGoAway').checked) ");
			$BSave->rmePCR("BackupManager", "", "GoAway", "", "Popup.close('','BackupManagerGUI'); contentManager.reloadFrame('contentLeft');");

			return $BSave.$GoAway." <label for=\"BackupGoAway\" style=\"float:none;text-align:left;width:auto;\">Diese Meldung nicht mehr anzeigen, ich erstelle meine Backups selbst.</label>";
	}

	public function getWindow($redo = false){
		register_shutdown_function('BackupManagerGUIFatalErrorShutdownHandler');
		
		$F = new File(Util::getRootPath()."system/Backup");
		
		if(!$F->A("FileIsWritable")){
			$B = new Button("Achtung","restrictions");
			$B->type("icon");
			$B->style("float:left;margin-right:10px;");

			$T = new HTMLTable(1);

			$T->addRow($B."Es können keine Backups von Ihrer Datenbank angelegt werden, da das Verzeichnis /system/Backup nicht durch den Webserver beschreibbar ist.");
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

				$T->addRow($B."Das Backup wurde erfolgreich abgeschlossen!<br />Die Größe der Sicherungsdatei beträgt <strong>".Util::formatByte($F->A("FileSize"), 2)."</strong>");
				$T->addRowClass("backgroundColor0");
				
				try {
					$ftpUpload = $this->FTPUpload(Util::getRootPath()."system/Backup/$BOK");
					
					if($ftpUpload === true){
						$B = new Button("FTP-Upload erfolgreich","okCatch");
						$B->type("icon");
						$B->style("float:left;margin-right:10px;");

						$T->addRow(array($B."Das Backup wurde erfolgreich auf den FTP-Server hochgeladen"));
					}
				} catch (Exception $e){
					
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
			$html .= OnEvent::script(OnEvent::frame("desktopLeft", "Desktop", "2"));#"<script type=\"text/javascript\">contentManager.reloadFrame('contentLeft');</script>";
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
		$BC->onclick(OnEvent::closePopup("", "BackupManagerGUI"));
		$BC->style("float:right;margin:10px;");
		
		$BD = new Button("Details\nanzeigen", "down");
		$BD->onclick("\$j('#BMMoreDetails').slideToggle();");
		$BD->style("margin:10px;");
		$BD->className("backgroundColor0");
		
		echo $html.$BC.$BD."<div style=\"clear:both;\"></div><div id=\"BMMoreDetails\" style=\"display:none;\">".$TF."</div>";
	}

	public function deleteOldBackups(){
		$Backups = $this->getBackupsList();
		$i = 0;

		foreach($Backups AS $fileName => $size){
			$i++;

			if($i > 27){
				unlink(Util::getRootPath()."system/Backup/".$fileName);
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

		foreach($dir as $value)
			if (!$value->isDot() AND strpos($value->getFilename(), ".") !== 0)
				$data[$value->getFilename()] = $value->getSize();

		krsort($data);

		return $data;
	}
	
	private static function getNewBackupName(){
		return Util::getRootPath()."system/Backup/".$_SESSION["DBData"]["datab"].".".date("Ymd").".sql";
	}

	public function makeBackupOfToday(){
		$this->deleteOldBackups();
		
		$F = new File(Util::getRootPath()."system/Backup/.htaccess");
		$F->loadMe();

		if($F->getA() == null){
			file_put_contents($F->getID(), "AuthUserFile ".Util::getRootPath()."system/Backup/.htpasswd
AuthGroupFile /dev/null
AuthName \"Restricted\"
AuthType Basic
<Limit GET>
require valid-user
</Limit>");

			file_put_contents(Util::getRootPath()."system/Backup/.htpasswd", "Restricted:kV.RuW/ox2sc2".mt_rand(0, 20000000));
		}

		require Util::getRootPath()."libraries/PMBP.inc.php";

		$CONF = array();
		$CONF['sql_host'] = $_SESSION["DBData"]["host"];
		$CONF['sql_user'] = $_SESSION["DBData"]["user"];
		$CONF['sql_passwd'] = $_SESSION["DBData"]["password"];
		$CONF['date'] = "d.m.Y";
		$CONF['sql_db'] = $_SESSION["DBData"]["datab"];

		define("PMBP_EXPORT_DIR", Util::getRootPath()."system/Backup/");
		define('PMBP_VERSION',"v.2.1");
		define('PMBP_WEBSITE',"http://www.phpMyBackupPro.net");

		$PMBP_SYS_VAR = array();
		$PMBP_SYS_VAR["except_tables"] = "";

		$filename = PMBP_dump($CONF, $PMBP_SYS_VAR, $_SESSION["DBData"]["datab"], true, true, false, false, "");
		
		chmod(Util::getRootPath()."system/Backup/".$filename, 0666);
		return $filename;
	}

	public function FTPUpload($filename){
		$FTPServer = LoginData::get("BackupFTPServerUserPass");
		
		if($FTPServer == null OR $FTPServer->A("server") == "")
			return null;
		
		$ftp_server = $FTPServer->A("server");
		$benutzername = $FTPServer->A("benutzername");
		$passwort = $FTPServer->A("passwort");

		$connection_id = ftp_connect($ftp_server);

		$login_result = ftp_login($connection_id, $benutzername, $passwort);

		if ((!$connection_id) || (!$login_result)) 
			throw new Exception("Verbindung mit FTP-Server $ftp_server als Benutzer $benutzername nicht möglich!");
		
		
		$subDir = $FTPServer->A("optionen");
		if($subDir != "" AND $subDir[strlen($subDir) - 1] != "/")
			$subDir .= "/";
		
		$zieldatei = $subDir.basename($filename);
		$lokale_datei = $filename;

		$upload = ftp_put($connection_id, $zieldatei, $lokale_datei, FTP_ASCII);

		if (!$upload)
		  throw new Exception("Beim FTP-Upload ist ein Fehler aufgetreten");
		
		ftp_quit($connection_id);
		
		return true;
	}
	
	public function restoreBackup($name){
		if($_SESSION["S"]->isUserAdmin() == "0")
			throw new AccessDeniedException();

		require Util::getRootPath()."libraries/PMBP.inc.php";

		$DB = new DBStorageU();
		$con = $DB->getConnection();

		$file = fopen(Util::getRootPath()."system/Backup/$name", "r");

		$return = PMBP_exec_sql($file, $con);

		$Tab = new HTMLTable(2);
		$Tab->setColWidth(1, "120px");
		$Tab->addLV("Tabellen", $return["insertQueries"]);
		$Tab->addLV("Datensätze", $return["tableQueries"]);
		$Tab->addLV("Befehle gesamt", $return["totalqueries"]);
		$Tab->addLV("Zeilen", $return["linenumber"]);
		$Tab->addLV("Fehler", $return["error"]);

		echo $Tab;
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
