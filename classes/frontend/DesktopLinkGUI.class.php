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
class DesktopLinkGUI implements iGUIHTML2 {
    public function getHTML($id){
		try {
			$U = new mUserdata();
			$U->addAssocV3("typ", "=", $_SESSION["applications"]->getActiveApplication()."DesktopLink");
			$U->addAssocV3("UserID", "=", $_SESSION["S"]->getCurrentUser()->getID());
			$U->addOrderV3("wert");
			$U->addOrderV3("UserdataID");
			$U->lCV3();
		} catch(Exception $e){
			return "";
		}
		$html = "";

		while($t = $U->getNextEntry()){
			$e = explode(";", $t->A("name"));
			$v = explode(";", $t->A("wert"));

			$B = new Button($v[2], $v[1]);
			$B->type("icon");
			$B->onclick("DesktopLink.hide(); contentManager.loadFrame('$e[2]', '$e[0]', '$e[1]')");

			$BS = new Button("Einstellungen", "./images/i2/settings.png");
			$BS->type("icon");
			$BS->className("DesktopLinkSettings");
			$BS->rmePCR("DesktopLink", "", "editInWindow", $t->getID(), "Popup.displayNamed('DesktopLinkPopup','Desktop-Link bearbeiten', transport);");

			$BM = new Button("Verschieben", "./images/i2/moveLeftRight.png");
			$BM->type("icon");
			$BM->className("DesktopLinkHandler");

			$html .= "<li id=\"DesktopLink_".$t->getID()."\" class=\"DesktopLinkIcon\">$BS$BM$B"."<p>".$v[2]."</p></li>";
		}

		if($html != "") echo "<ul id=\"DesktopLinkElements\">$html</ul><div style=\"clear:both;\"></div>";

	}

	public function editInWindow($UDID){
		$T = new HTMLTable(1);

		$B = new Button("Desktop-Link\nlöschen","trash");
		$B->onclick("");
		$B->style("float:right;");
		$B->rmePCR("DesktopLink", "", "delete", $UDID, "if(checkResponse(transport)){ Popup.close('', 'DesktopLinkPopup'); DesktopLink.loadContent(true); }");

		$T->addRow($B);
		$T->addRowClass("backgroundColor0");

		$UD = new Userdata($UDID);
		$F = new HTMLForm("DesktopLinkEdit", array("order","symbol","name","UDID"),"Bearbeiten:");
		$F->setSaveRMEP("Desktop-Link speichern", "./images/i2/save.gif", "DesktopLink", "$UDID", "save", "if(checkResponse(transport)){ Popup.close(\'\', \'DesktopLinkPopup\'); DesktopLink.loadContent(true); }");

		$v = explode(";", $UD->A("wert"));

		$F->setValue("order", $v[0]);
		$F->setValue("symbol", $v[1]);
		$F->setValue("name", $v[2]);
		$F->setValue("UDID", $UDID);

		$F->setType("UDID", "hidden");
		$F->setType("order", "hidden");
		$F->setType("symbol", "hidden");

		echo $T.$F;
	}

	public function save($values){
		parse_str($values, $v);

		$UD = new Userdata($v["UDID"]);
		$UD->changeA("wert", $v["order"].";".$v["symbol"].";".$v["name"]);
		$UD->saveMe(true, true);
	}

	public function createNew($targetClassName, $targetClassID, $targetFrame){
		$MU = new mUserdata();

		$class = new $targetClassName($targetClassID);
		list($icon, $name) = $class->getDesktopLink();
		
		$name = str_replace(";", "", $name);
		$MU->setUserdata("$targetClassName;$targetClassID;$targetFrame", "000;$icon;$name", $_SESSION["applications"]->getActiveApplication()."DesktopLink");
	}

	public function delete($UDID){
		$UD = new Userdata($UDID);
		$UD->deleteMe();
	}

	public function updateOrder($order){
		$ex = explode(";", $order);

		foreach($ex AS $k => $v){
			$U = new Userdata($v);

			$exW = explode(";", $U->A("wert"));
			$exW[0] = ($k < 100 ? "0" : "").($k < 10 ? "0" : "").$k;

			$U->changeA("wert", implode(";", $exW));
			$U->saveMe(true, false);
		}
		
	}
}
?>
