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
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;

class Bericht_AusstehendeRechnungenGUI extends Bericht_default implements iBerichtDescriptor {
	public $CustomizerTeilzahlungen = false;
	public $CustomizerZahlungsziel = false;
	private $kundennummer = null;
	
	function __construct() {
 		parent::__construct();
		
		$this->useVariables(array("useBAusstRechnMode", "useBAusstRechnDate"));
		$this->useVariableDefault("useBAusstRechnMode", "perCustomer");

		if(Applications::activeApplication() != "open3A" AND Applications::activeApplication() != "openFiBu") 
			return;

		$this->customize();
		
		$this->collection = $this->getData();
 	}
	
	public function getCategory(){
		return "Buchhaltung";
	}
	
 	public function getLabel(){
		if(Applications::activeApplication() != "open3A" AND Applications::activeApplication() != "openFiBu") 
			return null;
 		#if(!Session::isPluginLoaded("mStatistik")) return null;
 		
		return "Offene Posten Kunden";
 	}
 		
	public function getHTML($id){
 		$phtml = parent::getHTML($id);

		$F = new HTMLForm("BC", $this->variables, "Einstellungen");
		$F->getTable()->setColWidth(1, 120);
		
		$users = Users::getUsersArray("Alle");
		$p = mUserdata::getPluginSpecificData("mStatistik");
		$ps = mUserdata::getPluginSpecificData("mAkquise");
		
		if(!isset($p["pluginSpecificCanUseControlling"])){
			$u = array("Alle");
			$u[Session::currentUser()->getID()] = Session::currentUser()->A("name");
			
			foreach($ps AS $key => $value) 
				if(strstr($key, "pluginSpecificCanSeeFrom"))
					$u[str_replace("pluginSpecificCanSeeFrom", "", $key)] = $users[str_replace("pluginSpecificCanSeeFrom", "", $key)];
				
			$users = $u;
		}
		
		$F->setType("useBAusstRechnMode", "select", $this->userdata["useBAusstRechnMode"], array("perCustomer" => "Pro Kunde", "perInvoice" => "Pro Beleg"));
		$F->setType("useBAusstRechnDate", "date", isset($this->userdata["useBAusstRechnDate"]) ? $this->userdata["useBAusstRechnDate"] : null);
		
		$F->setLabel("useBAusstRechnMode", "Modus");
		$F->setLabel("useBAusstRechnDate", "per Datum");
		
		$F->setSaveBericht($this);
		$F->useRecentlyChanged();
		
 		return $phtml.$F;
 	}
	
	function getData(){
		$ac = anyC::get("GRLBM");
		$ac->addJoinV3("Auftrag","AuftragID","=","AuftragID");
		$ac->addJoinV3("Adresse","t2.AdresseID","=","AdresseID");
		
		$ac->addOrderV3("datum");
		
		if(isset($this->userdata["useBAusstRechnDate"]) AND trim($this->userdata["useBAusstRechnDate"]) != ""){
			$date = Util::CLDateParser($this->userdata["useBAusstRechnDate"], "store");
			$ac->addAssocV3("GRLBMpayedDate", ">", $date, "AND", "2");
			$ac->addAssocV3("GRLBMpayedDate", "<=", "0", "OR", "2");
			$ac->addAssocV3("isPayed","=","0", "OR", "2");
			$ac->addAssocV3("datum", "<=", $date);
		} else
			$ac->addAssocV3("isPayed","=","0");
		
		
		if(isset($this->userdata["useBAusstRechnMode"]) AND $this->userdata["useBAusstRechnMode"] == "perCustomer" AND !$this->kundennummer){
				$ac->addGroupV3("kundennummer");
				$ac->setFieldsV3(array(
					"firma",
					"kundennummer",
					"vorname",
					"nachname",
					"GROUP_CONCAT(nummer SEPARATOR ';') AS nummer",
					"SUM(CASE WHEN isR = '1' THEN bruttobetrag ".($this->CustomizerTeilzahlungen ? "- GRLBMTeilzahlungenSumme" : "")." ELSE -1 * bruttobetrag ".($this->CustomizerTeilzahlungen ? "- GRLBMTeilzahlungenSumme" : "")." END) AS bruttobetragRest",
					#"SUM(bruttobetrag ".($this->CustomizerTeilzahlungen ? "- GRLBMTeilzahlungenSumme" : "").") AS bruttobetragRest",
					"SUM(CASE WHEN isR = '1' THEN nettobetrag ELSE -1 * nettobetrag END) AS nettobetrag",
					"0 AS GRLBMID",
					"MIN(datum) AS datum",
					"tel"));
				
				$this->CustomizerZahlungsziel = false;
			#}
			
		} else {
			if($this->kundennummer)
				$ac->addAssocV3 ("kundennummer", "=", $this->kundennummer);
				
			$ac->addOrderV3("nummer");
			$fields = array(
				"firma",
				"kundennummer",
				"vorname",
				"nachname",
				"CONCAT(prefix, nummer) AS nummer", 
				"IF(isR = '1', bruttobetrag, bruttobetrag * -1) AS bruttobetragTotal",
				"IF(isR = '1', bruttobetrag ".($this->CustomizerTeilzahlungen ? "- GRLBMTeilzahlungenSumme" : "").", -1 * bruttobetrag ".($this->CustomizerTeilzahlungen ? "- GRLBMTeilzahlungenSumme" : "")." ) AS bruttobetragRest", 
				"IF(isR = '1', nettobetrag, nettobetrag * -1) AS nettobetrag",
				"GRLBMID", 
				"datum", 
				"tel", 
				"t2.AuftragID AS AuftragID");
			if($this->CustomizerTeilzahlungen){
				$fields[] = "GRLBMTeilzahlungenSumme";
				$fields[] = "GRLBMTeilzahlungen";
			}
			
			if($this->CustomizerZahlungsziel)
				$fields[] = "zahlungsziel";
						
			$ac->setFieldsV3($fields);
		}
		
		$ac->addAssocV3("isR","=","1", "AND", "3");
		$ac->addAssocV3("isG","=","1", "OR", "3");
		$ac->addAssocV3("isWhat","=","T", "OR", "3");
		
		return $ac;
	}

 	public function getPDF($save = false, $kundennummer = 0){
		if($kundennummer > 0){
			$this->userdata["useBAusstRechnMode"] = "perCustomer";
			$this->kundennummer = $kundennummer;
			$this->userdata["useBAusstRechnDate"] = "";
			$this->collection = $this->getData();
		}
		$this->fieldsToShow = array(/*"kundennummer",*/"firma","nummer", "datum2", "nettobetrag","bruttobetragRest");
		if($this->CustomizerZahlungsziel)
			$this->fieldsToShow[] = "zahlungsziel";
		$this->fieldsToShow[] = "datum";

		$this->setType("nummer", "MultiCell");
		$this->setType("firma", "MultiCell8");
		$this->setType("datum", "MultiCell");

		$this->setFieldParser("nummer", "Bericht_AusstehendeRechnungenGUI::nummerParser");
		$this->setFieldParser("datum2", "Bericht_AusstehendeRechnungenGUI::parserDatum", "\$datum");
		$this->setFieldParser("bruttobetragRest", "Bericht_AusstehendeRechnungenGUI::betragParser");
		$this->setFieldParser("nettobetrag", "Bericht_AusstehendeRechnungenGUI::betragParser");
		$this->setFieldParser("datum", "Bericht_AusstehendeRechnungenGUI::dateParser", "\$kundennummer");
		$this->setFieldParser("firma", "Bericht_AusstehendeRechnungenGUI::firmaParser");
		$this->setFieldParser("zahlungsziel", "Bericht_AusstehendeRechnungenGUI::parserDatumZ");
		
		#$this->setLabel("kundennummer", "Kd.Nr.");
		$this->setLabel("nachname", "Name");
		$this->setLabel("datum", "Tage");
		$this->setLabel("datum2", "Datum");
		$this->setLabel("bruttobetragRest", "Brutto");
		$this->setLabel("nettobetrag", "Netto");
		
		#$this->setAlignment("kundennummer", "R");
		$this->setAlignment("bruttobetragRest", "R");
		$this->setAlignment("nettobetrag", "R");
		$this->setAlignment("nummer", "R");
		$this->setAlignment("datum", "R");
		$this->setAlignment("datum2", "R");
		$this->setAlignment("zahlungsziel", "R");

		$this->setColWidth("firma", 60);
		$this->setColWidth("bruttobetragRest", 28);
		$this->setColWidth("nettobetrag", 22);
		$this->setColWidth("nummer", 30);
		#$this->setColWidth("kundennummer",11);
		$this->setColWidth("datum", 0);
		$this->setColWidth("datum2", 20);
		if($this->CustomizerZahlungsziel){
			$this->setColWidth("datum", 15);
			$this->setColWidth("firma", 50);
		}
		$this->setColWidth("zahlungsziel", 25);

		$this->calcSum("Ausstehender Betrag", array("nettobetrag", "bruttobetragRest"));
		$this->setSumParser("bruttobetragRest", "Util::CLNumberParserZ");
		$this->setSumParser("nettobetrag", "Util::CLNumberParserZ");
 		return parent::getPDF($save);
 	}

	public static function parserDatum($w, $p){
		return Util::CLDateParser($p);
	}

	public static function parserDatumZ($w, $p){
		return Util::CLDateParser($w);
	}
	
	public static function nummerParser($w, $p){
		return str_replace(";", "\n", $w);
	}
	
	/*public static function betragParserPC($w){
		$ex = explode(";", $w);
		$t = "";
		foreach($ex AS $b){
			$t .= Util::CLFormatCurrency($b * 1)."\n";
		}
		
		return $t;
	}*/

	public static function betragParser($w){
		return Util::CLFormatCurrency($w * 1);
	}
		
	public static function firmaParser($w, $p, $E){
		return trim(Util::utf8_encode($E->firma)."\n".$E->vorname." ".$E->nachname)."\n".$E->kundennummer;
	}

	public static function dateParser($w, $p){
		return floor((time() - $w) / (24 * 3600)).(Session::isPluginLoaded("mStatistik") ? "\n".Util::utf8_decode("Ø").ZahlungsmoralGUI::getAverage($p) : "");
	}
 } 
 ?>