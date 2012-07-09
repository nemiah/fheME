<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */


/**
 * Please load this class with the ExtConn class.
 * It will not work otherwise.
 */
class ExtConnFhem {
	public function __construct($absolutePathToPhynx) {
		#require_once($absolutePathToPhynx."open3A/Vertrag/Vertrag.class.php");
		require_once($absolutePathToPhynx."fheME/Fhem/FhemPreset.class.php");
		require_once($absolutePathToPhynx."fheME/Fhem/FhemServer.class.php");
		require_once($absolutePathToPhynx."fheME/Fhem/Telnet.class.php");
		require_once($absolutePathToPhynx."fheME/FhemLocation/FhemLocation.class.php");
	}

	function getDevices(){
		return array("hi1", "hi2");
	}

	function getPresets($FhemPresetLocationID = null){
		$ac = new anyC();
		$ac->setCollectionOf("FhemPreset");
		$ac->addAssocV3("FhemPresetHide","=","0");

		if($FhemPresetLocationID != null)
			$ac->addAssocV3("FhemPresetLocationID", "=", $FhemPresetLocationID);

		$presets = array();

		while($p = $ac->getNextEntry())
			$presets[] = $p->A("FhemPresetName");

		return $presets;
	}

	function setPreset($PresetName){
		$AC = new anyC();
		$AC->setCollectionOf("FhemPreset");
		$AC->addAssocV3("FhemPresetName", "=", $PresetName);

		$P = $AC->getNextEntry();

		$S = new FhemPreset($P->getID());
		$S->activate();

		return true;
	}

	function getLocations(){
		$AC = new anyC();
		$AC->setCollectionOf("FhemLocation");

		$Locations = array();

		while($L = $AC->getNextEntry())
			$Locations[] = $L->getID()."::".$L->A("FhemLocationName");

		return $Locations;
	}
}
?>
