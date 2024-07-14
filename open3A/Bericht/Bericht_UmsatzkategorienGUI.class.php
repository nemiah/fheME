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

		$this->useVariables(["useUKYear", "useUKMonths", "useUKSources", "useUKDate", "useUKAverage", "useUKNoAssign"]);
		
		$this->useVariableDefault("useUKYear", date("Y"));
		$this->useVariableDefault("useUKMonths", "all");
		$this->useVariableDefault("useUKSources", implode(";:;", $sources));
		$this->useVariableDefault("useUKDate", "datum");
		$this->useVariableDefault("useUKAverage", 0);
		$this->useVariableDefault("useUKNoAssign", 0);
		
		$this->orientation = "L";
 		parent::__construct();
		#if($_SESSION["applications"]->getActiveApplication() != "open3A" 
		#	AND $_SESSION["applications"]->getActiveApplication() != "openFiBu")
		#	return;
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
			"Q4" => "4. Quartal",
			"H1" => "1. Halbjahr",
			"H2" => "2. Halbjahr"];
 		for($i=1;$i<=12;$i++)
			$ops["$i"] = Util::CLMonthName($i);
		
		$F = new HTMLForm(get_class($this), $this->variables, "Einstellungen");
		$F->getTable()->setColWidth(1, 120);
		$F->useRecentlyChanged();
		
		$F->setLabel("useUKYear", "Jahr");
		$F->setLabel("useUKSources", "Quellen");
		$F->setLabel("useUKMonths", "Zeitraum");
		$F->setLabel("useUKDate", "Datum");
		$F->setLabel("useUKAverage", "Durchschnitt?");
		$F->setLabel("useUKNoAssign", "Ohne Zuordnung?");
		
		$F->setType("useUKYear", "select", $this->userdata["useUKYear"], $opsYear);
		$F->setType("useUKMonths", "select", $this->userdata["useUKMonths"], $ops);
		$F->setType("useUKDate", "select", $this->userdata["useUKDate"], ["datum" => "Belegdatum", "payment" => "Zahlungsdatum (falls vorhanden)"]);
		$F->setType("useUKAverage", "checkbox", $this->userdata["useUKAverage"]);
		$F->setType("useUKNoAssign", "checkbox", $this->userdata["useUKNoAssign"]);
		
		$F->setDescriptionField("useUKAverage", "Erzeugt eine extra Spalte für den Durchschnitt pro Monat");
		$F->setDescriptionField("useUKNoAssign", "Sollen die Einträge ohne Zuordnung im ensprechenden Zeitraum auch angezeigt werden?");
		
		$sources = [];
		if(Session::isPluginLoaded("mBanking")){
			
			$kontos = anyC::get("BankingKonto");
			$kontos->addAssocV3("BankingKontoHidden", "=", "0");
			$kontos->addOrderV3("BankingKontoOrder");
			$kontos->addOrderV3("BankingKontoID");
			$kontos->addAssocV3("BankingKontoUserIDs", "=", "", "AND", "2");
			$kontos->addAssocV3("BankingKontoUserIDs", "=", Session::currentUser()->getID(), "OR", "2");
			$kontos->addAssocV3("BankingKontoUserIDs", "LIKE", "".Session::currentUser()->getID().";:;%", "OR", "2");
			$kontos->addAssocV3("BankingKontoUserIDs", "LIKE", "%;:;".Session::currentUser()->getID().";:;%", "OR", "2");
			$kontos->addAssocV3("BankingKontoUserIDs", "LIKE", "%;:;".Session::currentUser()->getID()."", "OR", "2");
			while($K = $kontos->n())
				$sources["mBanking_".$K->getID()] = "Banking ".$K->A("BankingKontoName");
		}
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
		if(strpos($monthStart, "H") === 0){
			$monthStart = (substr($monthStart, 1) - 1) * 6 + 1;
			$monate = 6;
		}
		
		$timeStart = mktime(0, 1, 0, $monthStart, 1, $this->userdata["useUKYear"]);

		$timeEnd = new Datum(mktime(0, 1, 0, $monthStart + $monate, 1, $this->userdata["useUKYear"]));
		if($this->userdata["useUKMonths"] == "all")
			$timeEnd = new Datum(mktime(0, 1, 0, 1, 1, $this->userdata["useUKYear"] + 1));
		
		$ACK = new ArrayCollection();
		
		$this->fieldsToShow = ["name"];
		
		$ACKa = anyC::get("Kategorie", "type", "costs");
		$ACKa->addOrderV3("name", "ASC");
		while($K = $ACKa->n()){
			#$K->getA()->valueP = 0;
			#$K->getA()->valueN = 0;
			
			$DS = new Datum($timeStart);
			while($DS->time() < $timeEnd->time()){
				$f = "value".$DS->Y().$DS->m();
				$K->getA()->$f = 0;
				$DS->addMonth();
			}
			
			$K->getA()->valueAvgN = 0;
			$K->getA()->valueAvgP = 0;
			$K->getA()->sumP = 0;
			$K->getA()->sumN = 0;
			$ACK->add($K);
		}
		
		
		$width = 277;
		$width -= 24;
		if($this->userdata["useUKNoAssign"]){
			$K = new Kategorie(0);
			$K->loadMeOrEmpty();
			$K->changeA("name", "Ohne Zuordnung");
			
			#$K->getA()->valueP = 0;
			#$K->getA()->valueN = 0;
			$DS = new Datum($timeStart);
			while($DS->time() < $timeEnd->time()){
				$f = "value".$DS->Y().$DS->m();
				$K->getA()->$f = 0;
				$DS->addMonth();
			}
			$K->getA()->valueAvgP = 0;
			$K->getA()->valueAvgN = 0;
			$K->getA()->sumP = 0;
			$K->getA()->sumN = 0;
						
			$ACK->add($K);
			$width -= 25;
		}
		
		$ACK->resetPointer();
		
		
		if(Session::isPluginLoaded("mBanking") AND strpos($this->userdata["useUKSources"], "mBanking") !== false){
			$ex = explode(";:;", $this->userdata["useUKSources"]);
			$BKIDs = [];
			foreach($ex AS $v){
				if(strpos($v, "mBanking") !== 0)
					continue;
				
				$BKIDs[] = str_replace("mBanking_", "", $v);
			}
			
			if(count($BKIDs)){
				$ACB = anyC::get("Banking");
				#$ACB->addAssocV3("BankingKategorieID", "!=", "0");
				$ACB->addAssocV3("BankingDate", ">=", $timeStart);
				$ACB->addAssocV3("BankingDate", "<", $timeEnd->time());
				$ACB->addAssocV3("BankingBankingKontoID", "IN", "(".implode(",", $BKIDs).")");
				$ACB->addGroupV3("BankingKategorieID");
				$ACB->addGroupV3("monat");
				$ACB->setFieldsV3(["SUM(BankingValueValue) AS gesamt", "BankingKategorieID", "DATE_FORMAT(FROM_UNIXTIME(BankingDate), '%Y%m') AS monat"]);

				while($B = $ACB->n()){
					while($K = $ACK->n()){
						if($K->getID() != $B->A("BankingKategorieID"))
							continue;
						#print_r($B);
						#if($B->A("gesamt") > 0)
							$K->changeA("value".$B->A("monat"), $K->A("value".$B->A("monat")) + $B->A("gesamt"));
						#else
						#	$K->changeA("value".$K->A("monat"), $K->A("value".$K->A("monat")) + $B->A("gesamt"));

						break;
					}
					$ACK->resetPointer();
				}
			}
		}
		#die();
		
		if(Session::isPluginLoaded("mEingangsbeleg") AND strpos($this->userdata["useUKSources"], "mEingangsbeleg") !== false){
			$ACB = anyC::get("Eingangsbeleg");
			#$ACB->addAssocV3("EingangsbelegKategorieID", "!=", "0");
			if($this->userdata["useUKDate"] == "payment")
				$useDate = "EingangsbelegBezahltAm";
			else
				$useDate = "EingangsbelegDatum";
			
			$ACB->addAssocV3($useDate, ">=", $timeStart);
			$ACB->addAssocV3($useDate, "<", $timeEnd->time());
			
			$ACB->addGroupV3("EingangsbelegKategorieID");
			$ACB->addGroupV3("EingangsbelegTyp");
			$ACB->addGroupV3("monat");
			$ACB->setFieldsV3(["SUM(EingangsbelegBetragBrutto) AS gesamt", "EingangsbelegKategorieID", "EingangsbelegTyp", "DATE_FORMAT(FROM_UNIXTIME($useDate), '%Y%m') AS monat"]);
			
			while($B = $ACB->n()){
					#print_r($B);
				while($K = $ACK->n()){
					if($K->getID() != $B->A("EingangsbelegKategorieID"))
						continue;
					
					#if($B->A("EingangsbelegTyp") != 3)
						$K->changeA("value".$B->A("monat"), $K->A("value").$B->A("monat") + $B->A("gesamt"));
					#else
					#	$K->changeA("value".$K->A("monat"), $K->A("value").$K->A("monat") + $B->A("gesamt"));
					
					break;
				}
				$ACK->resetPointer();
			}
			#die();
		}
		
		if(Session::isPluginLoaded("mKassenbuch") AND strpos($this->userdata["useUKSources"], "mKassenbuch") !== false){
			$ACB = anyC::get("Kassenbuch");
			#$ACB->addAssocV3("KassenbuchKategorieID", "!=", "0");
			$ACB->addAssocV3("KassenbuchDatum", ">=", $timeStart);
			$ACB->addAssocV3("KassenbuchDatum", "<", $timeEnd->time());
			$ACB->addGroupV3("KassenbuchKategorieID");
			$ACB->addGroupV3("monat");
			$ACB->setFieldsV3(["SUM(KassenbuchEinnahme - KassenbuchAusgabe) AS gesamt", "KassenbuchKategorieID", "DATE_FORMAT(FROM_UNIXTIME(KassenbuchDatum), '%Y%m') AS monat"]);
			
			while($B = $ACB->n()){
				while($K = $ACK->n()){
					if($K->getID() != $B->A("KassenbuchKategorieID"))
						continue;
					
					#if($B->A("gesamt") > 0)
					#	$K->changeA("valueP", $K->A("valueP") + $B->A("gesamt"));
					#else
						$K->changeA("value".$B->A("monat"), $K->A("value".$B->A("monat")) + $B->A("gesamt"));
					
					break;
				}
				$ACK->resetPointer();
			}
		}
		
		
 		$this->collection = $ACK;
		
		$firma = "";
		if(Session::isPluginLoaded("mStammdaten")){
			$S = Stammdaten::getActiveStammdaten();

			$firma = $S->A("firmaLang").", ".$S->A("strasse")." ".$S->A("nr").", ".$S->A("plz")." ".$S->A("ort");
		}
		
		$div = 0;
		if(is_numeric($this->userdata["useUKMonths"])){
 			$this->setHeader(trim($firma."\nUmsatzkategorien ".Util::CLMonthName($this->userdata["useUKMonths"])." ".$this->userdata["useUKYear"]));
			$div = 1;
		} elseif(strpos($this->userdata["useUKMonths"], "Q") === 0){
 			$this->setHeader(trim($firma."\nUmsatzkategorien ".(substr($this->userdata["useUKMonths"], 1)).". Quartal ".$this->userdata["useUKYear"]));
			$div = 3;
		} elseif(strpos($this->userdata["useUKMonths"], "H") === 0){
 			$this->setHeader(trim($firma."\nUmsatzkategorien ".(substr($this->userdata["useUKMonths"], 1)).". Halbjahr ".$this->userdata["useUKYear"]));
			$div = 6;
		} else {
 			$this->setHeader(trim($firma."\nUmsatzkategorien ".$this->userdata["useUKYear"]));
			$div = 12;
		}
		
		$sums = [];
		$avgFields = [];
		$DS = new Datum($timeStart);
		while($DS->time() < $timeEnd->time()){
			$f = "value".$DS->Y().$DS->m();
			$avgFields[] = $f;
			$this->setSumParser($f, "Bericht_UmsatzkategorienGUI::parserNumber");
			
			$this->fieldsToShow[] = $f;
			$this->setLabel($f, mb_substr(Util::CLMonthName($DS->m()), 0, 3));
			$this->setColWidth($f, 15);
			$this->setAlignment($f, "R");
			$this->setFieldParser($f,"Util::CLNumberParser");
			$DS->addMonth();
			$width -= 15;
		}
		
		
		$this->collection->resetPointer();
		while($A = $this->collection->n()){
			$avg = 0;
			foreach($avgFields AS $f)
				$avg += $A->A($f);
			
			$avg2 = $avg / $div;
			
			if($avg2 < 0)
				$A->changeA("valueAvgN", $avg2);
			else
				$A->changeA("valueAvgP", $avg2);
			
			if($avg < 0)
				$A->changeA("sumN", $avg);
			else
				$A->changeA("sumP", $avg);
			#$A->changeA("valueT", $A->A("valueP") + $A->A("valueN"));
		}
		
		$this->setColBorderL("sumN");
		$this->setColBorderL($avgFields[0]);
		$this->setColBorderL("valueAvgN");
		
		$this->collection->resetPointer();
		$this->fieldsToShow[] = "sumN";
		$this->fieldsToShow[] = "sumP";
		if($this->userdata["useUKAverage"]){
			$this->fieldsToShow[] = "valueAvgN";
			$this->fieldsToShow[] = "valueAvgP";
		}
 		$this->setLabel("name", "Umsatzkategorie");
 		#$this->setLabel("valueP", "Betrag +");
 		#$this->setLabel("valueN", "Betrag -");
 		$this->setLabel("valueAvgP", "pro Monat");
 		$this->setLabel("valueAvgN", "");
 		$this->setLabel("sumP", "Gesamt");
 		$this->setLabel("sumN", "");
 		
 		#$this->setAlignment("valueP","R");
 		#$this->setAlignment("valueN","R");
 		$this->setAlignment("valueAvgP","R");
 		$this->setAlignment("valueAvgN","R");
 		$this->setAlignment("sumP","R");
 		$this->setAlignment("sumN","R");
		
		$this->setColWidth("valueAvgP", 13);
		$this->setColWidth("valueAvgN", 12);
		$this->setColWidth("sumP", 12);
		$this->setColWidth("sumN", 12);
		
 		$this->setColWidth("name",$width);
		#if($this->userdata["useUKAverage"])
		#	$this->setColWidth("name","125");
		
		#$this->setFieldParser("valueP","Util::PDFCurrencyParser");
		#$this->setFieldParser("valueN","Util::PDFCurrencyParser");
		$this->setFieldParser("valueAvgN","Bericht_UmsatzkategorienGUI::parserNumber");
		$this->setFieldParser("valueAvgP","Bericht_UmsatzkategorienGUI::parserNumber");
		$this->setFieldParser("sumP","Bericht_UmsatzkategorienGUI::parserNumber");
		$this->setFieldParser("sumN","Bericht_UmsatzkategorienGUI::parserNumber");
		
		$this->calcSum("Summe", ["valueAvgP", "valueAvgN", "sumP", "sumN"]);

		$this->setSumParser("valueAvgP", "Util::CLNumberParser");
		$this->setSumParser("valueAvgN", "Util::CLNumberParser");
		$this->setSumParser("sumP", "Util::CLNumberParser");
		$this->setSumParser("sumN", "Util::CLNumberParser");
		#$this->setSumParser("valueN", "Util::PDFCurrencyParser");
		#$this->setSumParser("valueAvg", "Util::PDFCurrencyParser");
		
 		$this->setPageBreakMargin(260);

		
 		return parent::getPDF($save);
 	}
	
	public static function parserNumber($w){
		if($w == 0)
			return "";
		
		return Util::CLNumberParser($w);
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