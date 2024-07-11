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

class Bericht_RechnungsAusgangsbuchGUI extends Bericht_default implements iBerichtDescriptor {
	protected $RAHeader = "Rechnungsausgang";
	public $CustomizerTeilzahlungen = false;
	public static $includeCanceledInSum = false;
	public static $showTeilzahlungen = true;
	
 	public function loadMe(){
 		parent::loadMe();

 		$this->A->useRAYear = "";
 		$this->A->useRAQuartal = "";
 		$this->A->useRAMonth = "";
 		$this->A->useRAHidePositions = "";
 		$this->A->useRAOrderBy = "";
		$this->A->useRALocked = "";
 	}
	
	function __construct($month = null, $year = null, $quartal = null, $force = false, $usePayedDate = false) {
		$this->useVariables(["useRAHidePositions", "useRALocked"]);
		
 		parent::__construct();
		
		$this->customize();
		
		if($month AND $month > -1 OR $force)
			$this->userdata["useRAMonth"] = $month;
		
		if($year OR $force)
			$this->userdata["useRAYear"] = $year;
		
		if($quartal OR $force)
			$this->userdata["useRAQuartal"] = $quartal;
		

		if($_SESSION["applications"]->getActiveApplication() != "open3A"
			AND $_SESSION["applications"]->getActiveApplication() != "openFiBu")
			return;
		
 		if(!$_SESSION["S"]->checkForPlugin("Auftraege"))
			return;
 		
 		
		$datum = "datum";
		$datumZ = "ZahlungsaufforderungDatum";
		if($usePayedDate){
			$datum = "GRLBMpayedDate";
			$datumZ = "ZahlungsaufforderungBezahltAm";
		}
		
		$DB = new DBStorage();
		$C = $DB->getConnection();
		$C->query("SET SQL_BIG_SELECTS=1");
		
 		$ac = anyC::get("GRLBM");
		
 		$ac->addAssocV3("isR","=","1","AND","2");
 		$ac->addAssocV3("isG","=","1","OR","2");
 		$ac->addAssocV3("isWhat","=","T","OR","2");
		
		if(isset($this->userdata["useRAOrderBy"]) AND $this->userdata["useRAOrderBy"] == "nummer"){
			$ac->addOrderV3("nummer", "ASC");
			$ac->addOrderV3($datum,"ASC");
		} elseif(isset($this->userdata["useRAOrderBy"]) AND $this->userdata["useRAOrderBy"] == "kundennummer"){
			$ac->addOrderV3("kundennummer","ASC");
			$ac->addOrderV3("nummer","ASC");
		} else {
			$ac->addOrderV3($datum,"ASC");
			$ac->addOrderV3("nummer","ASC");
		}
		
		if(isset($this->userdata["useRALocked"]) AND $this->userdata["useRALocked"] == "locked"){
			$ac->addAssocV3("isPrinted", "=", "1", "AND", "4");
			$ac->addAssocV3("isPayed", "=", "1", "OR", "4");
			$ac->addAssocV3("isEMailed", "=", "1", "OR", "4");
		}
		
		$ac->addJoinV3("Posten","GRLBMID","=","GRLBMID");
		$ac->addJoinV3("Auftrag","AuftragID","=","AuftragID");
		$ac->addJoinV3("Adresse","t3.AdresseID","=","AdresseID");
		$ac->addJoinV3("Kappendix", "t3.kundennummer", "=", "kundennummer");
		$ac->addGroupV3("t1.GRLBMID");
		
		//Better like this?
		#"if(isG='1' OR isWhat='T', ROUND(ABS(bruttobetrag) + 0.0000000001, 2) * -1 ,ROUND(bruttobetrag + 0.0000000001, 2)) AS Summe", //also Bericht_EUER
		#"if(isG='1' OR isWhat='T', ROUND(ABS(steuern) + 0.0000000001, 2) * -1, ROUND(steuern + 0.0000000001, 2)) AS USt",
		#"if(isG='1' OR isWhat='T', ROUND(ABS(nettobetrag) + 0.0000000001, 2) * -1, ROUND(nettobetrag + 0.0000000001, 2)) AS Netto",

		$fields = array(
			"nummer",
			"datum",
			"datum AS datumSort", //Bericht_EUER
			"if(isG='1' OR isWhat='T', ROUND(ABS(bruttobetrag)*-1,2) ,ROUND(bruttobetrag,2)) AS Summe", //also Bericht_EUER
			"if(isG='1' OR isWhat='T', ROUND(ABS(steuern)*-1,2), ROUND(steuern,2)) AS USt",
			"if(isG='1' OR isWhat='T', ROUND(ABS(nettobetrag)*-1,2), ROUND(nettobetrag,2)) AS Netto",
			"firma",
			"isG",
			"isAbschlussrechnung",
			"t1.AuftragID",
			"GRLBMpayedDate",
			"GRLBMpayedVia",
			"isPayed",
			"bruttobetrag", //Bericht_EUER
			"prefix", //Bericht_EUER
			"CASE WHEN isR='1' THEN 'R' WHEN isG='1' THEN 'G' WHEN isWhat='T' THEN 'T' END AS belegTyp",
			"CONCAT(IF(firma='',CONCAT(vorname,' ', nachname),firma),'\n',strasse,' ',nr,'\n',plz,' ',ort, '\n', land) AS Name",
			"CONCAT(IF(firma='',CONCAT(vorname,' ', nachname),firma)) AS NameKurz", //Bericht_EUER
			"IF(t3.kundennummer > 0, t3.kundennummer, '') AS kundennummer",
			"KappendixErloeskonto",
			"t1.GRLBMID AS GRLBMID",
			"payedWithSkonto",//Bericht_EUER
			"gebuehren",
			"nettobetrag"//Bericht_EUER
		);
		
		if($this->CustomizerTeilzahlungen AND self::$showTeilzahlungen)
			$fields[] = "GRLBMTeilzahlungenSumme";

		$ac->setFieldsV3($fields);
		
		$ac->setParser("datum","Util::CLDateParser");
		
		
		
		$acZ = anyC::get("Zahlungsaufforderung");
		$acZ->addOrderV3($datumZ, "ASC");
		$acZ->addOrderV3("ZahlungsaufforderungNummer", "ASC");
		
		$acZ->addJoinV3("GRLBM", "ZahlungsaufforderungGRLBMID", "=", "GRLBMID");
		$acZ->addJoinV3("Posten","t3.GRLBMID","=","GRLBMID");
		$acZ->addJoinV3("Auftrag","t2.AuftragID","=","AuftragID");
		$acZ->addJoinV3("Adresse","t4.AdresseID","=","AdresseID");
		$acZ->addJoinV3("Kappendix", "t4.kundennummer", "=", "kundennummer");
		$acZ->addGroupV3("t2.GRLBMID");
		$fields[0] = "ZahlungsaufforderungNummer AS nummer";
		$fields[9] = "t2.AuftragID";
		$fields[15] = "isWhat AS belegTyp";
		$fields[18] = "IF(t4.kundennummer > 0, t4.kundennummer, '') AS kundennummer";
		$fields[20] = "t2.GRLBMID AS GRLBMID";
		$acZ->setFieldsV3($fields);
		$acZ->setParser("datum","Util::CLDateParser");
		
		if($this->userdata != null) {
			if(isset($this->userdata["useRAMonth"]) AND $this->userdata["useRAMonth"] != ""){
	 			$D = new Datum(Util::parseDate("de_DE", "1.".$this->userdata["useRAMonth"].".".$this->userdata["useRAYear"]));
				$ac->addAssocV3($datum,">=", $D->time(),"AND","3");
				$acZ->addAssocV3($datumZ,">=", $D->time(),"AND","3");
				$D->addMonth();
				$ac->addAssocV3($datum,"<", $D->time(),"AND","3");
				$acZ->addAssocV3($datumZ,"<", $D->time(),"AND","3");
			} elseif(isset($this->userdata["useRAQuartal"]) AND $this->userdata["useRAQuartal"] != ""){
				switch($this->userdata["useRAQuartal"]){
					case "1":
			 			$D = new Datum(Util::parseDate("de_DE", "1.1.".$this->userdata["useRAYear"]));
			 			$D2 = new Datum(Util::parseDate("de_DE", "1.4.".$this->userdata["useRAYear"]));
					break;
					case "2":
			 			$D = new Datum(Util::parseDate("de_DE", "1.4.".$this->userdata["useRAYear"]));
			 			$D2 = new Datum(Util::parseDate("de_DE", "1.7.".$this->userdata["useRAYear"]));
					break;
					case "3":
			 			$D = new Datum(Util::parseDate("de_DE", "1.7.".$this->userdata["useRAYear"]));
			 			$D2 = new Datum(Util::parseDate("de_DE", "1.10.".$this->userdata["useRAYear"]));
					break;
					case "4":
			 			$D = new Datum(Util::parseDate("de_DE", "1.10.".$this->userdata["useRAYear"]));
			 			$D2 = new Datum(Util::parseDate("de_DE", "1.1.".($this->userdata["useRAYear"]+1)));
					break;
				}
	 			
				$ac->addAssocV3($datum, ">=", $D->time(), "AND", "3");
				$ac->addAssocV3($datum, "<", $D2->time(), "AND", "3");
				
				$acZ->addAssocV3($datumZ, ">=", $D->time(), "AND", "3");
				$acZ->addAssocV3($datumZ, "<", $D2->time(), "AND", "3");
			} elseif(isset($this->userdata["useRAYear"]) AND $this->userdata["useRAYear"] != ""){
	 			$D = new Datum(Util::parseDate("de_DE", "1.1.".$this->userdata["useRAYear"]));
	 			$D2 = new Datum(Util::parseDate("de_DE", "1.1.".($this->userdata["useRAYear"]+1)));
				$ac->addAssocV3($datum,">=", $D->time(),"AND","3");
				$ac->addAssocV3($datum,"<", $D2->time(),"AND","3");
				
				$acZ->addAssocV3($datumZ,">=", $D->time(),"AND","3");
				$acZ->addAssocV3($datumZ,"<", $D2->time(),"AND","3");
			} elseif(isset($this->userdata["useRADay"]) AND $this->userdata["useRADay"] != ""){
	 			$D = Util::parseDate("de_DE", $this->userdata["useRADay"]);
				$ac->addAssocV3($datum,"=", $D,"AND","3");
				
				$acZ->addAssocV3($datumZ,"=", $D,"AND","3");
			}
			
		}
		$ARC = $ac;
			
		
		if(Session::isPluginLoaded("mZahlungsaufforderung")){
			$ARC = new ArrayCollection();
			while($G = $ac->n())
				$ARC->add($G);
		
			try {
				#$G = $acZ->n();
				while($G = $acZ->n())
					$ARC->add($G);
			} catch (Exception $e){
				echo "<pre>". get_class($e)."\n";
				print_r(DBStorage::$lastQuery[count(DBStorage::$lastQuery) - 1]);
				echo "</pre>";
				echo $e->getField();
				die();
			}
		}
		
 		$this->collection = $ARC;
 	}
	
	public function getCategory(){
		return "Buchhaltung";
	}
 	
 	public function getLabel(){
		if($_SESSION["applications"]->getActiveApplication() != "open3A"
			AND $_SESSION["applications"]->getActiveApplication() != "openFiBu")
			return null;
		
 		if($_SESSION["S"]->checkForPlugin("Auftraege"))
			return "Rechnungsausgangsbuch";
 		
		return null;
 	}
 	
	public function getHTML($id){
 		$phtml = parent::getHTML($id);
 		$ops = "";
 		$opsQ = "";
 		$opsYear = "";
 		
 		for($j = 2007; $j < date("Y") + 2; $j++)
			$opsYear .= "<option value=\"$j\" ".(($this->userdata != null AND isset($this->userdata["useRAYear"]) AND $this->userdata["useRAYear"] == $j) ? "selected=\"selected\"" : "").">".$j."</option>";
 		
 		for($i=1;$i<=12;$i++)
 			$ops .= "<option value=\"$i\" ".(($this->userdata != null AND isset($this->userdata["useRAMonth"]) AND $this->userdata["useRAMonth"] == $i) ? "selected=\"selected\"" : "").">".Util::CLMonthName($i)."</option>";
 		
 		$opsShowPos = "<option value=\"\" ".($this->userdata["useRAHidePositions"] == "" ? "selected=\"selected\"" : "").">".T::_("anzeigen")."</option>
			<option value=\"yes\" ".($this->userdata["useRAHidePositions"] == "yes" ? "selected=\"selected\"" : "").">".T::_("nicht anzeigen")."</option>";
 		
 		$opsOrderBy = "<option value=\"\" ".($this->userdata["useRAOrderBy"] == "" ? "selected=\"selected\"" : "").">".T::_("Datum")."</option>
			<option value=\"nummer\" ".($this->userdata["useRAOrderBy"] == "nummer" ? "selected=\"selected\"" : "").">".T::_("Belegnummer")."</option>
			<option value=\"kundennummer\" ".($this->userdata["useRAOrderBy"] == "kundennummer" ? "selected=\"selected\"" : "").">".T::_("Kundennummer")."</option>";
 		
 		$opsLocked = "<option value=\"\" ".($this->userdata["useRALocked"] == "" ? "selected=\"selected\"" : "").">".T::_("Alle Belege")."</option>
			<option value=\"locked\" ".($this->userdata["useRALocked"] == "locked" ? "selected=\"selected\"" : "").">".T::_("Nur gesperrte Belege")."</option>";
 		

 		$monat = new HTMLTable(2);
 		$monat->setColWidth(1, "120px");
 		$monat->addRow(array("<label>".T::_("Monat").":</label>","<select id=\"useRAMonth\" name=\"useRAMonth\">$ops</select>"));
 		$monat->addRow(array("<label>".T::_("Jahr").":</label>","<select id=\"useRAYear\" name=\"useRAYear\">$opsYear</select>"));
 		$monat->addRow(array("<label>".T::_("Positionen").":</label>","<select id=\"useRAHidePositions\" name=\"useRAHidePositions\">$opsShowPos</select>"));
 		$monat->addRow(array("<label>".T::_("Sortierung").":</label>","<select id=\"useRAOrderBy\" name=\"useRAOrderBy\">$opsOrderBy</select>"));
		$monat->addRow(array("<label>".T::_("Gesperrt").":</label>","<select id=\"useRALocked\" name=\"useRALocked\">$opsLocked</select>"));
		$monat->addRow(array("<input type=\"button\" style=\"background-image: url(./images/i2/save.gif);\" value=\"".T::_("Einstellungen speichern")."\" onclick=\"saveBericht('".get_class($this)."','BerichtMonth');\" />"));
 		$monat->addRowColspan(1, 2);
 			
 		for($i=1;$i<=4;$i++)
 			$opsQ .= "<option value=\"$i\" ".(($this->userdata != null AND isset($this->userdata["useRAQuartal"]) AND $this->userdata["useRAQuartal"] == $i) ? "selected=\"selected\"" : "").">".$i."</option>";
 		
 		$quartal = new HTMLTable(2);
 		$quartal->setColWidth(1, "120px");
 		$quartal->addRow(array("<label>".T::_("Quartal").":</label>","<select id=\"useRAQuartal\" name=\"useRAQuartal\">$opsQ</select>"));
 		$quartal->addRow(array("<label>".T::_("Jahr").":</label>","<select id=\"useRAYear\" name=\"useRAYear\">$opsYear</select>"));
 		$quartal->addRow(array("<label>".T::_("Positionen").":</label>","<select id=\"useRAHidePositions\" name=\"useRAHidePositions\">$opsShowPos</select>"));
 		$quartal->addRow(array("<label>".T::_("Sortierung").":</label>","<select id=\"useRAOrderBy\" name=\"useRAOrderBy\">$opsOrderBy</select>"));
		$quartal->addRow(array("<label>".T::_("Gesperrt").":</label>","<select id=\"useRALocked\" name=\"useRALocked\">$opsLocked</select>"));
		$quartal->addRow(array("<input type=\"button\" style=\"background-image: url(./images/i2/save.gif);\" value=\"".T::_("Einstellungen speichern")."\" onclick=\"saveBericht('".get_class($this)."','BerichtQuartal');\" />"));
 		$quartal->addRowColspan(1, 2);
 		
 		
 		$jahr = new HTMLTable(2);
 		$jahr->setColWidth(1, "120px");
 		$jahr->addRow(array("<label>".T::_("Jahr").":</label>","<select id=\"useRAYear\" name=\"useRAYear\">$opsYear</select>"));
 		$jahr->addRow(array("<label>".T::_("Positionen").":</label>","<select id=\"useRAHidePositions\" name=\"useRAHidePositions\">$opsShowPos</select>"));
 		$jahr->addRow(array("<label>".T::_("Sortierung").":</label>","<select id=\"useRAOrderBy\" name=\"useRAOrderBy\">$opsOrderBy</select>"));
		$jahr->addRow(array("<label>".T::_("Gesperrt").":</label>","<select id=\"useRALocked\" name=\"useRALocked\">$opsLocked</select>"));
		$jahr->addRow(array("<input type=\"button\" style=\"background-image: url(./images/i2/save.gif);\" value=\"".T::_("Einstellungen speichern")."\" onclick=\"saveBericht('".get_class($this)."','BerichtJahr');\" />"));
 		$jahr->addRowColspan(1, 2);

 		if(isset($this->userdata["useRAMonth"]) AND $this->userdata["useRAMonth"] != "") 
			$t = "monat";
		elseif(isset($this->userdata["useRAQuartal"]) AND $this->userdata["useRAQuartal"] != "") 
			$t = "quartal";
		else 
			$t = "jahr";
			
 		return $phtml."
 		<table>
 			<tr>
 				<td>
					<div onclick=\"\$j('.periodSelection').hide(); \$j('#Monat').show();\" class=\"backgroundColor1\" style=\"padding:5px;cursor:pointer;\">
						<span style=\"float:right;\">".T::_("Hier klicken für Optionen")."</span><b>".T::_("Monat").":</b>
					</div>
					<div id=\"Monat\" class=\"periodSelection\"".($t != "monat" ? "style=\"display:none;\"" : "").">
			 		<form id=\"BerichtMonth\">
			 			".$monat->getHTML()."
			 		</form>
			 		</div>
					<div onclick=\"\$j('.periodSelection').hide(); \$j('#Quartal').show();\" class=\"backgroundColor1\" style=\"padding:5px;cursor:pointer;margin-top:5px;\">
						<span style=\"float:right;\">".T::_("Hier klicken für Optionen")."</span><b>".T::_("Quartal").":</b>
					</div>
					<div id=\"Quartal\" class=\"periodSelection\" ".($t != "quartal" ? "style=\"display:none;\"" : "").">
			 		<form id=\"BerichtQuartal\">
			 			".$quartal->getHTML()."
			 		</form>
			 		</div>
					<div onclick=\"\$j('.periodSelection').hide(); \$j('#Jahr').show();\" class=\"backgroundColor1\" style=\"padding:5px;cursor:pointer;margin-top:5px;\">
						<span style=\"float:right;\">".T::_("Hier klicken für Optionen")."</span><b>".T::_("Jahr").":</b>
					</div>
					<div id=\"Jahr\" class=\"periodSelection\" ".($t != "jahr" ? "style=\"display:none;\"" : "").">
			 		<form id=\"BerichtJahr\">
			 			".$jahr->getHTML()."
			 		</form>
			 		</div>
		 		</td>
	 		</tr>
 		</table>";
 	}

 	public function getPDF($save = false){
		
 		#while($e = $this->collection->getNextEntry()){
 			#print_r($e);

 			#if($e->getA()->Summe == "0.000" AND $e->getA()->USt == "0.000" AND $e->getA()->Netto == "0.000"){
 				#$v = $e->getSumOfPosten(true);
 				
 				#$e->changeA("Summe", $v[2] * ($e->A("isG") == "1" ? -1 : 1));
 				#$e->changeA("USt", $v[1] * ($e->A("isG") == "1" ? -1 : 1));
 				#$e->changeA("Netto", $v[0] * ($e->A("isG") == "1" ? -1 : 1));
 			#}
 		#}
 		#$this->collection->resetPointer();
 		
 		$Months = Datum::getGerMonthArray();
 		
 		$this->setLabel("nummer", "Beleg Nr.");
 		
 		$this->setAlignment("Summe","R");
 		$this->setAlignment("nummer","R");
 		$this->setAlignment("USt","R");
 		$this->setAlignment("Netto","R");
 		$this->setAlignment("kundennummer","R");
 		$this->setAlignment("KappendixErloeskonto","R");
		
 		if(Session::isPluginLoaded("Uebersicht"))
			$this->fieldsToShow = array("nummer","belegTyp","datum","Netto","USt","Summe","GRLBMpayedDate","Name", "kundennummer");
 		else 
			$this->fieldsToShow = array("nummer","belegTyp","datum","Netto","USt","Summe","Name", "kundennummer");
		
		$showKonto = false;
		if(Session::isPluginLoaded("mexportDatev") OR Session::isPluginLoaded("mexportLexware"))
			$showKonto = true;
		
		if($showKonto)
			$this->fieldsToShow[] = "KappendixErloeskonto";
		
 		$this->setLabel("Name","Kunde");
 		$this->setLabel("Summe","Brutto");
 		$this->setLabel("GRLBMpayedDate","Bez. am");
 		$this->setLabel("belegTyp","");
 		$this->setLabel("kundennummer","KdNr");
 		$this->setLabel("KappendixErloeskonto","Konto");
		
 		$this->setColWidth("Name","50");
 		$this->setColWidth("belegTyp","5");
 		$this->setColWidth("kundennummer","10");
 		$this->setColWidth("GRLBMpayedDate","25");

		if($showKonto){
			$this->defaultFontSize = 8.5;
			$this->setColWidth("Name","39");
			$this->setColWidth("GRLBMpayedDate","21");
			$this->setColWidth("USt", "15");
		}
		
		if($this->userdata["useRAHidePositions"] != "yes")
			$this->setLineParser("after", "Bericht_RechnungsAusgangsbuchGUI::parserLine");
		
		$this->setFieldParser("Summe","Bericht_RechnungsAusgangsbuchGUI::parserSumme");
		$this->setFieldParser("USt","Util::PDFCurrencyParser");
		$this->setFieldParser("Netto","Util::PDFCurrencyParser");
		$this->setFieldParser("GRLBMpayedDate","Bericht_RechnungsAusgangsbuchGUI::GRLBMpayedDateParser");
		$this->calcSum(" ",array("Netto","USt","Summe"));
		
		$this->setSumParser("Netto", "Util::PDFCurrencyParser");
		$this->setSumParser("USt", "Bericht_RechnungsAusgangsbuchGUI::parserSumUSt");
		$this->setSumParser("Summe", "Util::PDFCurrencyParser");

		$this->setValueParser("Netto", "Bericht_RechnungsAusgangsbuchGUI::valueParser");
		$this->setValueParser("USt", "Bericht_RechnungsAusgangsbuchGUI::valueParser");
		$this->setValueParser("Summe", "Bericht_RechnungsAusgangsbuchGUI::valueParserSumme");

 		#$this->setType("Summe","MultiCell");
 		$this->setType("Name","MultiCell");
 		$this->setType("Summe","Custom");
 		$this->setType("GRLBMpayedDate","MultiCell8");
 		$this->setPageBreakMargin(260);
 		$S = Stammdaten::getActiveStammdaten();
 		
 		$firma = $S->getA()->firmaLang.", ".$S->getA()->strasse." ".$S->getA()->nr.", ".$S->getA()->plz." ".$S->getA()->ort;
 		
		if(isset($this->userdata["useRAMonth"]) AND $this->userdata["useRAMonth"] != ""){
 			$this->setHeader($firma."\n$this->RAHeader ".($Months[$this->userdata["useRAMonth"]])." ".$this->userdata["useRAYear"]);
		} elseif(isset($this->userdata["useRAQuartal"]) AND $this->userdata["useRAQuartal"] != ""){
 			$this->setHeader($firma."\n$this->RAHeader ".$this->userdata["useRAQuartal"].". Quartal ".$this->userdata["useRAYear"]);
		} else {
 			$this->setHeader($firma."\n$this->RAHeader ".$this->userdata["useRAYear"]);
		}

 		return parent::getPDF($save);
 	}
	
	public static function parserSumUSt($w){
		if($w < 10000)
			return Util::PDFCurrencyParser($w);
		else
			return "\n".Util::PDFCurrencyParser($w);
	}
	
	public function CustomCellSumme(FPDF $pdf, $width, $height, $content, $align){
		$ex = explode("\n", $content);
		if(count($ex) == 1){
			$pdf->Cell($width, $height, $content, 0, 0, $align);
			return false;
		}
		
		if(count($ex) > 1){
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			foreach($ex AS $k => $line){
				$pdf->SetXY($x, $y + $height * $k);
				
				if(substr($line, 0, 1) == "=")
					$pdf->SetTextColor(255, 0, 0);
				
				$pdf->Cell($width, $height, $line, 0, 0, $align);
				
				if(substr($line, 0, 1) == "=")
					$pdf->SetTextColor(0, 0, 0);
			}
			/*$pdf->Cell($width, $height, $ex[0], 0, 0, $align);
			
			$pdf->SetXY($x, $y + $height);
			$pdf->Cell($width, $height, $ex[1], 0, 0, $align);
			
			$pdf->SetXY($x, $y + $height * 2);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->Cell($width, $height, $ex[2], 0, 0, $align);
			$pdf->SetTextColor(0, 0, 0);*/
			
			return false;
		}
		
		#$pdf->MultiCell($width, $height, $content, 0, $align);
		#return true;
	}
	
	public static function parserSumme($w, $l, $A, $E){
		$TZ = "";
		$sum = $w;
		$changes = false;
		
		if($E->A("payedWithSkonto") > 0){
			$TZ .= "\n- ".$E->A("payedWithSkonto")."% Skonto";
			$sum = Util::kRound($sum * ((100 - $E->A("payedWithSkonto")) / 100));
			$changes = true;
		}
		
		if($E->A("gebuehren") > 0){
			$TZ .= "\n+ ".Util::PDFCurrencyParser($E->A("gebuehren"));
			$sum += $E->A("gebuehren");
			$changes = true;
		}
		
		if($E->A("GRLBMTeilzahlungenSumme") AND $E->A("GRLBMTeilzahlungenSumme") > 0){
			$TZ .= "\n- ".Util::PDFCurrencyParser($E->A("GRLBMTeilzahlungenSumme"));
			$sum -= $E->A("GRLBMTeilzahlungenSumme");
			$changes = true;
		}
		
		if($changes)
			$TZ .= "\n= ".Util::PDFCurrencyParser($sum);
		
		
		return Util::PDFCurrencyParser($w).$TZ;
	}
	
	public static function parserLine(FPDF $fpdf, $E){
		$fpdf->SetDrawColor(190, 190, 190);
		$fpdf->Line(10,$fpdf->GetY(),200, $fpdf->GetY());
		$fpdf->SetDrawColor(0, 0, 0);
		
		$fpdf->SetFontSize(8);
		$fpdf->SetTextColor(100);
		$AC = anyC::get("Posten", "GRLBMID", $E->A("GRLBMID"));
		while($P = $AC->getNextEntry()){
			if($fpdf->GetY() > 280)
				$fpdf->AddPage ();
			
			$nettopreis = $P->A("preis");
			$bruttopreis = $P->A("bruttopreis");
			if($nettopreis > 0 AND $bruttopreis == 0)
				$bruttopreis = $nettopreis * ($P->A("mwst") / 100 + 1);
			
			if($nettopreis < 0 AND $bruttopreis == 0)
				$bruttopreis = $nettopreis * ($P->A("mwst") / 100 + 1);
			
			if($P->A("PostenIsAlternative") !== null AND $P->A("PostenIsAlternative") > 0){
				$nettopreis = 0;
				$bruttopreis = 0;
			}
			
			$rabatt = 1;
			if($P->A("rabatt")){
				$rabatt = (100 - $P->A("rabatt")) / 100;
				
				$nettopreis *= $rabatt;
				$bruttopreis *= $rabatt;
			}
			
			$fpdf->Cell8(10, 4.5, "");
			$fpdf->Cell8(35, 4.5, substr($P->A("name"), 0, 25));
			$fpdf->Cell(20, 4.5, Util::PDFCurrencyParser($nettopreis * $P->A("menge")), "", 0, "R");
			$fpdf->Cell(20, 4.5, Util::PDFCurrencyParser(($bruttopreis - $nettopreis) * $P->A("menge")), "", 0, "R");
			$fpdf->Cell(20, 4.5, Util::PDFCurrencyParser($bruttopreis * $P->A("menge")), "", 0, "R");
			$fpdf->Cell(20, 4.5, Util::CLNumberParserZ($P->A("mwst"))."%", "", 0, "R");
			$fpdf->Cell(0, 4.5, $P->A("PostenLeistungszeitraum"), "", 1, "R");
			
		}
		
		if($AC->numLoaded() == 0){
			$fpdf->Cell8(20, 4.5, "");
			$fpdf->Cell8(25, 4.5, "Keine Positionen", "", 1);
		}
		
		$fpdf->SetTextColor(0);
		$fpdf->SetFontSize(9);
		$fpdf->ln(5);
	}
	
	public static function valueParserSumme($v, $t, $E){
		if(!self::$includeCanceledInSum AND $E->isPayed == "2")
			return 0;
		
		return $v + $E->gebuehren;
	}
	
	public static function valueParser($v, $t, $E){
		if(!self::$includeCanceledInSum AND $E->isPayed == "2")
			return 0;
		
		return $v;
	}

 	public static function GRLBMpayedDateParser($v, $t, $E){
		$E->Netto = 0;
		if($E->isPayed == "2")
			return "storniert";
		

 		if($E->isPayed == "0") return "";
 		if($v == 0) return "";
 		else return Util::CLDateParser($v).(Session::isPluginLoaded ("mZahlungsart") ? "\n".GRLBM::getPaymentVia($E->A("GRLBMpayedVia")) : "");
 	}
 } 
 ?>