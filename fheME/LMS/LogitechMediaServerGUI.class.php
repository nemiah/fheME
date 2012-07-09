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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class LogitechMediaServerGUI extends LogitechMediaServer implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Logitech Media Server");
	
		$gui->type("LogitechMediaServerPlayers", "textarea");
		$gui->inputStyle("LogitechMediaServerPlayers", "font-size:10px;height:150px;");
		
		$gui->space("LogitechMediaServerPlayers");
		
		$B = $gui->addSideButton("Player\nherunterladen", "./fheME/LMS/downloadPlayers.png");
		$B->popup("", "Player herunterladen", "LogitechMediaServer", $this->getID(), "downloadPlayers");
		
		$B = $gui->addSideButton("Player\nSteuerung", "./fheME/LMS/controlPlayer.png");
		$B->popup("", "Player Steuerung", "LogitechMediaServer", $this->getID(), "playerControls", "1");
		
		return $gui->getEditHTML();
	}
	
	public function downloadPlayers(){
		echo "<p>Verbinde zu ".$this->A("LogitechMediaServerIP").":9090...</p>";
		$T = $this->getConnection();
		
		$P = "";
		$player = $this->getPlayers($T);
		foreach($player AS $k => $v){
			$player[$k] = $k." ".$v;
			$P .= "$k;#;$v\n";
		}
		
		$this->changeA("LogitechMediaServerPlayers", trim($P));
		$this->saveMe();
		
		$L = new HTMLList();
		$L->addItems($player);
		
		echo $L.OnEvent::script(OnEvent::reload("Left"));
		
		$T->disconnect();
	}
	
	public function getPlayers($T = null){
		if($T == null)
			$T = $this->getConnection();
		
		$return = $T->fireAndGet("player count ?");
		
		$return = str_replace("player count ", "", $return);
		
		$players = array();
		for($i = 0; $i < $return; $i++){
			$id = urldecode(str_replace("player id $i ", "", $T->fireAndGet("player id $i ?")));
			
			$players[$id] = urldecode(str_replace("player name $i ", "", $T->fireAndGet("player name $i ?")));
		}
		
		return $players;
	}
	
	public function getConnection(){
		$T = new Telnet($this->A("LogitechMediaServerIP"), 9090);
		$T->setPrompt("\n");
		
		return $T;
	}
}
?>