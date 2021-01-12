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
class DesktopGUI extends UnpersistentClass implements iGUIHTML2 {
	public function getHTML($id){
		if(Applications::activeApplication() != "supportBox" AND $_SESSION["S"]->isUserAdmin()) {
			$D = new ADesktopGUI();
			return $D->getHTML($id);
		}
		
		$c = Applications::activeApplication()."DesktopGUI";

		try {
			$c = new $c();
			
			if($id == "1")
				return "
					<div class=\"DesktopCol\"><div id=\"desktopRight\" style=\"padding:10px;\">".$c->getHTML($id)."</div></div>
					<div class=\"DesktopCol DesktopCol2\"><div id=\"desktopMiddle\" style=\"padding:10px;width:90%;margin:auto;\"></div></div>
					<div class=\"DesktopCol DesktopCol3\"><div id=\"desktopLeft\" style=\"padding:10px;\"></div></div>
					".OnEvent::script(OnEvent::frame("desktopLeft", "Desktop", "2").OnEvent::frame("desktopMiddle", "Desktop", "3"))."<div style=\"clear:both;\"></div>";
			
			return $c->getHTML($id);
		} catch(ClassNotFoundException $e) {}
	
		
		$data = ADesktopGUI::dataGet();
		
		$backgroundStyle = "";
		/*try {
			if(file_exists(Util::getRootPath()."ubiquitous/Hintergrundbilder")){
				require_once Util::getRootPath()."ubiquitous/Hintergrundbilder/Hintergrundbild.class.php";

				$HG = Hintergrundbild::find();
				if($HG)
					$backgroundStyle = "background-image: url(".$HG->A("HintergrundbildImageURL").");background-size: cover;background-position: bottom center;background-repeat: no-repeat;";

			}
		} catch (Exception $ex) {

		}*/
		
		$message = "<small style=\"color:#aaa;\">Willkommen bei</small>
			<br>".Applications::activeApplicationLabel()."!";
		$style = "letter-spacing: 0.2em;";
		
		if(date("md") >= 1201 AND date("md") < 1224){
			$message = "<small style=\"color:#aaa;\">".Applications::activeApplicationLabel()." wünscht Ihnen</small><br>eine schöne Adventszeit!";
			$style = "letter-spacing: 0.1em;";
			$backgroundStyle = "background-image: url(./images/seasons/advent.svg);background-size:50%;background-position: 99% 2em;background-repeat: no-repeat;";
		}
		
		if(date("md") >= 1224 AND date("md") < 1227){
			$message = "<small style=\"color:#aaa;\">".Applications::activeApplicationLabel()." wünscht Ihnen</small><br>frohe Weihnachten!";
			$style = "letter-spacing: 0.1em;";
			$backgroundStyle = "background-image: url(./images/seasons/advent.svg);background-size:50%;background-position: 99% 2em;background-repeat: no-repeat;";
			#$backgroundStyle = "background-image: url(./images/seasons/weihnachten.svg);background-size:20em;background-position: 94% 2em;background-repeat: no-repeat;";
		}
		
		if(date("md") >= 101 AND date("md") < 107){
			$message = "<small style=\"color:#aaa;\">".Applications::activeApplicationLabel()." wünscht Ihnen</small><br>ein gutes neues Jahr!";
			$style = "letter-spacing: 0.1em;";
			$backgroundStyle = "background-image: url(./images/seasons/advent.svg);background-size:50%;background-position: 99% 2em;background-repeat: no-repeat;";
		}
		
		#if($message != "")
		#	$message = "<br><small style=\"display:inline-block;padding-top:1em;\">$message</small>";
		
		$html = "<div class=\"SpellbookContainer\" style=\"margin-right:0;$backgroundStyle\">
			<h1 class=\"prettyTitle\" style=\"text-align:center;font-size:4em;color:#444;padding-top:1.8em;padding-bottom:1.7em;$style\">
			$message</h1>";
		
		$B = new Button("Öffnen", "arrow_right", "iconicG");
		
		$T = new HTMLTable(2);
		$T->setColWidth(2, 20);
		$T->useForSelection(false);
		$T->weight("lightlyColored");
		
		if(Session::isPluginLoaded("mHilfe")){
			$T->addRow(["Die ersten Schritte mit open3A", $B]);
			if(mUserdata::getUDValueS("firstStepsSeen", "0") != "1"){
				$T->addRowClass("confirm");
				$T->addRowStyle("font-weight:bold;");
			}
			
			$T->addRowEvent("click", OnEvent::window(new mHilfeGUI(), "firstSteps"));
			
			$T->addRow(["Das Hilfe-Plugin", $B]);
			$T->addRowEvent("click", OnEvent::frame("Screen", "mHilfe"));
		}
		
		#$T->addRow(["Der Blog", $B]);
		#$T->addRowEvent("click", "window.open('https://www.open3a.de/page-Blog');");
		
		$T->addRow(["Die angezeigten Reiter anpassen", $B]);
		$T->addRowEvent("click", OnEvent::frame("Screen", "Spellbook"));
		
		$T->addRow(["Das Forum <span style=\"color:grey;\">(Webseite)</span>", $B]);
		$T->addRowEvent("click", "window.open('https://forum.furtmeier.it/');");
		
		$T->addRow(["E-Mail-Anfrage stellen <span style=\"color:grey;\">(Webseite)</span>", $B]);
		$T->addRowEvent("click", "window.open('https://www.open3a.de/page-Kontakt');");
		
		if(!isset($data->hotline)){
			$T->addRow(["Hotline-Zeiten und Rufnummer <span style=\"color:grey;\">(Webseite)</span>", $B]);
			$T->addRowEvent("click", "window.open('https://www.open3a.de/page-Support');");

			$T->addRow(["AnyDesk herunterladen", $B]);
			$T->addRowEvent("click", OnEvent::popup("AnyDesk herunterladen", "Desktop", "-1", "popupAnyDesk"));
		}
		
		$html .= $this->spell(new Button("Unterstützung", "hilfe", "icon"), "Unterstützung", $T);
		
		#https://www.open3a.de/page-Abo
		
		if(isset($data->webinare) AND count($data->webinare)){
			$B = new Button("Öffnen", "arrow_right", "iconicG");

			$T = new HTMLTable(3);
			$T->setColWidth(3, 20);
			$T->addColStyle(2, "text-align:right;color:grey;");
			$T->useForSelection(false);
			$T->weight("lightlyColored");

			foreach($data->webinare AS $item){
				if($item->date < time())
					continue;
				
				$T->addRow([
					$item->title,
					Util::CLDateParser($item->date).", ".Util::CLTimeParser($item->time)." Uhr",
					$B
				]);
				$T->addRowEvent("click", "window.open('$item->link');");
			}
			
			$html .= $this->spell(new Button("Webinare", "wand", "icon"), "Webinare", $T);
		}
		
		if(Environment::getS("blogShow", "1") != "0" AND Environment::getS("blogRSSURL", null) !== null){
			
			if(function_exists("curl_init")){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
				curl_setopt($ch, CURLOPT_URL, Environment::getS("blogRSSURL", ""));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$dataRSS = curl_exec($ch);
				curl_close ($ch);
			} else {
				$ctx = stream_context_create(array('https' => array('timeout' => 1)));
				$dataRSS = file_get_contents(Environment::getS("blogRSSURL", ""), 0, $ctx);
			}
			$data->blog = [];
			
			try {
				$XML = new SimpleXMLElement($dataRSS);

				$i = 0;
				foreach($XML->channel->item AS $item){
					$it = new stdClass();
					$it->title = $item->title;
					$it->date = strtotime($item->pubDate);
					$it->link = $item->link;

					$i++;
					$data->blog[] = $it;

					if($i == 7)
						break;
					
				}
				
			} catch (Exception $e){}
		}
		
		if(Environment::getS("blogShow", "1") != "0" AND isset($data->blog)){
			
			$B = new Button("Öffnen", "arrow_right", "iconicG");

			$T = new HTMLTable(2);
			$T->setColWidth(2, 20);
			$T->useForSelection(false);
			$T->weight("lightlyColored");

			foreach($data->blog AS $item){
				$T->addRow([
					$item->title,
					$B
				]);
				$T->addRowEvent("click", "window.open('$item->link');");
			}
			
			$html .= $this->spell(new Button("Blog", "blog", "icon"), Environment::getS("blogName", "open3A blog"), $T);/*.
			OnEvent::script(OnEvent::rme("ADesktop", "getOpen3ARSSHeaders", "", "function(t){ \$j('#blogContent').html(t.responseText); }"));*/
		}
		
		
		$CH = Util::getCloudHost();
		if(!$CH AND Phynx::customer() > 0 AND Phynx::abo() == "0"){
			$B = new Button("Mehr Info", "navigation");
			$B->style("float:right;margin-top:-27px;");
			$B->onclick("window.open('https://www.open3a.de/page-Abo');");
			
			$textAbo = "<div style=\"height:calc(187px - 7px - 7px);\">Ihnen gefällt open3A und Sie möchten etwas zurückgeben?<br>
				<br>
				Erzählen Sie doch Ihren Kollegen, Vorgesetzten und Kunden von open3A!<br> 
				<br>
				Oder unterstützen Sie open3A direkt mit einem Abo und Sie erhalten - zusätzlich zur immer aktuellen Version - <strong>16% Rabatt!</strong> 
				auf Ihre verwendeten Pakete und Plugins.</div>$B";
			
			$html .= $this->spell(new Button("Cloud", "support", "icon"), "Abo", $textAbo);
		}
		
		
		
		if(Session::isPluginLoaded("mShop") AND Phynx::customer() == "0"){
			$B = new Button("Zum Shop", "navigation");
			$B->loadPlugin("contentScreen", "mShop");
			$B->style("float:right;margin-top:-27px;");
			
			$T = "<div style=\"height:calc(187px - 7px - 7px);\">open3A ist eine Software mit vielen Möglichkeiten. Im Shop finden Sie zahlreiche Erweiterungen, die Ihre tägliche Arbeit vereinfachen. Es gibt zwei Arten:
				<ul>
					<li>Plugins - Ein Plugin fügt meist einen ganzen neuen Reiter hinzu, zum Beispiel Statistiken oder Verträge.</li>
					<li>Customizer - Kleine Erweiterungen, wie etwa für das Sortieren von Positionen oder andere Belegarten.</li>
				</ul></div>$B";
			$html .= $this->spell(new Button("Erweiterungen", "./ubiquitous/Shop/Shop.png", "icon"), "Erweiterungen", $T);
		}
		
		
		if(isset($data->hotline)){
			$T = new HTMLTable(3);
			#$T->setColWidth(3, 20);
			$T->addColStyle(2, "text-align:right;");
			$T->addColStyle(3, "text-align:right;");
			$T->weight("lightlyColored");
			#$T->useForSelection(false);
			
			foreach($data->hotline->times AS $hTime){
				$D = new Datum($hTime->start);
				$D->addDay();
				if($D->time() < time())
					continue;
				
				if($hTime->isHoliday){
					$T->addRow([
						"Urlaub: ".Util::CLDateParser($hTime->start)." bis ".Util::CLDateParser($hTime->end)
					]);
					$T->addRowColspan(1, 3);
				} else {
					$T->addRow([
						Util::CLWeekdayName(date("w", $hTime->start)),
						Util::CLDateParser($hTime->start),
						Util::CLTimeParser($hTime->timeStart)." bis ".Util::CLTimeParser($hTime->timeEnd)." Uhr"
					]);
					
					$DC = new Datum();
					$DC->normalize();
					
					if($DC->time() == $hTime->start AND date("Hi") > date("Hi", $hTime->timeStart - 3600) AND date("Hi") < date("Hi", $hTime->timeEnd - 3600))
						$T->addRowClass ("confirm");
				}
				$T->addCellStyle(1, "height:22px;");
			}
			
			$B = new Button("AnyDesk\nherunterladen", "./images/AnyDeskLogo.png");
			$B->popup("", "AnyDesk herunterladen", "Desktop", -1, "popupAnyDesk");
			$B->style("float:right;margin-top:-27px;");
			
			$html .= $this->spell(new Button("Hotline", "hotline", "icon"), "Hotline", "<div style=\"height:calc(187px - 7px - 7px);\"><p class=\"prettySubtitle\" style=\"text-align:center;padding:12px;\"><a style=\"text-decoration:none;\" href=\"tel:".$data->hotline->number."\">".$data->hotline->number."</a></p>".$T."</div>".$B);
		}
		
		
		$noBM = mUserdata::getGlobalSettingValue("disableBackupManager", mUserdata::getUDValueS("noBackupManager", false));
		if(!$noBM){
			$htmlBackup = "";
			$F = new File(BackupManagerGUI::getBackupDir());

			if(!$F->A("FileIsWritable")) 
				$htmlBackup .= "
					<div class=\"dashboardButton\" style=\"margin-bottom:5px;\" onclick=\"contentManager.rmePCR('BackupManager', '', 'getWindow', '', 'Popup.displayNamed(\'BackupManagerGUI\',\'Backup-Manager\',transport);');\">
						<img style=\"float:right;margin-left:30px;\" src=\"./images/big/warnung.png\" />
						<p style=\"font-size:1.2em;font-weight:bold;color:#999999;\">
						open3A kann keine Sicherungen Ihrer Datenbank erstellen! <br>
						Klicken Sie hier für weitere Informationen.</p>
					</div>";

			$backedUp = BackupManagerGUI::checkForTodaysBackup();
			if($F->A("FileIsWritable") AND !$backedUp)
				$htmlBackup .= "
					<div class=\"dashboardButton\" style=\"margin-bottom:5px;\" onclick=\"contentManager.rmePCR('BackupManager', '', 'getWindow', '', 'Popup.displayNamed(\'BackupManagerGUI\',\'Backup-Manager\',transport);');\">
						<img style=\"float:right;margin-left:30px;\" src=\"./images/big/notice.png\" />
						<p style=\"font-size:1.2em;font-weight:bold;color:#999999;\">".T::_("Klicken Sie hier, um das tägliche Backup der Datenbank anzulegen.")."</p>
					</div>";

			if($F->A("FileIsWritable") AND $backedUp)
				$htmlBackup .= "
					<div class=\"dashboardButton\" style=\"margin-bottom:5px;height:auto;min-height:0px;\" onclick=\"contentManager.rmePCR('BackupManager', '', 'getWindow', '1', 'Popup.displayNamed(\'BackupManagerGUI\',\'Backup-Manager\',transport);');\">
						<p style=\"font-size:1.2em;font-weight:bold;color:#999999;\">".T::_("Ein neues Backup der Datenbank anlegen.")."</p>
					</div>";
			
			$BM = new BackupManagerGUI();
			$data = $BM->getBackupsList();
			$T = new HTMLTable(3);
			$T->addColStyle(2, "text-align:right;");
			$T->setColWidth(2, 80);
			$T->setColWidth(3, 20);
			$T->weight("lightlyColored");
			
			$B = new Button("", "check", "iconicG");
			$i = 0;
			foreach ($data as $name => $size) {
				if($i >= 3)
					break;
				
				
				$T->addRow([
					$name, 
					Util::formatByte($size,2),
					$B
				]);
				
				if($name == basename(BackupManagerGUI::getNewBackupName())) 
					$T->addRowClass ("confirm");
				
				$i++;
			}
			
			$html .= $this->spell(new Button("Datensicherung", "disk", "icon"), "Datensicherung", $htmlBackup.$T);
		}
		
				
		$html .= $this->spell(new Button("Version", "version", "icon"), "Version", "<span id=\"versionContent\"></span>").
		OnEvent::script(OnEvent::rme("ADesktop", "getOpen3AVersion", "", "function(t){ \$j('#versionContent').html(t.responseText); }"));
		
		
		$BR = "";
		if(Session::isPluginLoaded("mWebAuth")){
			$BR = new Button("WebAuth-Token\nregistrieren", "./plugins/WebAuth/WebAuth.png");
			$BR->onclick("WebAuth.newregistration(function(){".OnEvent::reload("Screen")."});");
			
			$U = new User(Session::currentUser()->getID());
			if($U->A("UserWebAuthCredentials") != ""){
				$BR = new Button("WebAuth-Token\nlöschen", "./plugins/WebAuth/lock_break.png");
				$BR->rmePCR("Spellbook", "-1", "clearToken", "", OnEvent::reload("Screen"));
			}
			#$BR->style("margin:10px;margin-left:0;");
			$BR->style("float:left;margin-top:-27px;");
			
		}
		
		$BP = new Button("Passwort\nändern", "refresh");
		$BP->style("margin:10px;margin-left:0;");
		$BP->popup("", "Passwort ändern", "Spellbook", "-1", "changePasswordPopup");
		$BP->style("float:right;margin-top:-27px;");
		
		$T = new HTMLTable(2);
		$T->weight("light");
		$T->setColWidth(1, 120);
		
		$T->addRow(["Benutzername:", Session::currentUser()->A("username")]);
		$T->addRow(["E-Mail-Adresse:", (Session::currentUser()->A("UserEmail") != "" ? Session::currentUser()->A("UserEmail") : "Nicht hinterlegt")]);
		$T->addRow(["Telefon:", (Session::currentUser()->A("UserTel") != "" ? Session::currentUser()->A("UserTel") : "Nicht hinterlegt")]);
		$T->addRow(["Position:", (Session::currentUser()->A("UserPosition") != "" ? Session::currentUser()->A("UserPosition") : "Nicht hinterlegt")]);
		
		$html .= $this->spell(new Button("Benutzer", "users", "icon"), "Benutzer", "<div style=\"height:calc(187px - 7px - 7px);\"><p style=\"padding-top:12px;padding-bottom:12px;\" class=\"prettySubtitle\">".Session::currentUser()->A("name")."</p> 
			 ".$T."</div>
				$BP$BR");
		
		
		try {
			$sk = mUserdata::getUDValueS("phynxColor", "standard");
		} catch (Exception $e){
			$sk = "standard";
		}
		
		$default = $this->getColors("standard");
		
		$T = new HTMLTable(5);
		$T->useForSelection(false);
		$T->setColWidth(1, 20);
		$T->setColWidth(3, 40);
		$T->setColWidth(4, 40);
		$T->setColWidth(5, 40);
		$T->weight("lightlyColored");
		$fp = opendir("../styles/");
		while(($file = readdir($fp)) !== false) {
			if($file[0] == ".") 
				continue;
			
			if(!is_dir("../styles/$file")) 
				continue;
			
			if($file == "tinymce")
				continue;
			if($file == "darkMode")
				continue;
			if($file == "future")
				continue;
			
			$label = ucfirst($file);
			if($file == "yellow")
				$label = ucfirst(T::_ ("gelb"));
			
			if($file == "grey")
				$label = ucfirst(T::_ ("grau"));
			
			if($file == "blue")
				$label = ucfirst(T::_ ("blau"));
			
			if($file == "green")
				$label = ucfirst(T::_ ("grün"));
			
			if($file == "lightBlue")
				$label = ucfirst(T::_ ("hellblau"));
			
			#if($file == "future")
			#	$label = ucfirst(T::_ ("weiß"));
			
			$matches = $this->getColors($file);
			
			$B = new Button("", "./images/i2/empty.png", "icon");
			
			if($sk == $file)
				$B = new Button("", "check", "iconicG");
			
			$T->addRow([
				$B,
				$label,
				"&nbsp;",
				"&nbsp;",
				"&nbsp;"
			]);
			
			if($sk == $file)
				$T->addRowClass ("backgroundColor1");
			
			$T->addRowEvent("click", OnEvent::rme("Colors", "saveContextMenu", ["1", "'$file'"], "function(){ ".OnEvent::frame("Screen", "Desktop")." Interface.setup();}"));
			
			for($i = 1; $i < 4; $i++)
				$T->addCellStyle($i + 2, "background-color:".(isset($matches[$i]) ? $matches[$i] : $default[$i]));
			
			
			#$kal[$file] = $label;
		}
		
		#$html .= "<div class=\"backgroundColor4\" style=\"padding:10px;\">";
		$html .= $this->spell(new Button("Farben", "farben", "icon"), "Farben", $T, true);
		
		
		
		$sk2 = mUserdata::getUDValueS("phynxLayout", "horizontal");
		$layouts = ["horizontal" => "Horizontal", "vertical" => "Vertikal", "desktop" => "Desktop", "fixed" => "Fixiert"];
		
		$T = new HTMLTable(2);
		$T->useForSelection(false);
		$T->setColWidth(1, 20);
		$T->weight("lightlyColored");
		
		foreach($layouts AS $k => $v){
			$B = new Button("", "./images/i2/empty.png", "icon");
			
			if($sk2 == $k)
				$B = new Button("", "check", "iconicG");
			
			$T->addRow([
				$B,
				$v
			]);
			
			if($sk2 == $k)
				$T->addRowClass ("backgroundColor1");
			
			$T->addRowEvent("click", OnEvent::rme("Colors", "saveContextMenu", ["2", "'$k'"], "function(){ ".OnEvent::frame("Screen", "Desktop")." Interface.setup();}"));
		}
		
	
		$html .= $this->spell(new Button("Layout", "theme", "icon"), "Layout", $T, true);
		
		$sk2 = mUserdata::getUDValueS("noAutoLogout", "false");
		$values = ["false" => "Ja", "true" => "Nein"];
		
		$T = new HTMLTable(2);
		$T->useForSelection(false);
		$T->setColWidth(1, 20);
		$T->weight("lightlyColored");
		
		foreach($values AS $k => $v){
			$B = new Button("", "./images/i2/empty.png", "icon");
		
			if($sk2 == $k)
				$B = new Button("", "check", "iconicG");
			
			$T->addRow([
				$B,
				$v
			]);
			
			if($sk2 == $k)
				$T->addRowClass ("backgroundColor1");
			
			$T->addRowEvent("click", OnEvent::rme("Colors", "saveContextMenu", ["3", "'$k'"], "function(){ if(confirm('Achtung: Die Anwendung muss neu geladen werden, damit die Einstellungen wirksam werden. Jetzt neu laden?')) document.location.reload(); }"));
		}
		
		$html .= $this->spell(new Button("Einstellungen", "system", "icon"), "Einstellungen", "<p>Automatisch abmelden?</p>".$T, true);
		
		
		$iconSet = mUserdata::getUDValueS("phynxIcons", "default");
		
		$N = new stdClass();
		$N->label = "Standard";
		$N->rel = "";
		$N->examples = ["address", "rechnung", "index"];
		
		$values = ["default" => $N];
		$hasIconSet = false;
		while($return = Registry::callNext("IconSet")){
			foreach($return AS $k => $v)
				$values[$k] = $v;
			
			$hasIconSet = true;
		}
		
		$T = new HTMLTable(3);
		$T->useForSelection(false);
		$T->setColWidth(1, 20);
		$T->setColWidth(3, 130);
		$T->addColStyle(3, "text-align:center;");
		$T->weight("lightlyColored");
		foreach($values AS $k => $v){
			$B = new Button("", "./images/i2/empty.png", "icon");

			$Bs = [];
			foreach($v->examples AS $ks => $exFile){
				$rel = $v->rel;
				if($v->base != "" AND !file_exists($v->folder.$exFile))
					$rel = $values[$v->base]->rel;
				
				$B0 = new Button("", $rel.$exFile, "icon");
				$B0->style("width:32px;height:32px;".($ks < 2 ? "margin-right:10px;" : ""));
				$B0->useCustom(false);
				
				$Bs[] = $B0;
			}
			
			
			if($iconSet == $k)
				$B = new Button("", "check", "iconicG");
			
			$T->addRow([$B, $v->label, implode("", $Bs)]);
			
			if($iconSet == $k)
				$T->addRowClass ("backgroundColor1");
			
			$T->addRowEvent("click", OnEvent::rme("Colors", "saveContextMenu", ["4", "'$k'"], "function(){ ".OnEvent::frame("Screen", "Desktop")." Menu.refresh();}"));
		}
		
		if($hasIconSet)
			$html .= $this->spell(new Button("Symbole", "symbole", "icon"), "Symbole", $T, true);
		
		#$html .= "<div style=\"clear:both;\"></div>";
		
		$html .= "</div>";
		return $html.OnEvent::script(OnEvent::rme("ADesktop", "dataUpdate"));/*.OnEvent::script("  \$j( function() { \$j( '.SpellbookContainer' ).sortable({
      placeholder: 'highlight'
    });  \$j( '.SpellbookContainer' ).disableSelection(); } );");*/
	}

	public function popupAnyDesk(){
		$BWin = new Button("Windows", "./images/download_for_windows.png");
		$BWin->onclick("document.location.href='https://get.anydesk.com/UMPofNFa/AnyDesk.exe'");
		$BWin->style("margin:10px;");
		if(Util::getOS() == "Windows_64" OR Util::getOS() == "Windows_32")
			$BWin->addClass("confirm");
		
		$BLinux64 = new Button("Linux\n64 Bit", "./images/download_for_linux.png");
		$BLinux64->onclick("document.location.href='https://get.anydesk.com/4e1wED5Q/AnyDesk.tar.gz'");
		$BLinux64->style("margin:10px;");
		if(Util::getOS() == "Linux_64")
			$BLinux64->addClass("confirm");
		
		$BLinux32 = new Button("Linux\n32 Bit", "./images/download_for_linux.png");
		$BLinux32->onclick("document.location.href='https://get.anydesk.com/ihOg9zzZ/AnyDesk.tar.gz'");
		$BLinux32->style("margin:10px;");
		if(Util::getOS() == "Linux_32")
			$BLinux32->addClass("confirm");
		
		$BMac = new Button("Mac OS", "./images/download_for_mac.png");
		$BMac->onclick("document.location.href='https://get.anydesk.com/4URqWuYU/AnyDesk.dmg'");
		$BMac->style("margin:10px;");
		if(Util::getOS() == "MacOS_64")
			$BMac->addClass("confirm");
		
		echo '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 300 55.4" style="enable-background:new 0 0 300 55.4;margin:20px;" xml:space="preserve">
<style type="text/css">
	.st0{fill:#EF443B;}
</style>
<polygon class="st0" points="46.6,0 41.1,5.5 60.4,24.7 41.1,44 46.6,49.4 71.3,24.7 "/>
<rect x="7.2" y="7.2" transform="matrix(-0.7071 -0.7071 0.7071 -0.7071 24.7138 59.6646)" class="st0" width="35" height="35"/>
<g>
	<path d="M106.3,43.1l-2.7-8.7H90.3l-2.7,8.7h-8.4L92.2,6.3h9.5l13,36.8H106.3z M101.8,27.9C99.3,20,98,15.5,97.7,14.5   S97.1,12.6,97,12c-0.6,2.1-2.1,7.4-4.7,15.9H101.8z"/>
	<path d="M144.5,43.1h-7.6V26.8c0-2-0.4-3.5-1.1-4.5s-1.9-1.5-3.4-1.5c-2.1,0-3.7,0.7-4.6,2.1s-1.4,3.8-1.4,7.1v13.2h-7.6v-28h5.8   l1,3.6h0.4c0.9-1.4,2-2.4,3.5-3.1c1.5-0.7,3.2-1,5.1-1c3.3,0,5.7,0.9,7.4,2.6s2.5,4.3,2.5,7.6V43.1z"/>
	<path d="M147.6,15.1h8.4l5.3,15.8c0.5,1.4,0.8,3,0.9,4.9h0.2c0.2-1.7,0.5-3.3,1.1-4.9l5.2-15.8h8.2L165,46.7   c-1.1,2.9-2.6,5.1-4.6,6.6c-2,1.5-4.4,2.2-7.1,2.2c-1.3,0-2.6-0.1-3.9-0.4v-6.1c0.9,0.2,1.9,0.3,3,0.3c1.4,0,2.5-0.4,3.5-1.2   c1-0.8,1.8-2.1,2.4-3.7l0.5-1.4L147.6,15.1z"/>
	<path d="M211.8,24.4c0,6-1.7,10.6-5.1,13.9s-8.4,4.8-14.9,4.8h-10.4V6.5h11.5c6,0,10.6,1.6,13.9,4.7   C210.1,14.4,211.8,18.8,211.8,24.4z M203.7,24.6c0-7.9-3.5-11.8-10.4-11.8h-4.1v23.8h3.3C200,36.7,203.7,32.7,203.7,24.6z"/>
	<path d="M230.2,43.6c-4.5,0-8-1.2-10.6-3.7c-2.5-2.5-3.8-6-3.8-10.6c0-4.7,1.2-8.3,3.5-10.9c2.3-2.6,5.6-3.8,9.7-3.8   c4,0,7,1.1,9.2,3.4c2.2,2.3,3.3,5.4,3.3,9.3V31h-18.1c0.1,2.2,0.7,3.9,1.9,5.1c1.2,1.2,2.9,1.8,5.1,1.8c1.7,0,3.3-0.2,4.8-0.5   c1.5-0.4,3.1-0.9,4.7-1.7v5.9c-1.3,0.7-2.8,1.2-4.3,1.5C234.2,43.4,232.4,43.6,230.2,43.6z M229.1,20c-1.6,0-2.9,0.5-3.8,1.5   c-0.9,1-1.4,2.5-1.6,4.4h10.7c0-1.9-0.5-3.3-1.5-4.4C232,20.5,230.7,20,229.1,20z"/>
	<path d="M267.4,34.8c0,2.9-1,5.1-3,6.6c-2,1.5-5,2.3-9,2.3c-2,0-3.8-0.1-5.2-0.4c-1.4-0.3-2.8-0.7-4-1.2v-6.3   c1.4,0.7,3,1.2,4.8,1.7c1.8,0.5,3.3,0.7,4.7,0.7c2.8,0,4.2-0.8,4.2-2.4c0-0.6-0.2-1.1-0.6-1.5c-0.4-0.4-1-0.8-1.9-1.3   c-0.9-0.5-2.1-1-3.6-1.7c-2.2-0.9-3.7-1.7-4.7-2.5c-1-0.8-1.7-1.6-2.2-2.6c-0.5-1-0.7-2.2-0.7-3.7c0-2.5,1-4.4,2.9-5.8   c1.9-1.4,4.7-2,8.2-2c3.4,0,6.7,0.7,9.8,2.2l-2.3,5.5c-1.4-0.6-2.7-1.1-3.9-1.5c-1.2-0.4-2.5-0.6-3.7-0.6c-2.3,0-3.4,0.6-3.4,1.8   c0,0.7,0.4,1.3,1.1,1.8c0.7,0.5,2.3,1.2,4.8,2.2c2.2,0.9,3.8,1.7,4.8,2.5c1,0.8,1.8,1.7,2.3,2.7C267.1,32.2,267.4,33.4,267.4,34.8z   "/>
	<path d="M279.5,27.9l3.3-4.3l7.8-8.5h8.6l-11.1,12.1L300,43.1h-8.8l-8.1-11.3l-3.3,2.6v8.7h-7.6v-39h7.6v17.4l-0.4,6.4H279.5z"/>
</g>
</svg>
<p>Hier erhalten Sie die Fernwartungssoftware AnyDesk für unterschiedliche Betriebssysteme.</p>
				<p>Bitte laden Sie die Datei für Ihr Betriebssystem herunter und führen Sie sie anschließend aus. Um eine Verbindung herzustellen, geben Sie bitte die angezeigte Nummer der Hotline durch.</p>
				<div style="text-align:center;padding-bottom:2em;">'.$BLinux64.$BLinux32.$BWin.$BMac.'</div>';
	}
	
	private function spell($B, $title, $content, $lessImportant = false){
		$B->style("float:left;margin-right:10px;margin-top:-7px;margin-left:-5px;width:32px;height:32px;");
		
		return "<div style=\"\" class=\"SpellbookSpell\">
			<div style=\"margin:10px;\" class=\"borderColor1 spell backgroundColor0\">
				<div class=\"backgroundColor2\" style=\"padding:10px;padding-bottom:5px;".($lessImportant ? "background-color:#CCC;" : "")."\">
					$B<h2 style=\"margin-bottom:0px;margin-top:0px;\">$title</h2>
				</div>
				<div style=\"padding:7px;height:187px;overflow:auto;\" class=\"SpellbookDescription SpellbookKeepDescription\">
					$content
				</div>
			</div>
		</div>";
	}
	
	private function getColors($style){
		$css = file_get_contents(Util::getRootPath()."styles/$style/colors.css");

		preg_match_all("/\.backgroundColor([0-9])\s+{\s+background-color:([#0-9ABCDEFabcdef]+);/", $css, $matches);
		
		$colors = [];
		foreach($matches[1] AS $k => $bgcNum)
			$colors[$bgcNum] = $matches[2][$k];
		
		
		#echo "<pre>";
		#echo $style."\n";
		#print_r($colors);
		#echo "</pre>";
		return $colors;
	}
}
?>