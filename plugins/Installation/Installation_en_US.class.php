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

class Installation_en_US implements iTranslation {
	public function getLabels(){
		return array("datab" => "Database",
		"user" => "User",
		"password" => "Password");
	}
	
	public function getMenuEntry(){
		return "";
	}
	
	public function getLabelDescriptions(){
		return array();
	}
	
	public function getFieldDescriptions(){
		return array("host" => "of the database");
	}
	
	public function getText(){
		return array("neue Plugins laden" => "load new\nplugins",
		"alle Tabellen aktualisieren" => "update all\ntables",
		"keine DB-Info-Datei" => "no database-info file",
		"Tabelle existiert" => "table exists",
		"Tabelle anlegen" => "create table",
		"wrongData" => "The database access data you provided is wrong.<br /><br />Please try again. The list of plugins will be displayed here if a connection to the database can be established.",
		"noDatabase" => "The database name you provided is wrong.<br /><br />Please use an existing database, it will not be created automatically.");
	}

	public function getSingular(){
		return "Installation";
	}
	
	public function getPlural(){
		return "Installations";
	}

	public function getSearchHelp(){
		return "";
	}
	
	public function getEditCaption(){
		return "edit Installation";
	}
	
	public function getSaveButtonLabel(){
		return "save Installation";
	}
	
	public function getBrowserCaption(){
		return "Please select an Installation";
	}
	
	public function getBrowserNewEntryLabel(){
		return "new Installation";
	}
}
?>