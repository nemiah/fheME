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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
require "../system/connect.php";

class AppProvider {

	function test(){
		return "Test erfolgreich";
	}

	/*private function login($credentials){
		$U = new Users();
		$L = $U->getUser($credentials->username, $credentials->SHAPassword, true);

		if($L === null)
			throw new SoapFault("Server", "Credentials invalid");

		return $L;
	}*/

	function getApplications(){
		return Applications::getList();
	}

	function getServices(){
		return Services::getList();
	}
}

$S = new SoapServer(null, array('uri' => 'http://localhost/'.$_SERVER["SCRIPT_NAME"]));
$S->setClass('AppProvider');
$S->handle();
?>