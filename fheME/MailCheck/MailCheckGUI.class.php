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
		$mbox = $this->connection();

		#echo "<h1>Nachrichten in INBOX</h1><div style=\"overflow:auto;max-height:400px;\"><pre>";
		$MC = imap_check($mbox);

		$T = new HTMLTable(1, $touch ? "Mails" : "");
		$T->setTableStyle("font-size:11px;");
		$T->useForSelection();
		
		$start = $MC->Nmsgs - 10;
		if($start < 1)
			$start = 1;
		
		$result = imap_fetch_overview($mbox,"$start:{$MC->Nmsgs}",0);
		$result = array_reverse($result);
		foreach ($result as $overview) {
			#print_r($overview);
			$T->addRow(array("
				<small style=\"color:grey;float:right;\">".Util::CLDateParser($overview->udate)."</small>
				".str_replace("\"", "", $this->decodeBlubb($overview->from))."<br />
				<small style=\"color:grey;\">".substr($this->decodeBlubb($overview->subject), 0, 50)."</small>"));
			$T->addCellEvent(1, "click", "\$j('#MailFrame').attr('src', './interface/rme.php?class=MailCheck&constructor=".$this->getID()."&method=showMailBody&parameters=\'$overview->uid\'');");
		}
		imap_close($mbox);
		#echo "</pre></div>";
		
		$BC = "";
		if($touch){
			$BC = new Button("Fenster\nschließen", "stop");
			$BC->style("float:right;margin:10px;");
			$BC->onclick(OnEvent::closePopup("MailCheck"));
		}
		

		echo "<div style=\"float:right;width:300px;\">";
		echo $BC;
		echo "<p>$MC->Nmsgs Nachricht".($MC->Nmsgs == 1 ? "" : "en")."</p><div style=\"clear:both;\"></div>";
		
		echo $T;
		echo "</div>";
		
		echo "
			<div style=\"border-right-style:solid;border-right-width:1px;width:699px;\" class=\"borderColor1\">
				<iframe id=\"MailFrame\" style=\"border:0px;width:699px;height:520px;\" src=\"./fheME/MailCheck/Home/index.html\"></iframe>
			</div>";
		echo "<div style=\"clear:both;\"></div>";
	}
	
	/*public function showMail($uid, $touch){
		if($touch){
			$BC = new Button("Fenster\nschließen", "stop");
			$BC->style("float:right;margin:10px;");
			$BC->onclick(OnEvent::closePopup("MailCheck", "showMail"));
			
			echo $BC;
		}
		
		echo "<iframe style=\"border:0px;width:830px;height:450px;\" src=\"./interface/rme.php?class=MailCheck&constructor=".$this->getID()."&method=showMailBody&parameters='$uid'\"></iframe>";
	}*/
	
	public function showMailBody($uid){
		$mbox = $this->connection();
		
		
		$body = trim($this->get_part($mbox, $uid, "TEXT/HTML"));
		
		if($body == ""){
			$text = trim($this->get_part($mbox, $uid, "TEXT/PLAIN"));
			echo "<!doctype html>
				<html class=\"noLetter\">
					<head>
					</head>
					<link rel=\"stylesheet\" type=\"text/css\" href=\"../fheME/MailCheck/email.css\">
					
					<body class=\"noLetter\">".preg_replace("/\n|\r\n/", "<br />", $text)."
					</body>
				</html>";
		} else
			echo $body;
	}

	private function connection(){
		$mbox = imap_open("{".$this->A("MailCheckServer").":".$this->A("MailCheckPort")."/".$this->A("MailCheckProtocol")."/novalidate-cert}INBOX", $this->A("MailCheckUsername"), $this->A("MailCheckPassword"));
		
		if(!$mbox)
			die("<p style=\"color:red;\">".imap_last_error()."</p>");
		
		return $mbox;
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

		return trim(stripslashes($decoded));
	}
	
	/**
	 * Thanks to http://www.linuxscope.net/articles/mailAttachmentsPHP.html
	 * 
	 * @param type $stream
	 * @param type $uid
	 * @param type $mime_type
	 * @param type $structure
	 * @param string $part_number
	 * @return boolean 
	 */
	function get_part($stream, $uid, $mime_type, $structure = false, $part_number = false) {
		
		if(!$structure)
			$structure = imap_fetchstructure($stream, $uid, FT_UID);
		
		if($structure) {
			if($mime_type == $this->get_mime_type($structure)) {
				if(!$part_number)
					$part_number = "1";
				
				$text = imap_fetchbody($stream, $uid, $part_number, FT_UID);
				
				$charset = "UTF7-IMAP";
				foreach($structure->parameters AS $k => $v)
					if(strtolower($v->attribute) == "charset")
						$charset = $v->value;
				
				if($structure->encoding == 3)
					$text = imap_base64($text);
				
				if($structure->encoding == 4)
					$text = imap_qprint($text);
				
				return mb_convert_encoding($text, "UTF-8", $charset);
				
			}

			if($structure->type == 1) /* multipart */ {
				$prefix = "";
				while(list($index, $sub_structure) = each($structure->parts)) {
					if($part_number)
						$prefix = $part_number . '.';
					
					$data = $this->get_part($stream, $uid, $mime_type, $sub_structure, $prefix . ($index + 1));
					if($data)
						return $data;
					
				}
			}
		}
		return false;
	}
	
	/**
	 * Thanks to http://www.linuxscope.net/articles/mailAttachmentsPHP.html
	 * 
	 * @param type $structure
	 * @return string 
	 */
	function get_mime_type($structure) {
		$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
		
		if($structure->subtype)
			return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
		
		return "TEXT/PLAIN";
	}
}
?>