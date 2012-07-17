<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007, 2008, 2009, 2010, 2011, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class MailCheckGUI extends MailCheck implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("MailCheck");
	
		$gui->type("MailCheckPassword", "password");
		$gui->type("MailCheckProtocol", "select", array("imap" => "imap", "pop3" => "pop3"));
		
		$gui->descriptionField("MailCheckPort", "POP3: 110, IMAP: 143");
		
		$B = $gui->addSideButton("Mails\nprüfen", "./fheME/MailCheck/check.png");
		$B->popup("", "Mails prüfen", "MailCheck", $this->getID(), "check");
		
		return $gui->getEditHTML();
	}
	
	public function check($touch = false){
		$mbox = imap_open("{".$this->A("MailCheckServer").":".$this->A("MailCheckPort")."/".$this->A("MailCheckProtocol")."}INBOX", $this->A("MailCheckUsername"), $this->A("MailCheckPassword"));
		if(!$mbox)
			die("<p style=\"color:red;\">".imap_last_error()."</p>");

		#echo "<h1>Nachrichten in INBOX</h1><div style=\"overflow:auto;max-height:400px;\"><pre>";
		$MC = imap_check($mbox);

		$T = new HTMLTable(1, $touch ? "Mails" : "");
		$T->setTableStyle("font-size:10px;");
		
		$start = $MC->Nmsgs - 10;
		if($start < 1)
			$start = 1;
		
		$result = imap_fetch_overview($mbox,"$start:{$MC->Nmsgs}",0);
		$result = array_reverse($result);
		foreach ($result as $overview) {
			#print_r($overview);
			$T->addRow(array("<small style=\"color:grey;float:right;\">".Util::CLDateParser($overview->udate)."</small>".$this->decodeBlubb($overview->from)."<br /><small style=\"color:grey;\">".($this->decodeBlubb($overview->subject))."</small>"));
		}
		imap_close($mbox);
		#echo "</pre></div>";
		
		if($touch){
			
			$BC = new Button("Popup schließen", "stop", "icon");
			$BC->style("float:right;margin:10px;");
			$BC->onclick(OnEvent::closePopup("MailCheck"));
			
			echo $BC;
			
		}
		
		echo "<p>$MC->Nmsgs Nachricht".($MC->Nmsgs == 1 ? "" : "en")."</p><div style=\"clear:both;\"></div>";
		
		echo $T;
	}

	private function decodeBlubb($text){
		$textDecode = imap_mime_header_decode($text);
		$decoded = "";
		foreach ($textDecode AS $k => $S){
			if($S->charset == "default"){
				$decoded .= $S->text;
				continue;
			}

			$decoded .= mb_convert_encoding($S->text, "UTF-8", $S->charset);
		}

		return stripslashes($decoded);
	}
}
?>