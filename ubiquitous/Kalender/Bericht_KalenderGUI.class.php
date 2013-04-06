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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class Bericht_KalenderGUI extends Bericht_default implements iBerichtDescriptor {

 	/*function __construct() {
 		parent::__construct();
 	}*/
 	protected $between = false;
	
 	public function getLabel(){
		if(Applications::activeApplication() != "lightCRM") return null;
		
 		return "Kalender";
 	}

	public function loadMe(){
		parent::loadMe();

		if(!isset($this->A->lightCRMKalBerichtMonth)) $this->A->lightCRMKalBerichtMonth = date("m");
		if(!isset($this->A->lightCRMKalBerichtYear)) $this->A->lightCRMKalBerichtYear = date("Y");
	}

	protected function AddPage() {
		parent::AddPage();
		
		$this->pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
		$this->pdf->SetFillColor(200, 200, 200);
		
		$this->pdf->SetFont("Arial","B",15);
		$this->pdf->MultiCell(0,7,utf8_decode($this->header), 0);
		$this->pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
		$this->pdf->ln(4);
		
	}
	
 	public function getHTML($id){
 		$phtml = parent::getHTML($id);

 		$monate = Datum::getGerMonthArray();
		$jahre = array(date("Y") - 1 => date("Y") - 1, date("Y") => date("Y"), date("Y") + 1 => date("Y") + 1);

		$f = new HTMLForm("Bericht", array("lightCRMKalBerichtMonth", "lightCRMKalBerichtYear"), "Anzeige:");
		$f->getTable()->setColWidth(1, "120px");

		$f->setType("lightCRMKalBerichtMonth", "select", ($this->userdata != null AND isset($this->userdata["lightCRMKalBerichtMonth"])) ? $this->userdata["lightCRMKalBerichtMonth"] : 0, $monate);
		$f->setType("lightCRMKalBerichtYear", "select", ($this->userdata != null AND isset($this->userdata["lightCRMKalBerichtYear"])) ? $this->userdata["lightCRMKalBerichtYear"] : 0, $jahre);
		
		$f->setLabel("lightCRMKalBerichtMonth", "Monat");
		$f->setLabel("lightCRMKalBerichtYear", "Jahr");
		
		$f->setSaveBericht($this);
 		
 		return $f.$phtml;
 	}
 	
	public function getPDFContent($save = false) {
		if($this->userdata == null OR count($this->userdata) == 0)
			die(Util::getBasicHTMLError("Bitte wählen Sie Monat und Jahr aus", "Fehler"));
		
		$this->pdf->SetAutoPageBreak(false);
		$this->AddPage();
		
		
		$D = new Datum(mktime(0, 0, 1, $this->userdata["lightCRMKalBerichtMonth"], 1, $this->userdata["lightCRMKalBerichtYear"]));
		$day = clone $D;
		
		$D->setToMonthLast();
		$lastDay = $D;
		
		$Kalender = new mKalenderGUI();
		$K = $Kalender->getData($day->time(), $lastDay->time());
		
		$i = 0;
		while($day->time() <= $lastDay->time()){
			$events = $K->getEventsOnDay(date("dmY", $day->time()));
			if(count($events) == 0){
				$day->addDay();
				continue;
			}
			
			if($this->pdf->getY() > $this->pageBreakMargin - 15) {
				$this->AddPage();
				$i = 0;
			}
			
			if($i > 0)
				$this->pdf->ln(10);
			
			$this->pdf->SetFont($this->defaultFont,"B",$this->defaultFontSize);
			$this->pdf->Cell(0, $this->defaultCellHeight, "Termine am ".Util::CLWeekdayName(date("w", $day->time())).", ".Util::CLFormatDate($day->time()), 0, 1);
			$this->pdf->Line(10,$this->pdf->GetY(),200, $this->pdf->GetY());
			$this->pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
			
			foreach($events AS $time)
				foreach($time AS $event){
					if($this->pdf->getY() > $this->pageBreakMargin) {
						$this->AddPage();
						$i = 0;
						if($this->between){
							$this->pdf->SetFont($this->defaultFont,"B",$this->defaultFontSize);
							$this->pdf->Cell(0, $this->defaultCellHeight, "Termine am ".Util::CLWeekdayName(date("w", $day->time())).", ".Util::CLFormatDate($day->time()), 0, 1);
							$this->pdf->Line(10,$this->pdf->GetY(),200, $this->pdf->GetY());
							$this->pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
						}
							
					}
			
					$this->between = true;
					
					$this->pdf->Cell8(isset($this->widths["time"]) ? $this->widths["time"] : 20, $this->defaultCellHeight, Util::CLTimeParser(Kalender::parseTime($event->getTime()))." - ".Util::CLTimeParser(Kalender::parseTime($event->getEndTime())), 0, 0);
					$this->pdf->Cell8(isset($this->widths["title"]) ? $this->widths["title"] : 20, $this->defaultCellHeight, $event->title(), 0, 0);
					$this->pdf->Cell8(isset($this->widths["location"]) ? $this->widths["location"] : 20, $this->defaultCellHeight, $event->location(), 0, 1);
					
					$summary = $event->summary();
					if($summary != ""){
						$this->pdf->setTextColor(100, 100, 100);
						$this->pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize - 1);
						$this->pdf->MultiCell8(isset($this->widths["summary"]) ? $this->widths["summary"] : 20, $this->defaultCellHeight - 1, trim(str_replace("<br />", "", $summary)), 0, 1);
						$this->pdf->setTextColor(0, 0, 0);
						$this->pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
					}
					
					$this->pdf->SetDrawColor(150, 150, 150);
					$this->pdf->Line(10,$this->pdf->GetY(),200, $this->pdf->GetY());
					$this->pdf->SetDrawColor(0, 0, 0);
					
					$this->pdf->ln(5);
					
				}
			
			$this->between = false;
			$day->addDay();
			$i++;
		}
		
		$tmpfname = Util::getTempFilename("Bericht");
		$this->pdf->Output($tmpfname, ($save ? "F" : "I"));
		if($save) return $tmpfname;
	}
	
 	public function getPDF($save = false){
 		$this->defaultCellHeight++;
 		$this->fieldsToShow = array("titel", "GRLBMServiceTerminTag", "nachname", "kundennummer", "kunde", "textbausteinOben");
 		$this->setHeader("Kalender für ".Util::CLMonthName($this->userdata["lightCRMKalBerichtMonth"])." ".$this->userdata["lightCRMKalBerichtYear"]);
		$this->setPageBreakMargin(270);
		
		$this->setLabel("nachname", "Monteur");
		$this->setLabel("GRLBMServiceTerminTag", "Datum");
		$this->setLabel("kundennummer", "Kunden-Nr");
		$this->setLabel("textbausteinOben", "Beschreibung");
		
		$this->setColWidth("time", 30);
		$this->setColWidth("title", 60);
		$this->setColWidth("summary", 150);
		$this->setColWidth("location", 50);
		
		$this->setFieldParser("GRLBMServiceTerminTag", "Bericht_THBMonteureKalenderGUI::parserDatum");
		$this->setFieldParser("kunde", "Bericht_THBMonteureKalenderGUI::parserKunde", array("\$AdresseID"));
		
		$this->setType("kunde", "MultiCell");
		$this->setType("textbausteinOben", "MultiCell");
		
 		return parent::getPDF($save);
 	}
	
	public static function parserKunde($w, $p){
		$Adresse = new Adresse($p);
		return utf8_decode($Adresse->getFormattedAddress());
	}
	
	public static function parserDatum($w){
		return Util::CLDateParser($w);
	}
 } 
 ?>