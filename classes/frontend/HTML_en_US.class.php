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
class HTML_en_US {
	public function getEditTexts(){
		return array(
		"kein Speichern" => "You are not allowed to save this entry.",
		"gehe zu Datensatz ID" => "go to<br />dataset ID",
		"Operationen" => "Operations",
		"Neu mit Werten" => "New with values",
		"XML Export" => "XML-Export",
		"Kopieren" => "Copy",
		"Löschen" => "Delete",
		"Wirklich löschen?" => "Really delete?",
		"ID für Repeatable" => "ID for Repeatable",
		"Repeatable erstellen" => "create Repeatable",
		"zuerst speichern" => "You have to save the dataset before you can upload an image",
		"Bild wirklich löschen?" => "Really delete the image?",
		"Bild hochladen" => "Upload image",
		"Bild löschen" => "Delete image",
		"in Editor bearbeiten" => "show\nEditor");
	}
	
	public function getBrowserTexts(){
		return array(
		"%1 wirklich löschen?" => "really delete %1?",
		"Eintrag" => "entry",
		"Einträge" => "entries",
		"Seite" => "page",
		"Seiten" => "pages",
		"Einstellungen" => "Settings",
		"Filter löschen" => "delete filter",
		"Anzeige wurde gefiltert" => "Categories are filtered",
		"Anzahl Einträge pro Seite" => "Entries per page",
		"speichern" => "save",
		"nach Kategorien filtern" => "filter by category",
		"nach Spalte sortieren" => "order by column",
		"hochladen" => "upload",
		"<=100KB" => "It is not recommended to upload images larger than 100kB",
		"Suche" => "Search",
		"versionError" => "You are using an old version of this plugin (%1) within a newer framework (%2).<br />If you have updated this application please use the following button to reload it.");
	}
}
?>