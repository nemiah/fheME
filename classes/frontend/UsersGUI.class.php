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
class UsersGUI extends Users implements iGUIHTML2{
	public function getHTML($id){
		$allowedUsers = Environment::getS("allowedUsers", null);
		
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

		$T = new HTMLTable(1, "Application Server");

		$I = new HTMLInput("AppServer", "text", mUserdata::getGlobalSettingValue("AppServer", ""));
		$I->onEnter("contentManager.rmePCR('Users', '-1', 'saveAppServer', [this.value], ' ');");
		if($allowedUsers === null)
			$T->addRow($I."<br /><small>Wenn Sie einen Application Server betreiben, tragen Sie hier bitte die URL ein, um die Benutzer mit diesem Server zu authentifizieren.</small>");

		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Benutzer");
		$gui->setCollectionOf($this->collectionOf,"Benutzer");

		$gui->setParser("isAdmin","UsersGUI::isAdminParser");
		$gui->setColWidth("isAdmin","20px");
		
		$gui->setShowAttributes(array("name","username","isAdmin"));
		
		$g = "";
		
		if(strstr($_SERVER["SCRIPT_FILENAME"],"demo")) {
			$UA = $_SESSION["S"]->getCurrentUser()->getA();
			if($UA->name != "Installations-Benutzer"){
				$g = "In der Demo können keine Benutzer geändert werden!";
				$gui->setIsDisplayMode(true);
			}
		}
		
		$TR = new HTMLTable(1);
		if($allowedUsers !== null AND $id == -1){
			$B = new Button("", "notice", "icon");
			$B->style("float:left;margin-right:10px;");
			$TR->addRow(array($B."Bitte beachten Sie: Sie können insgesamt $allowedUsers Benutzer ohne Admin-Rechte anlegen."));
		}
		
		$gui->customize($this->customizer);
		
		return $TR.$g.$gui->getBrowserHTML($id).($id == -1 ? $T : "");
	}
	
	function doCertificateLogin($application, $sprache, $cert) {
		echo parent::doCertificateLogin($application, $sprache, $cert);
	}
	
	function doPersonaLogin($application, $sprache, $assertion) {
		echo parent::doPersonaLogin($application, $sprache, $assertion);
	}
	
	function doLogin($ps){
		$args = func_get_args();
		if(count($args) > 1){
			$ps = array();
			$ps["loginUsername"] = $args[0];
			$ps["loginSHAPassword"] = $args[1];
			$ps["anwendung"] = $args[2];
			$ps["loginSprache"] = $args[3];
		}
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