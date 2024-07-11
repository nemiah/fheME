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

class Bericht_UmsatzkategorienGUI extends Bericht_default implements iBerichtDescriptor {
 	/*public function loadMe(){
 		parent::loadMe();

 		$this->A->useUKYear = "";
 		$this->A->useUKQuartal = "";
 		$this->A->useUKMonth = "";
 	}*/
	
	function __construct() {
		$sources = [];
		if(Session::isPluginLoaded("mBanking"))
			$sources[] = "mBanking";
		if(Session::isPluginLoaded("mKassenbuch"))
			$sources[] = "mKassenbuch";
		if(Session::isPluginLoaded("mEingangsbeleg"))
			$sources[] = "mEingangsbeleg";

		$this->useVariables(["useUKYear", "useUKMonths", "useUKSources", "useUKDate"]);
		
		$this->useVariableDefault("useUKYear", date("Y"));
		$this->useVariableDefault("useUKMonths", "all");
		$this->useVariableDefault("useUKSources", implode(";:;", $sources));
		$this->useVariableDefault("useUKDate", "datum");
		
 		parent::__construct();
		
		if($_SESSION["applications"]->getActiveApplication() != "open3A" 
			AND $_SESSION["applications"]->getActiveApplication() != "openFiBu")
			return;
 	}
 	
	public function getCategory(){
		return "Buchhaltung";
	}
	
 	public function getLabel(){
		if($_SESSION["applications"]->getActiveApplication() != "open3A"
			AND $_SESSION["applications"]->getActiveApplication() != "openFiBu"
			AND $_SESSION["applications"]->getActiveApplication() != "fheME")
			return null;
		
		return "Umsatzkategorien";
 	}
 	
	public function getHTML($id){
 		$phtml = parent::getHTML($id);
		
 		$opsYear = [];
 		for($j = 2007; $j < date("Y") + 2; $j++)
			$opsYear[$j] = $j;
		
		$ops = [
			"all" => Util::CLMonthName(1)." - ".Util::CLMonthName(12),
			"Q1" => "1. Quartal",
			"Q2" => "2. Quartal",
			"Q3" => "3. Quartal",
			"Q4" => "4. Quartal"];
 		for($i=1;$i<=12;$i++)
			$ops["$i"] = Util::CLMonthName($i);
		
		$F = new HTMLForm(get_class($this), $this->variables, "Einstellungen");
		$F->getTable()->setColWidth(1, 120);
		$F->useRecentlyChanged();
		
		$F->setLabel("useUKYear", "Jahr");
		$F->setLabel("useUKSources", "Quellen");
		$F->setLabel("useUKMonths", "Zeitraum");
		$F->setLabel("useUKDate", "Datum");
		
		$F->setType("useUKYear", "select", $this->userdata["useUKYear"], $opsYear);
		$F->setType("useUKMonths", "select", $this->userdata["useUKMonths"], $ops);
		$F->setType("useUKDate", "select", $this->userdata["useUKDate"], ["datum" => "Belegdatum", "payment" => "Zahlungsdatum (falls vorhanden)"]);
		
		$sources = [];
		if(Session::isPluginLoaded("mBanking"))
			$sources["mBanking"] = "Banking";
		if(Session::isPluginLoaded("mKassenbuch"))
			$sources["mKassenbuch"] = "Kassenbuch";
		if(Session::isPluginLoaded("mEingangsbeleg"))
			$sources["mEingangsbeleg"] = "Eingangsbelege";
		
		$F->setType("useUKSources", "select-multiple", $this->userdata["useUKSources"], $sources);
		
		$F->setSaveBericht2($this);
		
		return $phtml.$F;
 	}

 	public function getPDF($save = false){
		$monthStart = $this->userdata["useUKMonths"];
		if($monthStart == "all")
			$monthStart = 1;
		
		$monate = 1;
		if(strpos($monthStart, "Q") === 0){
			$monthStart = (substr($monthStart, 1) - 1) * 3 + 1;
			$monate = 3;
		}
		
		$timeStart = mktime(0, 1, 0, $monthStart, 1, $this->userdata["useUKYear"]);
		
		$timeEnd = new Datum(mktime(0, 1, 0, $monthStart + $monate, 1, $this->userdata["useUKYear"]));
		if($this->userdata["useUKMonths"] == "all")
			$timeEnd = new Datum(mktime(0, 1, 0, 1, 1, $this->userdata["useUKYear"] + 1));
		
		$ACK = anyC::get("Kategorie", "type", "costs");
		$ACK->addOrderV3("name", "ASC");
		while($K = $ACK->n())
			$K->getA()->value = 0;
		
		$ACK->resetPointer();
		
		
		if(Session::isPluginLoaded("mBanking") AND strpos($this->userdata["useUKSources"], "mBanking") !== false){
			$ACB = anyC::get("Banking");
			$ACB->addAssocV3("BankingKategorieID", "!=", "0");
			$ACB->addAssocV3("BankingDate", ">=", $timeStart);
			$ACB->addAssocV3("BankingDate", "<", $timeEnd->time());
			$ACB->addGroupV3("BankingKategorieID");
			$ACB->setFieldsV3(["SUM(BankingValueValue) AS gesamt", "BankingKategorieID"]);
			
			while($B = $ACB->n()){
				while($K = $ACK->n()){
					if($K->getID() != $B->A("BankingKategorieID"))
						continue;
					
					$K->changeA("value", $K->A("value") + $B->A("gesamt"));
					
					break;
				}
				$ACK->resetPointer();
			}
		}
		
		if(Session::isPluginLoaded("mEingangsbeleg") AND strpos($this->userdata["useUKSources"], "mEingangsbeleg") !== false){
			$ACB = anyC::get("Eingangsbeleg");
			$ACB->addAssocV3("EingangsbelegKategorieID", "!=", "0");
			if($this->userdata["useUKDate"] == "payment"){
				$ACB->addAssocV3("EingangsbelegBezahltAm", ">=", $timeStart);
				$ACB->addAssocV3("EingangsbelegBezahltAm", "<", $timeEnd->time());
			} else {
				$ACB->addAssocV3("EingangsbelegDatum", ">=", $timeStart);
				$ACB->addAssocV3("EingangsbelegDatum", "<", $timeEnd->time());
			}
			
			$ACB->addGroupV3("EingangsbelegKategorieID");
			$ACB->addGroupV3("EingangsbelegTyp");
			$ACB->setFieldsV3(["SUM(EingangsbelegBetragBrutto) AS gesamt", "EingangsbelegKategorieID", "EingangsbelegTyp"]);
			
			while($B = $ACB->n()){
					#print_r($B);
				while($K = $ACK->n()){
					if($K->getID() != $B->A("EingangsbelegKategorieID"))
						continue;
					
					if($B->A("EingangsbelegTyp") != 3)
						$K->changeA("value", $K->A("value") - $B->A("gesamt"));
					else
						$K->changeA("value", $K->A("value") + $B->A("gesamt"));
					
					break;
				}
				$ACK->resetPointer();
			}
			#die();
		}
		
		if(Session::isPluginLoaded("mKassenbuch") AND strpos($this->userdata["useUKSources"], "mKassenbuch") !== false){
			$ACB = anyC::get("Kassenbuch");
			$ACB->addAssocV3("KassenbuchKategorieID", "!=", "0");
			$ACB->addAssocV3("KassenbuchDatum", ">=", $timeStart);
			$ACB->addAssocV3("KassenbuchDatum", "<", $timeEnd->time());
			$ACB->addGroupV3("KassenbuchKategorieID");
			$ACB->setFieldsV3(["SUM(KassenbuchEinnahme - KassenbuchAusgabe) AS gesamt", "KassenbuchKategorieID"]);
			
			while($B = $ACB->n()){
				while($K = $ACK->n()){
					if($K->getID() != $B->A("KassenbuchKategorieID"))
						continue;
					
					$K->changeA("value", $K->A("value") + $B->A("gesamt"));
					
					break;
				}
				$ACK->resetPointer();
			}
		}
		
		
 		$this->collection = $ACK;
		
		
		$this->fieldsToShow = ["name", "value"];
		
 		$this->setLabel("name", "Umsatzkategorie");
 		$this->setLabel("value", "Betrag");
 		
 		$this->setAlignment("value","R");
		
 		#$this->setLabel("EingangsbelegLieferantID","Lieferant");
		
 		$this->setColWidth("name","170");
 	
		#$this->setLineParser("after", "Bericht_RechnungsAusgangsbuchGUI::parserLine");
		
		$this->setFieldParser("value","Util::PDFCurrencyParser");
		
		#$this->setLineParser("before", "Bericht_RechnungsEingangsbuchGUI::parserLine");
		
		$this->calcSum("Summe",array("value"));

		$this->setSumParser("value", "Util::PDFCurrencyParser");
		#$this->setSumParser("VSt", "Bericht_RechnungsEingangsbuchGUI::parserSumVSt");
		#$this->setSumParser("EingangsbelegBetragBrutto", "Util::PDFCurrencyParser");

		#$this->setValueParser("Netto", "Bericht_RechnungsAusgangsbuchGUI::valueParser");
		#$this->setValueParser("VSt", "Bericht_RechnungsAusgangsbuchGUI::valueParser");
		#$this->setValueParser("EingangsbelegBetragBrutto", "Bericht_RechnungsAusgangsbuchGUI::valueParser");

 		#$this->setType("Summe","MultiCell");
 		$this->setType("EingangsbelegBezahltAm","MultiCell8");
 		$this->setType("EingangsbelegLieferantID","MultiCell");
 		$this->setPageBreakMargin(260);
		$firma = "";
		if(Session::isPluginLoaded("Stammdaten")){
			$S = Stammdaten::getActiveStammdaten();

			$firma = $S->A("firmaLang").", ".$S->A("strasse")." ".$S->A("nr").", ".$S->A("plz")." ".$S->A("ort");
		}
		
		if(is_numeric($this->userdata["useUKMonths"])){
 			$this->setHeader($firma."\nUmsatzkategorien ".Util::CLMonthName($this->userdata["useUKMonths"])." ".$this->userdata["useUKYear"]);
		} elseif(strpos($this->userdata["useUKMonths"], "Q") === 0){
 			$this->setHeader($firma."\nUmsatzkategorien ".(substr($this->userdata["useUKMonths"], 1)).". Quartal ".$this->userdata["useUKYear"]);
		} else {
 			$this->setHeader($firma."\nUmsatzkategorien ".$this->userdata["useUKYear"]);
		}

		
 		return parent::getPDF($save);
 	}
	
	public static function parserSumVSt($w){
		if($w < 1000)
			return Util::PDFCurrencyParser($w);
		else
			return "\n".Util::PDFCurrencyParser($w);
	}
	
	public static function parserLine($pdf, $E){
		if($E->A("EingangsbelegLieferantID") != "0"){
			$L = new Lieferant($E->A("EingangsbelegLieferantID"));
			$E->changeA("EingangsbelegLieferantID", $L->getFormattedAddress(Stammdaten::getActiveStammdaten()));
			if($L->A("LieferantKonto") != "0")
				$E->changeA("LieferantKonto", $L->A("LieferantKonto"));
		}
		
		if($E->A("EingangsbelegLieferantID") == "0")
			$E->changeA("EingangsbelegLieferantID", $E->A("EingangsbelegLieferantName"));
	}
	
	/*public static function parserLieferant($v, $t, $E){
		if($v != "0"){
			$L = new Lieferant($v);
			return $L->getFormattedAddress(Stammdaten::getActiveStammdaten());
		}
		
		return $E->A("EingangsbelegLieferantName");
	}*/
	
	
	#public static function valueParser($v, $t, $E){
	#	return $v;
	#}

 	public static function parserPayed($v, $t, $E){
		if($v == 0)
			return "";
		
 		return Util::CLDateParser($v)."\n".GRLBM::getPaymentVia($E->A("EingangsbelegZahlungVia"));
 	}
 } 
 ?>