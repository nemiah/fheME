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
abstract class Bericht_default extends PersistentObject implements iGUIHTML2, icontextMenu {
	protected $variables = array();
	protected $variablesDefaults = array();
	
	protected $collection = null;
	protected $fieldsToShow = array();
	protected $positionEmpfaengerAdresse = array(20, 55);
	
	/**
	 * @var FPDF
	 */
	protected static $pdf = null;
	protected $template = "";
	
	protected $userdata = array();
	
	protected $labels = array();
	protected $widths = array();
	protected $aligns = array();
	#protected $parsers = array();
	protected $lineParser = array();
	protected $parserParameters = array();
	protected $DGParser = null;
	protected $header = "";
	protected $above = "";
	protected $empfaenger;
	
	protected $types = array();
	
	protected $defaultFont = "Arial";
	protected $defaultFontStyle = "";
	protected $defaultFontSize = 9;
	
	protected $sumFontSize = 9;
	
	protected $defaultCellHeight = 4;
	protected $defaultHeaderCellHeight  = 4;
	
	protected $pageBreakMargin = 270;
	
	protected $groupBy = "";
	
	protected $table;

	protected $borderL = array();

	protected $labelSum;
	protected $fieldsSum = array();
	protected static $sums = array();
	protected static $zwSums = array();
	
	protected $labelZwischensumme;
	protected $fieldsZwischensumme = array();
	protected $fieldsPostParserZwischensumme = array();
	
	protected $fieldsPostParserSum = array();
	protected $valueParsers = array();
	protected $sumParsers;

	protected $YPosFirstLine;
	protected $XPosFields = array();
	protected $orientation = "P";
	
	function useVariables(array $variables){
		$this->variables = $variables;
	}
	
	function useVariableDefault($variable, $value){
		$this->variablesDefaults[$variable] = $value;
	}
	
 	public function loadMe(){
 		parent::loadMe();

		foreach($this->variables AS $v){
			$this->A->$v = "";
			$this->A->$v = "";
		}
 	}
 	
	public function getCategory(){
		return null;
	}
	
	function getClearClass(){
		return str_replace("GUI","",get_class($this));
	}
	
	public function quickButton(){
		return false;
	}
	
	public function hasSettings(){
		return true;
	}
	
	public function setA($A){
		$n = $this->getClearClass(get_class($this))."ID";
		$A->$n = 0;
		
		parent::setA($A);
	}
	
	protected function setAlignment($col, $al){
		$this->aligns[$col] = $al;
	}
	
	protected function setDefaultFont($font, $style, $size){
		$this->defaultFont = $font;
		$this->defaultFontSize = $size;
		$this->defaultFontStyle = $style;
		$this->sumFontSize = $size;
	}

	protected function setFieldParser($col, $function, $parameters = null){
		$this->parsers[$col] = $function;
		$this->parserParameters[$col] = !is_array($parameters) ? array($parameters) : $parameters;
	}

	protected function setLineParser($where, $method){
		$this->lineParser[$where] = $method;
	}
	
	protected function setValueParser($col, $function){
		$this->valueParsers[$col] = $function;
	}

	public function setSumParser($col, $function){
		$this->sumParsers[$col] = $function;
	}

	protected function setDGParser($function){
		$this->DGParser = $function;
	}
	
	protected function setType($col, $type){
		$this->types[$col] = $type;
	}
	
	protected function calcZwischensumme($label, array $fields){
		$this->labelZwischensumme = T::_($label);
		$this->fieldsZwischensumme = $fields;
	}
	
	protected function calcPostParserZwischensumme($label, array $fields){
		$this->labelZwischensumme = T::_($label);
		$this->fieldsPostParserZwischensumme = $fields;
	}
	
	protected function setDefaultCellHeight($height){
		$this->defaultCellHeight = $height;
	}
	
	protected function setDefaultHeaderCellHeight($height){
		$this->defaultHeaderCellHeight = $height;
	}

	public function calcSum($label, $fields){
		$this->labelSum = T::_($label);
		$this->fieldsSum = $fields;
	}

	protected function calcPostParserSum($label, $fields){
		$this->labelSum = T::_($label);
		$this->fieldsPostParserSum = $fields;
	}
	
	protected function setColWidth($col, $width){
		$this->widths[$col] = $width;
	}

	protected function setColBorderL($col){
		$this->borderL[] = $col;
	}
	
	protected function setGroupBy($groupBy){
		$this->groupBy = $groupBy;
	}
	
	protected function setHeader($header){
		$this->header = $header;
	}
	
	protected function setAbove($text, $fontSize = 9, $cellHeight = 5){
		$this->above = array($text, $fontSize, $cellHeight);
	}
	
	protected function setEmpfaenger(Adresse $Adresse){
		$this->empfaenger = $Adresse;
	}
	
	protected function setPageBreakMargin($mm){
		$this->pageBreakMargin = $mm;
	}
	
	public function setLabel($col, $label){
		$this->labels[$col] = $label;
	}
	
	public function __construct(){
		T::load(__DIR__, "Berichte");
		
		$ud = new mUserdata();
		$this->template = $ud->getUDValue(get_class($this)."Template", Environment::getS("defaultTemplateBericht", "open3ABericht"));
		
		$pdfc = $this->template;
		self::$pdf = new $pdfc($this->orientation);
		$this->storage = "UDStorage";
		
 		$mU = new mUserdata();
 		$this->userdata = $mU->getAsArray(str_replace("GUI","",get_class($this)));
		
		foreach($this->variables AS $v)
			if(!isset($this->userdata[$v]))
				$this->userdata[$v] = isset($this->variablesDefaults[$v]) ? $this->variablesDefaults[$v] : "";
		
		
		$this->defaultHeaderCellHeight = $this->defaultCellHeight;
	}
	
	//public abstract function getLabel();
	
	function saveMe($checkUserData = true, $output = false){
		#if($checkUserData) mUserdata::checkRestrictionOrDie("cantEdit".str_replace("GUI","",get_class($this)));
		#print_r($this->A);
		$this->loadAdapter();
		$this->Adapter->saveSingle2(str_replace("GUI","",get_class($this)),$this->A);
		
		echo Red::messageSaved();
	}
	
	public function newAttributes(){
		return new stdClass();
	}
	
	public function sendEmail($subject = "", $body = "", $recipient = 0){
		$filename = $this->getPDF(true);
		
		$data = $this->getEMailData();
		if($data["recipients"] !== null)
			$recipient = $data["recipients"][$recipient][1];
		
		if($subject == "")
			$subject = $data["subject"];
		
		if($body == "")
			$body = $data["body"];
		
		$mimeMail2 = new phynxMailer($recipient, $subject, $body);
		$mimeMail2->from($data["fromAddress"], $data["fromName"], $data["fromAddress"]);
		$mimeMail2->attach($filename);
		
		$mimeMail2->send();
		
		Red::messageD("E-Mail verschickt");
	}
	
	public function getEMailData(){
		$data = array(
			"fromName" => Session::currentUser()->A("name"),
			"fromAddress" => Session::currentUser()->A("UserEmail"),
			"recipients" => null,
			"subject" => "",
			"body" => "");
		
		
		return $data;
	}
	
	public function getHTMLHeader($id){
		T::load(__DIR__, "Berichte");

		$T = new HTMLTable(1);
		$T->setColClass(1, "");
		$BP = "";
		if(Session::isPluginLoaded("mDrucker")){
			$BP = DruckerWindowGUI::getButton(str_replace("GUI","",get_class($this)), "", "printPDF", "", false);
			$BP->style("float:right;margin-left:10px;");
			$BP->type("LPBig");
		}
		
		$BM = new Button("Per E-Mail verschicken", "mail", "LPBig");
		$BM->style("float:right;");
		$BM->popup("", "Per E-Mail verschicken", "Util", "00", "EMailPopup", array("'".get_class($this)."'", -1));
		
		$B = new Button("Bericht\nanzeigen", "pdf");
		$B->windowRme(str_replace("GUI","",get_class($this)), "-1", "getPDF");
		$B->settings(str_replace("GUI","",get_class($this)), "1");
		$T->addRow(array($BP.$BM.$B));
		
		return "<p class=\"prettyTitle\">".T::_($this->getLabel())."</p>".$T;
	}
	
	public function getHTML($id){
		return $this->getHTMLHeader($id);
	}

	public function getContextMenuHTML($identifier){
		$FB = new FileBrowser();
		$FB->addDir("../specifics");
		$FB->addDir(FileStorage::getFilesDir());
		$files = $FB->getAsLabeledArray("iBerichtTemplate",".class.php",true);
		$gui = new HTMLGUI();
		
		$default = array("Standard-Bericht" => "open3ABericht");
		echo $gui->getContextMenu(array_flip($default + $files), str_replace("GUI","",get_class($this)), "1", $this->template);
	}
	
	public function saveContextMenu($identifier, $key){
		$ud = new mUserdata();
		
		if($identifier == "1")
			$ud->setUserdata(get_class($this)."Template",$key);
	}

	public function printLabels(){
		foreach($this->fieldsToShow AS $key => $value){
			$this->XPosFields[$value] = self::$pdf->GetX();
			
			self::$pdf->SetFont("Arial","BI",10);
			self::$pdf->Cell8(isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultHeaderCellHeight, isset($this->labels[$value]) ? T::_($this->labels[$value]) : T::_(ucfirst($value)), 0, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
		
			self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
		}
		self::$pdf->ln();
		$this->YPosFirstLine = self::$pdf->GetY();
		self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
		self::$pdf->ln(2);
	}
	
	public function printPDF(){
		$filename = $this->getPDF(true);
		
		try {
			$drucker = mDrucker::getStandardPrinter(false);
		} catch (NoStandardPrinterInstalledException $e){
			echo -1;
			return -1;
		}

		$drucker->makePaper($filename, true);
	}
	
	public function getPDF($save = false){
		if($save == true)
			return $this->getPDFContent($save);
		
		Util::showPDF($this, "getPDFContent");
	}

	private function drawLines(){
		if(count($this->XPosFields) == 0)
			return;
		
		foreach ($this->borderL AS $k){
			self::$pdf->Line($this->XPosFields[$k], $this->YPosFirstLine, $this->XPosFields[$k], self::$pdf->GetY() - 1);
		}
	}

	protected function AddPage(){
		$this->drawLines();
		self::$pdf->AddPage();
	}

	public function getCollection(){
		return $this->collection;
	}
	
	public function getPDFContent($save = false){
		
		self::$pdf->SetAutoPageBreak(false);
		
		if(count($this->fieldsToShow) == 0 AND $this->collection != null) {
			$E = $this->collection->getNextEntry();
			if($E != null){
				$A = $E->getA();
				$this->fieldsToShow = PMReflector::getAttributesArray(get_class($A));
				$this->collection->resetPointer();
			}
		}
		$this->AddPage();
		self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
		self::$pdf->SetFillColor(200, 200, 200);
		
		if($this->empfaenger){
			#self::$pdf->SetXY($this->positionEmpfaengerAdresse[0], $this->positionEmpfaengerAdresse[1]);
			self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
			self::$pdf->MultiCell8(0,5,$this->empfaenger->getFormattedAddress(), 0);
			self::$pdf->ln(4);
		}
		
		if($this->header != "") {
			self::$pdf->SetFont("Arial","B",15);
			self::$pdf->MultiCell(0,7,Util::utf8_decode($this->header), 0);
			self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
			self::$pdf->ln(4);
		}
		
		if($this->above) {
			self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->above[1]);
			self::$pdf->MultiCell(0,$this->above[2],Util::utf8_decode($this->above[0]), 0);
			
			self::$pdf->ln(4);
		}
		
		$this->prepend();
		
		$gB = $this->groupBy;
		$grouper = null;

		$this->printLabels();
		
		$sums = array();
		
		while($t = $this->collection->n()){
			if(self::$pdf->getY() > $this->pageBreakMargin) {
				$this->AddPage();
				$this->printLabels();
			}
			
			$A = $t->getA();
			
			if($gB != "" AND $grouper !== $A->$gB) {
				if(self::$pdf->getY() > $this->pageBreakMargin - 10) {
					$this->AddPage();
					$this->printLabels();
				}
				
				if($this->labelZwischensumme != null AND $grouper !== null){
					$this->printSum("Zwischensumme", self::$zwSums);
					self::$zwSums = array();
				}
				
				self::$pdf->SetFont($this->defaultFont,"B",9);
				if($grouper !== null) 
					self::$pdf->ln(7);
				$dgv = $A->$gB;
				if($this->DGParser != null) {
					$ex = explode("::", $this->DGParser);
					$dgv = Util::invokeStaticMethod($ex[0], $ex[1], [$dgv, $A]);
				}
				self::$pdf->SetFillColor(200, 200, 200);
				self::$pdf->Cell(0,5,Util::utf8_decode($dgv.""), 0, 1, "L",1);
				self::$pdf->ln(2);
				self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
			}
			


			if(isset($this->lineParser["before"]))
				$this->invokeParser($this->lineParser["before"], self::$pdf, $t);

			$maxHeight = $yB = self::$pdf->getY();
			$xB = self::$pdf->getX();
			$hasMultiCell = false;
			
			foreach($this->fieldsToShow AS $key => $value){
				
				$c = "";
				if(property_exists($A, $value)){
					$A->$value = Util::utf8_decode($A->$value."");
					$c = $A->$value;
				}
				
				if(isset($this->valueParsers[$value]))
					$c = $this->invokeParser($this->valueParsers[$value], $A->$value, null, $A, $t);

				if($this->fieldsSum != null)
					if(in_array($value, $this->fieldsSum))
						if(isset(self::$sums[$value])) self::$sums[$value] += (float) $c;
						else self::$sums[$value] = 0 + (float) $c;
						
				if($this->fieldsZwischensumme != null)
					if(in_array($value, $this->fieldsZwischensumme))
						if(isset(self::$zwSums[$value])) self::$zwSums[$value] += $c * 1;
						else self::$zwSums[$value] = 0 + $c * 1;
					
				if(isset($this->parsers[$value])) {
					$parameters = $this->makeParameterStringFromArray($this->parserParameters[$value], $A);
					$c = $this->invokeParser($this->parsers[$value], isset($A->$value) ? $A->$value : "", $parameters, $A, $t);
				}

				if($this->fieldsPostParserSum != null)
					if(in_array($value, $this->fieldsPostParserSum))
						if(isset(self::$sums[$value])) self::$sums[$value] += $c * 1;
						else self::$sums[$value] = 0 + $c * 1;
				
				if($this->fieldsPostParserZwischensumme != null)
					if(in_array($value, $this->fieldsPostParserZwischensumme))
						if(isset(self::$zwSums[$value])) self::$zwSums[$value] += $c * 1;
						else self::$zwSums[$value] = 0 + $c * 1;

				#if(isset($this->parsers[$value])) eval("\$c = ".$this->parsers[$value]."('$c');");

				self::$pdf->setXY($xB, $yB);
				
				if(!isset($this->types[$value]) OR $this->types[$value] == "Cell")
					self::$pdf->Cell(isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultCellHeight, $c, 0, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
				
				if(isset($this->types[$value]) AND $this->types[$value] == "Cell8")
					self::$pdf->Cell8(isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultCellHeight, Util::utf8_encode($c), 0, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
				
				if(isset($this->types[$value]) AND $this->types[$value] == "MultiCell"){
					self::$pdf->MultiCell(isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultCellHeight, $c, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
					$hasMultiCell = true;
				}
				
				if(isset($this->types[$value]) AND $this->types[$value] == "MultiCell8"){
					self::$pdf->MultiCell8(isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultCellHeight, $c, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
					$hasMultiCell = true;
				}
				
				if(isset($this->types[$value]) AND $this->types[$value] == "Custom"){
					$method = "CustomCell$value";
					$hasMultiCell = $this->$method(self::$pdf, isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultCellHeight, $c, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
					#self::$pdf->MultiCell8(isset($this->widths[$value]) ? $this->widths[$value] : 20, $this->defaultCellHeight, $c, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
					#$hasMultiCell = true;
				}
				
				$xB += isset($this->widths[$value]) ? $this->widths[$value] : 20;
				if(self::$pdf->getY() > $maxHeight) $maxHeight = self::$pdf->getY();
			}
			if($hasMultiCell) $maxHeight -= $this->defaultCellHeight;
			self::$pdf->setY($maxHeight);
			self::$pdf->ln();
			
			if(isset($this->lineParser["after"]))
				$this->invokeParser($this->lineParser["after"], self::$pdf, $t);
			
			
			self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
			self::$pdf->ln(1);
			if($gB != "") $grouper = $A->$gB;
		}
		
		if($this->labelZwischensumme != null){
			$this->printSum("Zwischensumme", self::$zwSums);
			self::$pdf->ln(5);
		}
		
		if($this->labelSum != null){
			$this->printSum("Sum");#, self::$sums);
		}

		$this->drawLines();

		self::$pdf->SetFont($this->defaultFont,$this->defaultFontStyle,$this->defaultFontSize);
		self::$pdf->Ln();
		$this->append();
		
		$tmpfname = $this->filename();
		self::$pdf->Output($tmpfname, ($save ? "F" : "I"));
		if($save) return $tmpfname;
	}
	
	public function filename($overwriteName = null){
		$name = "Bericht_".Util::makeFilename($this->getLabel() == null ? "" : $this->getLabel())."_".date("Ymd");
		if($overwriteName)
			$name = $overwriteName;
		
		return Util::getTempFilename($name);
	}
	
	protected function prepend(){
		
	}
	
	protected function append(){
		
	}

	private function printSum($sumField, $sums = null){
		if($sums == null)
			$sums = self::$sums;
		$labelprint = false;
		$width = 0;
		self::$pdf->SetFont($this->defaultFont, "B", $this->sumFontSize);
		self::$pdf->Line(10,self::$pdf->GetY(),self::$pdf->w - 10, self::$pdf->GetY());
		self::$pdf->ln(1);
		$label = "label".$sumField;

		#$x = self::$pdf->GetX();
		$y = self::$pdf->GetY();
		foreach($this->fieldsToShow AS $key => $value){
			if(in_array($value,$this->fieldsSum) OR in_array($value,$this->fieldsPostParserSum)){
				if(!isset($sums[$value]))
					$sums[$value] = 0;
				
				$c = $sums[$value];

				if(isset($this->sumParsers[$value]))
					$c = $this->invokeParser($this->sumParsers[$value], $sums[$value], "", null);

				#if(isset($this->parsers[$value])) eval("\$c = ".$this->parsers[$value]."('$c');");
				if(!$labelprint){
					self::$pdf->Cell($width, $this->defaultCellHeight, $this->$label, 0, 0, "R");
					$labelprint = true;
				}
				self::$pdf->SetXY($width + self::$pdf->GetMargin("L") - 15, $y);
				self::$pdf->MultiCell((isset($this->widths[$value]) ? $this->widths[$value] : 20) + 15, $this->defaultCellHeight, $c, 0, (isset($this->aligns[$value]) ? $this->aligns[$value] : "L"));
				$width += isset($this->widths[$value]) ? $this->widths[$value] : 20;
			} else {
				$width += isset($this->widths[$value]) ? $this->widths[$value] : 20;
			}
		}
	}
	
	protected function makeParameterStringFromArray($array, $sc){
		foreach($array AS $k => $v) {
			if(strpos($v."","\$") !== false ){
				$v = str_replace("\$","",$v);
				$array[$k] = $sc->$v;
			} else
				$array[$k] = $v;
		}
		return implode("%§%",$array);
	}

	protected function invokeParser($function, $value, $parameters, $A = null, $E = null){
		$c = explode("::", $function);
		if(count($c) == 1)
			$c = array(get_class($this), $c[0]);

		if($c[0] == "Util")
			$parameters = "load";
		
		$method = new ReflectionMethod($c[0], $c[1]);
		return $method->invoke(null, $value, $parameters, $A, $E);
	}
}
?>