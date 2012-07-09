<?php
/*
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
class FhemServer extends PersistentObject {
	public function setDevice($fhemID, $action){
		$F = new Fhem($fhemID);

		switch($this->A("FhemServerType")){
			case "1":
				if($fhemID != -1) $url = $this->A("FhemServerURL")."?device=".$F->A("FhemName")."&value=".$action;
				else $url = $this->A("FhemServerURL")."?value=".$action;
				fopen($url, "r");
			break;
		}
	}
}
?>
