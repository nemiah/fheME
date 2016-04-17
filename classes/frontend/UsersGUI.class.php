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
class UsersGUI extends Users implements iGUIHTML2{
	public function getHTML($id){
		$allowedUsers = Environment::getS("allowedUsers", null);
		
		#$this->addAssocV3("UserType", "=", "0");
		$this->addOrderV3("name");
		if($this->A == null) $this->lCV3($id);
		
		$up = new anyC();
		$up->setCollectionOf("User");
		$up->addAssocV3("password","!=",";;;-1;;;");
		$up->lCV3();
		
		if($up->numLoaded() > 0 AND $id == -1) return "
		<table>
			<colgroup>
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td><input onclick=\"rme('Users','','convertPasswords','','contentManager.reloadFrameRight();');\" type=\"button\" style=\"float:right;background-image:url(./images/navi/keys.png);\" class=\"bigButton backgroundColor2\" value=\"Passwörter\nkonvertieren\" />In Ihrer Datenbank befinden sich noch unkonvertierte Passwörter.</td>
			</tr>
		</table>";

		$gui = new HTMLGUIX($this);
		$gui->screenHeight();
		$gui->name("Benutzer");
		#$gui->setCollectionOf($this->collectionOf,"Benutzer");

		$gui->parser("isAdmin","UsersGUI::isAdminParser");
		$gui->colWidth("isAdmin","20px");
		
		$gui->attributes(array("name","username","isAdmin"));
		
		/*$g = "";
		
		if(strstr($_SERVER["SCRIPT_FILENAME"],"demo")) {
			$UA = $_SESSION["S"]->getCurrentUser()->getA();
			if($UA->name != "Installations-Benutzer"){
				$g = "In der Demo können keine Benutzer geändert werden!";
				$gui->setIsDisplayMode(true);
			}
		}*/
		
		$TR = new HTMLTable(1);
		if($allowedUsers !== null AND $id == -1){
			$B = new Button("", "notice", "icon");
			$B->style("float:left;margin-right:10px;");
			$TR->addRow(array($B."Bitte beachten Sie: Sie können insgesamt $allowedUsers Benutzer ohne Admin-Rechte anlegen."));
		}
		
		if($allowedUsers === null){
			$B = $gui->addSideButton ("Externe\nAuthentifizierung", "./plugins/Users/auth.png");
			$B->popup("", "Externe Authentifizierung", "Users", "-1", "authenticationPopup");
		}
		
		$gui->prepend($TR);
		$gui->customize($this->customizer);
		
		try {
			$AD = new LoginAD();
			$AD->getUsers();
			
			if($AD->n() !== null){
				$B = $gui->addSideButton ("ActiveDirectory-\nBenutzer", "users");
				$B->popup("", "ActiveDirectory-Benutzer", "Users", "-1", "ldapUsersPopup");
			}
		} catch(Exception $e){
			
		}
			
		return $gui->getBrowserHTML($id);
	}
	
	function ldapUsersPopup(){
		$T = "";
		try {
			$AD = new LoginAD();
			$AD->getUsers();

			$T = new HTMLTable(2);
			$T->setColWidth(1, 20);
			$T->maxHeight(400);
			$T->useForSelection(false);
			$B = new Button("Eintrag bearbeiten", "./images/i2/edit.png", "icon");
			
			while($U = $AD->n()){
				$T->addRow(array($B, $U->A("name")));
				$T->addRowEvent("click", OnEvent::frame("contentLeft", "User", $U->getID()));
			}
			
		} catch(Exception $e){
			
		}
		
		echo $T;
	}
	
	function authenticationPopup(){
		$allowedUsers = Environment::getS("allowedUsers", null);
		if($allowedUsers !== null)
			return;
		
		$F = new HTMLForm("appserver", array("appServer"), "Application Server");
		$F->useRecentlyChanged();
		
		$F->setLabel("appServer", "App-Server");
		
		if(function_exists("ldap_connect"))
			$F->getTable()->setTableStyle("margin-bottom:30px;");
		$F->getTable()->setColWidth(1, 120);
		$F->setValue("appServer", mUserdata::getGlobalSettingValue("AppServer", ""));
		
		$F->setDescriptionField("appServer", "Wenn Sie einen Application Server betreiben, tragen Sie hier bitte die URL ein, um die Benutzer mit diesem Server zu authentifizieren.");
		
		
		$F->setSaveRMEPCR("Speichern", "", "Users", "", "saveAppServer", OnEvent::closePopup("Users"));
		

		echo $F;
		
		if(!function_exists("ldap_connect"))
			return;
		
		echo "<span></span><div class=\"backgroundColor1 Tab\"><p>Active Directory</p></div>";
		
		$LD = LoginData::get("ADServerUserPass");
		
		
		BPS::setProperty("LoginDataGUI", "preset", "adServer");
		
		$gui = new LoginDataGUI($LD == null ? -1 : $LD->getID());
		$gui->loadMeOrEmpty();
		if($LD != null)
			$gui->setA($LD->getA());
		
		$gui->getPopup();
	}
	
	function doCertificateLogin($application, $sprache, $cert) {
		echo parent::doCertificateLogin($application, $sprache, $cert);
	}
	
	function doLogin($ps){
		$args = func_get_args();
		if(count($args) > 1){
			$ps = array();
			$ps["loginUsername"] = $args[0];
			$ps["loginSHAPassword"] = $args[1];
			$ps["anwendung"] = $args[2];
			$ps["loginSprache"] = $args[3];
			$ps["loginPWEncrypted"] = $args[4];
		}
		
		if(is_array($ps) AND !isset($ps["loginPWEncrypted"]))
			$ps["loginPWEncrypted"] = true;
			
		echo parent::doLogin($ps);
	}
	
	function doLogout(){
		$r = parent::doLogout();
	}
	
	public static function isAdminParser($w){
		return $w == 1 ? "<img src=\"./images/i2/ok.gif\" />" : "<img src=\"./images/i2/notok.gif\" />";
	}

	
	public function convertPasswords(){
		$ac = new anyC();
		$ac->setCollectionOf("User");
		$ac->addAssocV3("password","!=",";;;-1;;;");
		
		while($t = $ac->getNextEntry())
			$t->convertPassword();
		
	}

	public function saveAppServer($value){
		mUserdata::setUserdataS("AppServer", $value, "", -1);
		Red::messageSaved();
	}
}
?>