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

class Userdata_en_US implements iTranslation {
	public function getLabels(){
		return array("username" => "Username",
		"SHApassword" => "Password",
		"language" =>"Language",
		"isAdmin" => "Admin-rights?");
	}
	
	public function getMenuEntry(){
		return "";
	}
	
	public function getLabelDescriptions(){
		return array();
	}
	
	public function getFieldDescriptions(){
		return array("isAdmin" => "Attention: As Admin you will only see this Admin-interface and NOT the program itself!");
	}
	
	public function getText(){
		return array(
		"kann nicht löschen" => "can't delete",
		"kann nicht erstellen" => "can't create",
		"kann nicht bearbeiten" => "can't edit",
		"Feld wurde umbenannt" => "field was relabeled",
		"Feld wurde versteckt" => "field was hidden",
		"Plugin-spezifisch" => "plugin-specific",
		"Plugin ausblenden" => "hide plugin",
		
		"Feld\numbenennen" => "relabel\nfield",
		"Einschränkung\nhinzufügen" => "add\nrestriction",
		"Feld\nausblenden" => "hide\nfield",
		"Plugin-\nspezifisch" => "plugin-\nspecific",
		"Plugin\nausblenden" => "hide\nplugin",
		"copy" => "copy from\nuser",
		"kopieren" => "copy",

		"Umbenennung" => "Relabel",
		"Einschränkung" => "Restrict",
		"Ausblenden" => "Hide",
		"Plugin" => "Plugin",
		
		"pluginSupport" => "Please be aware that a plugin needs to support these settings even if it is shown here!",
		"selectPlugin" => "Please select a plugin",
		"add" => "add",
		"selectPluginButton" => "select plugin",
		"save" => "save",
		"noPsOptions" => "no plugin specific options available",
		"newFieldName" => "new field name",
		"select" => "select",
		"maybeHidden" => "Please be aware that the field names displayed here are internal names which may differ from the actual field names!<br />Some fields may not be shown at all."
		);
	}

	public function getSingular(){
		return "Userdata";
	}
	
	public function getPlural(){
		return "Acces limitations";
	}

	public function getSearchHelp(){
		return "";
	}
	
	public function getEditCaption(){
		return "edit Userdata";
	}
	
	public function getSaveButtonLabel(){
		return "save Userdata";
	}
	
	public function getBrowserCaption(){
		return "Please select an Userdata";
	}
	
	public function getBrowserNewEntryLabel(){
		return "new Userdata";
	}
}
?>