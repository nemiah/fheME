<?php
/**
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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class ADesktopGUI extends UnpersistentClass implements iGUIHTML2 {
	function  __construct() {
		parent::__construct();
		$this->customize();
	}
	public function getHTML($id){
		switch($id){
			case "1":
				return "
				<script type=\"text/javascript\">
					if($('mInstallationMenuEntry')){
						$('contentLeft').update('');
						Popup.closeNonPersistent();
						contentManager.loadFrame('contentRight', 'mInstallation', -1, 0, 'mInstallationGUI;-');
						setHighLight($('mInstallationMenuEntry'));
					}
					else if($('mCloudMenuEntry')){
						$('contentLeft').update('');
						Popup.closeNonPersistent();
						contentManager.loadFrame('contentRight', 'mCloud', -1, 0, 'mCloudGUI;-');
						setHighLight($('mCloudMenuEntry'));
					}
				</script>";
			break;
			
			case "2":
				
			break;
		}
	}
}
?>