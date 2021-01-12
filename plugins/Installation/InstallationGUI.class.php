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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class InstallationGUI extends Installation implements iGUIHTML2 {
	public function getHTML($id){
		$this->loadMeOrEmpty();
		#if($this->A == null AND $id != -1) $this->loadMe();
		#if($id == -1) $this->A = new InstallationAttributes();
		
		BPS::setProperty("mInstallationGUI", "showErrorText", "1");
		
		$gui = new HTMLGUI();
		
		if(!Session::isPluginLoaded("multiInstall")){
			$this->A->httpHost = "*";
			$gui->setType("httpHost","hidden");
		} else $gui->insertSpaceAbove("httpHost");
		
		$gui->setObject($this);
		$gui->setName("Zugangsdaten");
		$gui->setType("password","password");
		
		$gui->setLabel("datab","Datenbank");
		$gui->setLabel("user","Benutzer");
		$gui->setLabel("password","Passwort");
		$gui->setLabel("httpHost","Mandant");
		
		$gui->setInputJSEvent("password", "onkeyup", "\$j('#maxLength').css('color', 'inherit'); if(\$j(this).val().length >  20) \$j('#maxLength').css('color', 'red');");
		
		$gui->setFieldDescription("password","<span id=\"maxLength\">Die maximale Passwortlänge beträgt 20 Zeichen.</span>");
		$gui->setFieldDescription("host","Der Rechner, auf dem die Datenbank liegt. Das kann 'localhost' sein, oder eine IP wie '192.168.8.243' oder ein Hostname wie 'rdbms.strato.de'.");
		$gui->setFieldDescription("httpHost","* für Standard-Mandant oder Name. <span style=\"color:red;\">ACHTUNG: Es muss genau einen *-Eintrag geben!</span>");
		#$this->loadGUITranslation($gui);
		#$gui->translate($this->loadTranslation());
		$gui->setJSEvent("onSave","
				function() {
					contentManager.emptyFrame('contentLeft');
					contentManager.emptyFrame('contentBelow');
					contentManager.reloadFrame('contentRight');"."
				}");
		$gui->setStandardSaveButton($this);
		
		return $gui->getEditHTML();
	}
}
?>