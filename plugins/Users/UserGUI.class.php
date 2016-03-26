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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
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
		
		$AC = anyC::get("User", "isAdmin", "1");
		$AC->lCV3();
		$admins = $AC->numLoaded();
		
		$AC = anyC::get("User", "isAdmin", "0");
		$AC->lCV3();
		$users = $AC->numLoaded();
		
		$gui = new HTMLGUIX($this);
		#$gui->setObject();
		$gui->name("Benutzer");
		
		$gui->attributes(array(
			"name",
			"username",
			"password",
			"SHApassword",
			"language",
			"UserPosition",
			"isAdmin",
			"UserEmail",
			"UserICQ",
			"UserJabber",
			"UserSkype",
			"UserTel"));
		
		
		$gui->label("name","Name");
		$gui->label("username","Benutzername");
		$gui->label("password","Passwort");
		$gui->label("SHApassword","Passwort");
		$gui->label("language","Sprache");
		$gui->label("isAdmin","Admin-Rechte?");
		$gui->label("UserEmail","E-Mail");
		$gui->label("UserICQ","ICQ");
		$gui->label("UserJabber","Jabber");
		$gui->label("UserSkype","Skype");
		$gui->label("UserTel","Telefon");
		
		$gui->type("language","select", array("de_DE" => "Deutsch (Deutschland) €", "de_DE_EUR" => "Deutsch (Deutschland) EUR", "de_CH" => "Deutsch (Schweiz) sFr", "de_CH_CHF" => "Deutsch (Schweiz) CHF", "en_GB" => "English (United Kingdom)"));
		#$gui->setOptions("language",);
		$gui->descriptionField("SHApassword","Zum Ändern eingeben.");
		$gui->type("password","hidden");
		$gui->type("SHApassword","password");
		#$gui->type("isAdmin","radio");
		$gui->descriptionField("isAdmin","<span style=\"color:red;\">Achtung: als Admin sehen Sie nur diese Admin-Oberfläche und NICHT das Programm selbst!</span>");
		
		
		#$gui->translate($this->loadTranslation());
		$gui->space("UserEmail",isset($this->texts["Kontaktdaten"]) ? $this->texts["Kontaktdaten"] : "Kontaktdaten");
		$gui->type("isAdmin","checkbox");
		if($admins == 1 AND $users == 0)
			$gui->type("isAdmin", "hidden");
		#$gui->setOptions("isAdmin",array("1","0"),array("ja ","nein"));
		#$gui->setStandardSaveButton($this);
		
		$gui->customize($this->customizer);
					
		$mUD = new mUserdataGUI();
		$mUD->addAssocV3("UserID","=",$this->ID);
		$mUD->addAssocV3("typ","=","uRest","AND","1");
		$mUD->addAssocV3("typ","=","relab","OR","1");
		$mUD->addAssocV3("typ","=","hideF","OR","1");
		$mUD->addAssocV3("typ","=","pSpec","OR","1");
		$mUD->addAssocV3("typ","=","pHide","OR","1");
		$mUD->addAssocV3("typ","=","loginTo","OR","1");
		$html = "<div>".$mUD->getHTML(-1)."</div>";
		if($id == -1) $html = "<table><tr><td class=\"backgroundColor3\">Sie können Einschränkungen erst anlegen, wenn der Benutzer gespeichert wurde.</td></tr></table>";
		
		if($this->getID() > 20000)
			$gui->optionsEdit (false, false);
		
		return $gui->getEditHTML().(($this->A->isAdmin != 1) ? $html :"");
	}
}
?>