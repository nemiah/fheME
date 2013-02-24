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
class UserGUI extends User implements iGUIHTML2 {
	function getHTML($id){
		$this->customize();
		
		#if($this->A == null AND $id != -1) $this->loadMe();
		#if($id == -1) $this->A = new UserAttributes();
		$this->loadMeOrEmpty();
		
		$up = new anyC();
		$up->setCollectionOf("User");
		$up->addAssocV3("password","!=",";;;-1;;;");
		$up->lCV3();
		
		if($up->numLoaded() > 0) return "
		<table>
			<colgroup>
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td><input onclick=\"rme('Users','','convertPasswords','','contentManager.reloadFrameRight();');\" type=\"button\" style=\"float:right;background-image:url(./images/navi/keys.png);\" class=\"bigButton backgroundColor2\" value=\"Passwörter\nkonvertieren\" />In Ihrer Datenbank befinden sich noch unkonvertierte Passwörter.</td>
			</tr>
		</table>";
		$this->A->password = ";;;-1;;;";
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Benutzer");
		$gui->setLabel("username","Benutzername");
		$gui->setLabel("password","Passwort");
		$gui->setLabel("SHApassword","Passwort");
		$gui->setLabel("language","Sprache");
		$gui->setType("language","select");
		$gui->setOptions("language",array("de_DE", "de_CH", "en_GB"),array("Deutsch (Deutschland)", "Deutsch (Schweiz)", "English (United Kingdom)"));
		$gui->setFieldDescription("SHApassword","Zum Ändern eingeben.");
		$gui->setType("password","hidden");
		$gui->setType("SHApassword","password");
		$gui->setLabel("isAdmin","Admin-Rechte?");
		$gui->setType("isAdmin","radio");
		$gui->setFieldDescription("isAdmin","<span style=\"color:red;\">Achtung: als Admin sehen Sie nur diese Admin-Oberfläche und NICHT das Programm selbst!</span>");
		
		$gui->setLabel("UserEmail","E-Mail");
		$gui->setLabel("UserICQ","ICQ");
		$gui->setLabel("UserJabber","Jabber");
		$gui->setLabel("UserSkype","Skype");
		$gui->setLabel("UserTel","Telefon");
		
		$gui->translate($this->loadTranslation());
		$gui->insertSpaceAbove("UserEmail",isset($this->texts["Kontaktdaten"]) ? $this->texts["Kontaktdaten"] : "Kontaktdaten");
		$gui->setType("isAdmin","checkbox");
		#$gui->setOptions("isAdmin",array("1","0"),array("ja ","nein"));
		$gui->setStandardSaveButton($this);
		
		$gui->customize($this->customizer);
					
		$mUD = new mUserdataGUI();
		$mUD->addAssocV3("UserID","=",$this->ID);
		$mUD->addAssocV3("typ","=","uRest","AND","1");
		$mUD->addAssocV3("typ","=","relab","OR","1");
		$mUD->addAssocV3("typ","=","hideF","OR","1");
		$mUD->addAssocV3("typ","=","pSpec","OR","1");
		$mUD->addAssocV3("typ","=","pHide","OR","1");
		$html = "<div>".$mUD->getHTML(-1)."</div>";
		if($id == -1) $html = "<table><tr><td class=\"backgroundColor3\">Sie können Einschränkungen erst anlegen, wenn der Benutzer angelegt wurde.</td></tr></table>";
		return $gui->getEditHTML().(($this->A->isAdmin != 1) ? $html :"");
	}
}
?>