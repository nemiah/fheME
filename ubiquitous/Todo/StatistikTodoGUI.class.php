<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class StatistikTodoGUI extends StatistikGUI implements iGUIHTML2 {
	function __construct(){
		$this->dataFile = "chart";
		
		parent::__construct();

		$this->bps = BPS::getAllProperties("StatistikControllingGUI");

		$D1 = new Datum(time());
		$D1->setToJan1st(date("Y"));
		
		$D2 = new Datum(time());
		$D2->setToJan1st(date("Y") + 1);
		$D2->subDay();
		
		$this->startDatum = isset($this->bps["start"]) ? $this->bps["start"] : Util::CLFormatDate($D1->time());
		$this->endDatum = isset($this->bps["ende"]) ? $this->bps["ende"] : Util::CLFormatDate($D2->time());

		
		$this->mK = null;
	}
	
	public function getLabel(){
		return "Termine";
	}
	
	function getHTML($id){
		#$p = mUserdata::getPluginSpecificData("mStatistik");
		#if(!isset($p["pluginSpecificCanUseControlling"]))
		#	return;
		
		$html = "";#parent::getHTML($id);
		
		$tab = new HTMLTable(1);

		$tab->addRow("<div id=\"my_chartTodo\" style=\"height:200px;width:470px;\"></div>");
		$tab->addRowClass("backgroundColor0");
		
		
		$AC = $this->data();
		
		$U = Users::getUsersArray(null, true);
		$U[-1] = "Allgemein";
		
		$T = new HTMLTable(6);
		$T->addColStyle(2, "text-align:right;");
		$T->addColStyle(3, "text-align:right;");
		$T->addColStyle(4, "text-align:right;");
		$T->addColStyle(5, "text-align:right;");
		$T->addColStyle(6, "text-align:right;");
		$T->addHeaderRow(array(
			"Benutzer",
			"Gesamt",
			"Abgesch.",
			"Kalt",
			"Erst",
			"Folge"
		));
		while($A = $AC->n()){
			$T->addRow(array(
				isset($U[$A->A("TodoUserID")]) ? $U[$A->A("TodoUserID")] : "Benutzer ID ".$A->A("TodoUserID"),
				$A->A("gesamt"),
				$A->A("gesamtDM")." (".Util::CLNumberParserZ($A->A("gesamt") != 0 ? round($A->A("gesamtDM") / $A->A("gesamt") * 100, 2) : 0)."%)",
				$A->A("gesamtKalt"),
				$A->A("gesamtErst"),
				$A->A("gesamtFolge")
				
			));
			
			$T->addRowStyle("cursor:pointer;");
			$T->addRowEvent("click", OnEvent::rme($this, "details", array($A->A("TodoUserID")), "function(t){ \$j('#contentScreenLeft').html(t.responseText); }"));
		}
		
		if($AC->numLoaded() == 0){
			$T->addRow (array("Es liegen keine Daten vor"));
			$T->addRowColspan(1, 4);
		}
		
		return "<p class=\"prettySubtitle\">Termine</p>".$tab.$html.$T.OnEvent::script("
			var plot = \$j.plot(\$j('#my_chartTodo'), ".$this->chart().", {
			series: {
				pie: { show: true, innerRadius: 0.4 }
			}
			});".OnEvent::rme($this, "getHeaderCenter", "", "function(transport){ \$j('#contentScreenCenterHeader').html(transport.responseText); }"));
	}
	
	public function details($UserID){
		$p = mUserdata::getPluginSpecificData("mStatistik");
		$ps = mUserdata::getPluginSpecificData("mAkquise");
		if(!isset($p["pluginSpecificCanUseControlling"]) AND $UserID != Session::currentUser()->getID() AND !isset($ps["pluginSpecificCanSeeFrom$UserID"]))
			return "";
		
		$U = Users::getUsersArray(null, true);
		
		$AC = $this->data();
		$AC->setOrderV3("anzahl", "DESC");
		$AC->addJoinV3("Adresse", "TodoClassID", "=", "AdresseID");
		$AC->setGroupV3("CONCAT(TodoClass, TodoClassID)");
		$AC->addAssocV3("TodoUserID", "=", $UserID);
		$AC->setFieldsV3(array(
			"firma",
			"vorname",
			"nachname",
			"COUNT(*) AS anzahl",
			"AdresseID",
			"TodoClass"
		));
		
		$T = new HTMLTable(2, "Termine ".$U[$UserID]." vom $this->startDatum bis $this->endDatum");
		$T->setColWidth(1, 30);
		#$T->setColWidth(3, 20);
		$T->addColStyle(1, "text-align:right;");
		#$T->setTableID("termineTable");
		
		while($A = $AC->n()){
			#$B = new Button("Akquise anzeigen", "./images/i2/telephone.png", "icon");
			#$B->onclick("\$j('#termineTable .lastSelected').removeClass('lastSelected'); \$j(this).parent().parent().addClass('lastSelected'); ".Akquise::getWindowAction($A->A("AdresseID"), 0));
			#onclick="Popup.load('Akquise', 'mAkquise', '-1', 'showMinTelPopup', Array('65143','0'), '', 'edit', '{width: 730, top:$j(\'#navTabsWrapper\').outerHeight() + 20, left: contentManager.maxWidth() < 800 + 730 ? contentManager.maxWidth() - 740 : 800}');"
			if($A->A("TodoClass") != "WAdresse"){
				$T->addRow(array(
					$A->A("anzahl")."x",
					"Ohne Adresse"
				));
				continue;
			}
			
			if($A->A("TodoClass") == "WAdresse" AND !$A->A("AdresseID")){
				$T->addRow(array(
					$A->A("anzahl")."x",
					"Adresse gelöscht"
				));
				continue;
			}
			
			$T->addRow(array(
				$A->A("anzahl")."x",
				($A->A("firma") != "" ? $A->A("firma") : $A->A("vorname")." ".$A->A("nachname"))
			));
		}
		
		echo $T;
	}
	
	public function data($UserID = 0, $start = null, $ende = null){
		$p = mUserdata::getPluginSpecificData("mStatistik");
		$ps = mUserdata::getPluginSpecificData("mAkquise");
		
		$AC = anyC::get("Todo");
		$AC->addAssocV3("TodoFromDay", ">=", $start ? $start : Util::CLDateParser($this->startDatum, "store"));
		$AC->addAssocV3("TodoFromDay", "<=", $ende ? $ende : Util::CLDateParser($this->endDatum, "store"));
		if($UserID)
			$AC->addAssocV3("TodoUserID", "=", $UserID);
		$AC->addGroupV3("TodoUserID");
		
		if(!isset($p["pluginSpecificCanUseControlling"])){
			$AC->addAssocV3 ("TodoUserID", "=", Session::currentUser()->getID(), "AND", "2");
			
			foreach($ps AS $key => $value) 
				if(strstr($key, "pluginSpecificCanSeeFrom"))
					$AC->addAssocV3("TodoUserID", "=", str_replace("pluginSpecificCanSeeFrom", "", $key), "OR", "2");
		}
		
		$AC->setFieldsV3(array(
			"TodoUserID",
			"COUNT(*) AS gesamt",
			"COUNT(CASE WHEN TodoDoneTime > 0 THEN 1 END) AS gesamtDM",
			"COUNT(CASE WHEN TodoType = 3 THEN 1 END) AS gesamtKalt",
			"COUNT(CASE WHEN TodoType = 4 THEN 1 END) AS gesamtErst",
			"COUNT(CASE WHEN TodoType = 5 THEN 1 END) AS gesamtFolge"
		));
		$AC->addOrderV3("gesamt ", "DESC");
		
		return $AC;
	}
	
	public function format(){
		
	}
	
	public function getHeaderCenter() {
		#$I = new HTMLInput("user", "select", BPS::getProperty("StatistikAkquiseGUI", "UserID", 0), Users::getUsersArray("Bitte Benutzer auswählen", 0));
		echo "";
	}
	
	public function chart() {
		$AC = $this->data();
		$U = Users::getUsersArray(null, true);
		
		$data = array();
		
		$i = 0;
		while($A = $AC->n()){
			$v = new stdClass();
			$v->data = $A->A("gesamt");
			$v->label = isset($U[$A->A("TodoUserID")]) ? $U[$A->A("TodoUserID")] : "ID ".$A->A("TodoUserID");
			$data[] = $v;
			
			if(++$i == 3){
				break;
			}
		}
		
		$rest = 0;
		while($A = $AC->n()){
			$rest += $A->A("gesamt");
		}
		
		if($rest > 0){
			$v = new stdClass();
			$v->data = $rest;
			$v->label = "Andere";
			$data[] = $v;
		}

		return json_encode($data);
	}
	
}
?>