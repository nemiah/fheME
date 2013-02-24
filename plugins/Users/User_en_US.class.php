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

class User_en_US implements iTranslation {
	public function getLabels(){
		return array("username" => "Username",
		"SHApassword" => "Password",
		"language" =>"Language",
		"isAdmin" => "Admin-rights?");
	}
	
	public function getMenuEntry(){
		return "Users";
	}
	
	public function getLabelDescriptions(){
		return array();
	}
	
	public function getFieldDescriptions(){
		return array(
		"SHApassword" => "input new password if you want to change it",
		"isAdmin" => "Attention: As Admin you will only see this Admin-interface and NOT the program itself!");
	}
	
	public function getText(){
		return array("Kontaktdaten" => "Contact data",
			"lostPasswordErrorUser" => "Please insert a valid username.",
			"lostPasswordErrorAdmin" => "The administrator can unfortunately not be notified.",
			"lostPasswordOK" => "The administrator has been notified.");
	}

	public function getSingular(){
		return "User";
	}
	
	public function getPlural(){
		return "Users";
	}

	public function getSearchHelp(){
		return "";
	}
	
	public function getEditCaption(){
		return "edit User";
	}
	
	public function getSaveButtonLabel(){
		return "save User";
	}
	
	public function getBrowserCaption(){
		return "Please select an User";
	}
	
	public function getBrowserNewEntryLabel(){
		return "new User";
	}
}
?>