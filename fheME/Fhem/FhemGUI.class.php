<?php
/*
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class FhemGUI extends Fhem implements iGUIHTML2 {
	function __construct($ID) {
		parent::__construct($ID);

		$this->setParser("FhemFHTDefaultDayTemp", "Util::CLNumberParserZ");
	}

	function getHTML($id){

		$this->loadMeOrEmpty();

		$gui = new HTMLGUIX($this);
		$gui->name("Fhem");

		$gui->label("FhemServerID","Server");
		$gui->label("FhemName","Name in FHEM");
		$gui->label("FhemAlias","Zu zeigender Name");
		$gui->label("FhemType","Type");
		$gui->label("FhemSpecific","Specific");
		$gui->label("FhemModel","Model");
		$gui->label("FhemFHTModel","Model");
		$gui->label("FhemITModel","Model");
		$gui->label("FhemHMModel","Model");
		$gui->label("FhemEMModel","Model");
		$gui->label("FhemRunOn","run on");
		$gui->label("FhemCommand","Command");
		$gui->label("FhemLocationID","Location");
		$gui->label("FhemInOverview","In Overview?");
		$gui->label("FhemFHTDefaultDayTemp","day-temp");

		$gui->type("FhemInOverview","checkbox");
		$gui->type("FhemModel", "select", array("" => "none", "fs20du" => "FS20 DU","fs20s4u" => "FS20 S4U","fs20st" => "FS20 ST","fs20di" => "FS20 DI","fs20irf" => "FS20 IRF", "fs20rsu" => "FS20 RSU"));

        $gui->type("FhemITModel", "select", array("" => "none", "itdimmer" => "IT-Dimmer", "itswitch" => "IT-Switch"));

        $gui->type("FhemHMModel", "select", array("" => "none", "dimmer" => "Dimmer", "switch" => "Switch"));
        
        $gui->type("FhemEMModel", "select", array("" => "none", "emem" => "EM 1000-EM"));

		$gui->type("FhemFHTModel", "select", array("" => "none", "fht80b" => "80B"));

		$gui->type("FhemType", "select", array("" => "none", "FS20" => "FS20","FHZ" => "FHZ", "FHT" => "FHT", "HomeMatic" => "HomeMatic", "Intertechno" => "Intertechno", "ELV EM" => "ELV EM", "notify" => "notify", "dummy" => "dummy"/*,"RGB" => "RGB"*/));

		$B = $gui->addSideButton("Show\ndata", "./fheME/Fhem/showData.png");
		$B->popup("", "Show data", "Fhem", $this->getID(), "showData");

		$gui->type("FhemLocationID", "select", new mFhemLocationGUI(), "FhemLocationName", "everywhere");
		#$gui->selectWithCollection("FhemLocationID", );

		$gui->type("FhemCommand","textarea");

		$gui->descriptionField("FhemRunOn","e.g. Door:toggle");
		$gui->descriptionField("FhemCommand","you may use new line, it will be replaced by space");

		$gui->inputStyle("FhemCommand","height:300px;font-size:8px;");

		$gui->attributes(array("FhemServerID", "FhemLocationID", "FhemName", "FhemAlias", "FhemInOverview", "FhemType", "FhemModel", "FhemITModel", "FhemHMModel", "FhemEMModel", "FhemSpecific", "FhemRunOn", "FhemCommand", "FhemFHTModel"/*, "FhemFHTDefaultDayTemp"*/));

		$gui->space("FhemType");

		$gui->toggleFieldsInit("FhemType", array("FhemModel", "FhemITModel", "FhemHMModel", "FhemEMModel", "FhemSpecific", "FhemRunOn", "FhemCommand", "FhemFHTModel", "FhemFHTDefaultDayTemp"));
		$gui->toggleFields("FhemType", "FHZ", array("FhemSpecific"));
		$gui->toggleFields("FhemType", "FS20", array("FhemModel", "FhemSpecific"));
		$gui->toggleFields("FhemType", "notify", array("FhemRunOn", "FhemCommand"));
		$gui->toggleFields("FhemType", "Intertechno", array("FhemITModel", "FhemSpecific"));
		$gui->toggleFields("FhemType", "HomeMatic", array("FhemHMModel", "FhemSpecific"));
		$gui->toggleFields("FhemType", "ELV EM", array("FhemEMModel", "FhemSpecific"));
		$gui->toggleFields("FhemType", "FHT", array("FhemFHTModel", "FhemSpecific"/*, "FhemFHTDefaultDayTemp"*/));

		$gui->type("FhemServerID", "select", new mFhemServerGUI(), "FhemServerName");

		return $gui->getEditHTML();
	}

	public function showData(){
		list($S, $I) = $this->getData();


		$TabS = new HTMLTable(3);
		$TabS->maxHeight(200);

		$TabI = new HTMLTable(2, "Int");
		$TabI->maxHeight(200);

		foreach($S AS $v)
			$TabS->addRow($v);

		foreach($I AS $v)
			$TabI->addRow($v);

		echo $TabS.$TabI;

		Fhem::disconnectAll();
	}
}
?>
