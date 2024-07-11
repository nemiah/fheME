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

class Bericht_ControllingGUI extends Bericht_default implements iBerichtDescriptor {
	function __construct() {
		$this->useVariables(array("useBCOUserID", "useBCOStart", "useBCOEnde"));
		$this->useVariableDefault("useBCOUserID", 0);
		
 		parent::__construct();

		if(Applications::activeApplication() != "lightCRM")
			return;
		
 	}
 	
	function hasSettings() {
		return false;
	}
	
 	public function getLabel(){
		if(Applications::activeApplication() != "lightCRM" OR !Session::isPluginLoaded("mStatistik"))
			return null;
		
 		#if(!Session::isPluginLoaded("mStatistik")) return null;
 		
		return "Controlling";
 	}
 	
 	public function getHTML($id){
 		$phtml = parent::getHTML($id);

		$F = new HTMLForm("BC", $this->variables, "Zeitraum");
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
		
		$F->setType("useBCOStart", "date", $this->userdata["useBCOStart"]);
		$F->setType("useBCOEnde", "date", $this->userdata["useBCOEnde"]);
		$F->setType("useBCOUserID", "select", $this->userdata["useBCOUserID"], $users);
		
		$F->setLabel("useBCOUserID", "Benutzer");
		$F->setLabel("useBCOStart", "Start");
		$F->setLabel("useBCOEnde", "Ende");
		
		$F->setSaveBericht($this);
		$F->useRecentlyChanged();
		
 		return $phtml.$F;
 	}

 	public function getPDF($save = false){
		#$this->setHeader("Controlling");
		$U = Users::getUsersArray("Alle");
		
		
		$this->AddPage();
		self::$pdf->SetFont("Arial","B",15);
		self::$pdf->Cell8(0, 7, "Controlling für ".$U[$this->userdata["useBCOUserID"]].($this->userdata["useBCOStart"] ? " vom ".$this->userdata["useBCOStart"] : "").($this->userdata["useBCOEnde"] ? " bis ".$this->userdata["useBCOEnde"] : ""), 0);
		self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
		self::$pdf->ln(15);
		
		$U = Users::getUsersArray(null, true);
		$U[-1] = "Allgemein";
		
		$makeSpace = false;
		if(Session::isPluginLoaded("mAkquise")){
			$S = new StatistikAkquiseGUI();
			$AC = $S->data($this->userdata["useBCOUserID"], Util::CLDateParserE($this->userdata["useBCOStart"], "store"), Util::CLDateParserE($this->userdata["useBCOEnde"], "store"));
			
			self::$pdf->SetFont("Arial","",15);
			self::$pdf->Cell8(0, 7, "Telefonate", 0, 1);
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
			
			$c1 = 40;
			$c2 = 25;
			$c3 = 25;
			$c4 = 25;
			self::$pdf->SetFont("Arial","B",10);
			self::$pdf->Cell8($c1, 5, "Benutzer");
			self::$pdf->Cell8($c2, 5, "Gesamt", 0, 0, "R");
			self::$pdf->Cell8($c3, 5, "Entsch.", 0, 0, "R");
			self::$pdf->Cell8($c4, 5, "Prozent", 0, 0, "R");
			self::$pdf->Ln();
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
				
				
			self::$pdf->SetFont("Arial","",10);
			while($A = $AC->n()){			
				self::$pdf->Cell8($c1, 5, isset($U[$A->A("AkquiseUserID")]) ? $U[$A->A("AkquiseUserID")] : "Benutzer ID ".$A->A("AkquiseUserID"));
				self::$pdf->Cell8($c2, 5, $A->A("gesamt"), 0, 0, "R");
				self::$pdf->Cell8($c3, 5, $A->A("gesamtDM"), 0, 0, "R");
				self::$pdf->Cell8($c4, 5, Util::CLNumberParserZ($A->A("gesamt") != 0 ? round($A->A("gesamtDM") / $A->A("gesamt") * 100, 2) : 0)."%", 0, 0, "R");
				self::$pdf->Ln();
			}
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
			
			$makeSpace = true;
		}
		
		
		if(Session::isPluginLoaded("mTodo")){
			if($makeSpace)
				self::$pdf->Ln(20);
			
			$S = new StatistikTodoGUI();
			$AC = $S->data($this->userdata["useBCOUserID"], Util::CLDateParserE($this->userdata["useBCOStart"], "store"), Util::CLDateParserE($this->userdata["useBCOEnde"], "store"));
			
			self::$pdf->SetFont("Arial","",15);
			self::$pdf->Cell8(0, 7, "Termine", 0, 1);
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
			
			$c1 = 40;
			$c2 = 25;
			$c3 = 25;
			$c4 = 25;
			self::$pdf->SetFont("Arial","B",10);
			self::$pdf->Cell8($c1, 5, "Benutzer");
			self::$pdf->Cell8($c2, 5, "Gesamt", 0, 0, "R");
			self::$pdf->Cell8($c3, 5, "Abgesch.", 0, 0, "R");
			self::$pdf->Cell8($c4, 5, "Kalt", 0, 0, "R");
			self::$pdf->Cell8($c4, 5, "Erst", 0, 0, "R");
			self::$pdf->Cell8($c4, 5, "Folge", 0, 0, "R");
			self::$pdf->Ln();
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
										
			self::$pdf->SetFont("Arial","",10);
			while($A = $AC->n()){
				self::$pdf->Cell8($c1, 5, isset($U[$A->A("TodoUserID")]) ? $U[$A->A("TodoUserID")] : "Benutzer ID ".$A->A("TodoUserID"));
				self::$pdf->Cell8($c2, 5, $A->A("gesamt"), 0, 0, "R");
				self::$pdf->Cell8($c3, 5, $A->A("gesamtDM")."(".Util::CLNumberParserZ($A->A("gesamt") != 0 ? round($A->A("gesamtDM") / $A->A("gesamt") * 100, 2) : 0)."%)", 0, 0, "R");
				self::$pdf->Cell8($c4, 5, $A->A("gesamtKalt"), 0, 0, "R");
				self::$pdf->Cell8($c4, 5, $A->A("gesamtErst"), 0, 0, "R");
				self::$pdf->Cell8($c4, 5, $A->A("gesamtFolge"), 0, 0, "R");
				self::$pdf->Ln();
			}
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
			
			$makeSpace = true;
		}
		
		
		if(Session::isPluginLoaded("mAufgabe")){
			if($makeSpace)
				self::$pdf->Ln(20);
			
			$S = new StatistikAufgabenGUI();
			$AC = $S->data($this->userdata["useBCOUserID"], Util::CLDateParserE($this->userdata["useBCOStart"], "store"), Util::CLDateParserE($this->userdata["useBCOEnde"], "store"));
			
			self::$pdf->SetFont("Arial","",15);
			self::$pdf->Cell8(0, 7, "Bearbeitete Aufgaben", 0, 1);
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
			
			$c1 = 40;
			$c2 = 25;
			$c3 = 25;
			$c4 = 25;
			self::$pdf->SetFont("Arial","B",10);
			self::$pdf->Cell8($c1, 5, "Benutzer");
			self::$pdf->Cell8($c2, 5, "Gesamt", 0, 0, "R");
			self::$pdf->Ln();
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
				
				
			self::$pdf->SetFont("Arial","",10);
			while($A = $AC->n()){			
				self::$pdf->Cell8($c1, 5, isset($U[$A->A("AufgabeUserID")]) ? $U[$A->A("AufgabeUserID")] : "Benutzer ID ".$A->A("AufgabeUserID"));
				self::$pdf->Cell8($c2, 5, $A->A("gesamt"), 0, 0, "R");
				self::$pdf->Ln();
			}
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
		}

		$tmpfname = Util::getTempFilename("Bericht");
		self::$pdf->Output($tmpfname, ($save ? "F" : "I"));
		if($save)
			return $tmpfname;
 	}
#
	public static function nummerParser($w){
		return str_replace(";", "\n", $w);
	}

	public static function betragParser($w){
		return Util::CLFormatCurrency($w * 1);
	}

	public static function firmaParser($w, $p, $E){
		return utf8_decode(trim($E->firma."\n".$E->vorname." ".$E->nachname));
	}

	public static function dateParser($w, $p){
		return floor((time() - $w) / (24 * 3600))."\n".utf8_decode("Ø").ZahlungsmoralGUI::getAverage($p);
	}
 } 
 ?>