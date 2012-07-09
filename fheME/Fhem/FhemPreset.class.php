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
class FhemPreset extends PersistentObject {
	public function activate(){

		/*$ac = new anyC();
		$ac->setCollectionOf("FhemPreset");
		$ac->addJoinV3("FhemServer","FhemPresetServerID","=","FhemServerID");
		$ac->addAssocV3("FhemPresetID", "=", $PresetID);
		$S = $ac->getNextEntry();*/

		$S = new FhemServer($this->A("FhemPresetServerID"));

		switch($S->A("FhemServerType")){
			case "0":
				try {
					$T = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
				} catch(NoServerConnectionException $e){
					die("error:'The connection to the server with IP-address ".$S->A("FhemServerIP")." could not be established!'");
				}
				$c = "set ".$this->A("FhemPresetName")." on";
				$T->fireAndForget($c);
				$T->disconnect();
			break;

			case "1":
				$ac = new anyC();
				$ac->setCollectionOf("FhemEvent");
				$ac->addAssocV3("FhemEventPresetID", "=", $this->ID);
				$ac->lCV3();

				while($E = $ac->getNextEntry())
					$S->setDevice($E->A("FhemEventFhemID"), $E->A("FhemEventAction"));

			break;
		}
	}
}
?>
