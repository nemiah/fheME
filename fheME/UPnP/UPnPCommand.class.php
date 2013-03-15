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
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class UPnPCommand {
	private $Device;
	function __construct(UPnP $UPnP) {
		$this->Device = $UPnP;
	}
	
	private function makeURL($type){
		$url = parse_url($this->Device->A("UPnPLocation"));
		return $url["scheme"]."://".$url["host"].":".$url["port"].$this->Device->A("UPnP{$type}controlURL");
	}
	
	function Browse($ObjectID, $BrowseFlag) {
		$args = '<ObjectID>'.$ObjectID.'</ObjectID>' . "\r\n";
		$args .= '<BrowseFlag>'.$BrowseFlag.'</BrowseFlag>' . "\r\n";
		$args .= '<Filter>'.'</Filter>' . "\r\n";
		$args .= '<StartingIndex>0</StartingIndex>' . "\r\n";
		$args .= '<RequestedCount>0</RequestedCount>' . "\r\n";
		$args .= '<SortCriteria>'.'</SortCriteria>' . "\r\n";
		return array($args, "ContentDirectory", $this->makeURL("ContentDirectory"));
	}
	
	function Next() {
		$args = '<InstanceID>0</InstanceID>' . "\r\n";
		return array($args, "AVTransport", $this->makeURL("AVTransport"));
	}

	function Pause() {
		$args = '<InstanceID>0</InstanceID>' . "\r\n";
		return array($args, "AVTransport", $this->makeURL("AVTransport"));
	}

	function Play($prmSpeed = 1) {
		$args = '<InstanceID>0</InstanceID>' . "\r\n";
		$args .= '<Speed>' . $prmSpeed . '</Speed>' . "\r\n";
		return array($args, "AVTransport", $this->makeURL("AVTransport"));
	}

	function Stop() {
		$args = '<InstanceID>0</InstanceID>' . "\r\n";
		return array($args, "AVTransport", $this->makeURL("AVTransport"));
	}
}

?>
