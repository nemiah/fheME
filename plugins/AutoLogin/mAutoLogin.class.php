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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mAutoLogin extends anyC {
	function __construct(){
		$this->setCollectionOf("AutoLogin");
	}
	
	public static function doAutoLogin($username = null, $SHAPassword = null, $language = null, $AutoLoginApp = null){
		if($username == null){
			$al = new mAutoLogin();
			$al->addAssocV3("AutoLoginIP", "=", $_SERVER["REMOTE_ADDR"]);
			$al->addAssocV3("AutoLoginIP", "=", "*", "OR");
			$al->addJoinV3("User","AutoLoginUserID","=","UserID");
			try {
				$c = $al->getNextEntry();
			} catch (Exception $e){
				$c = null;
			}

			if($c == null) return;

			$username = $c->getA()->username;
			$SHAPassword= $c->getA()->SHApassword;
			$language = $c->getA()->language;
			if($AutoLoginApp == null)
				$AutoLoginApp = $c->getA()->AutoLoginApp;
		}

		ob_start();
		$d = array();
		$d["loginUsername"] = $username;
		$d["loginSHAPassword"] = $SHAPassword;
		$d["loginSprache"] = $language;
		$d["anwendung"] = $AutoLoginApp;
		$U = new UsersGUI();
		$U->doLogin($d);
		ob_end_clean();
	}
}
?>