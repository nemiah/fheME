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

class User_de_DE implements iTranslation {
	public function getLabels(){
		return array();
	}
	
	public function getMenuEntry(){
		return "Benutzer";
	}
	
	public function getLabelDescriptions(){
		return array();
	}
	
	public function getFieldDescriptions(){
		return array();
	}
	
	public function getText(){
		return array(
			"lostPasswordErrorUser" => "Bitte geben Sie einen gültigen Benutzernamen ein.",
			"lostPasswordErrorAdmin" => "Der Administrator kann leider nicht benachrichtigt werden.",
			"lostPasswordOK" => "Der Administrator wurde benachrichtigt.");
	}

	public function getSingular(){
		return "Benutzer";
	}
	
	public function getPlural(){
		return "Benutzer";
	}

	public function getSearchHelp(){
		return "";
	}
	
	public function getEditCaption(){
		return "Benutzer bearbeiten";
	}
	
	public function getSaveButtonLabel(){
		return "Benutzer speichern";
	}
	
	public function getBrowserCaption(){
		return "Bitte Benutzer auswählen";
	}
	
	public function getBrowserNewEntryLabel(){
		return "Benutzer neu anlegen";
	}
}
?>