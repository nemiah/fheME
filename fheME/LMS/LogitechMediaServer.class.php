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
class LogitechMediaServer extends PersistentObject {
	
	public function playerControls($echo = false){
		
		$I = new HTMLInput("LMSPlayerID", "select", "0", $this->players());
		$I->id("LMSPlayerID".$this->getID());
		#$I->style("margin:10px;");
				
		$BP = new Button("Play", "./fheME/LMS/controlPlay.png");
		$BP->style("margin-top:10px;width:100px;background-position:65px 50%;");
		$BP->rmePCR("LogitechMediaServer", $this->getID(), "play", array("$('LMSPlayerID".$this->getID()."').value"));
		
		$BS = new Button("Stop", "./fheME/LMS/controlStop.png");
		$BS->style("margin-right:10px;margin-top:10px;width:100px;background-position:65px 50%;");
		$BS->rmePCR("LogitechMediaServer", $this->getID(), "stop", array("$('LMSPlayerID".$this->getID()."').value"));
		
		$T = $I.$BS.$BP;
		if($echo)
			echo $T;
		
		return $T;
	}
	
	public function play($playerID){
		$T = $this->getConnection();
		
		$T->fireAndForget(urlencode($playerID)." play");
		
		$T->disconnect();
	}
	
	public function stop($playerID){
		$T = $this->getConnection();
		
		$T->fireAndForget(urlencode($playerID)." stop");
		
		$T->disconnect();
	}
	
	public function players(){
		$P = $this->A("LogitechMediaServerPlayers");
		$P = explode("\n", $P);
		
		$Ps = array();
		foreach ($P AS $v){
			$e = explode(";#;", $v);
			$Ps[$e[0]] = $e[1];
		}
		
		return $Ps;
	}
}
?>