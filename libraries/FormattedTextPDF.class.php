<?php
/**
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

#if(!class_exists("TemporaryPDFClass", false))
#	eval("class TemporaryPDFClass extends FPDF {}");

class FormattedTextPDF extends FPDI {
	function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $copy = false) {
		if(file_exists(Util::getRootPath()."ubiquitous/Fonts/")){# AND !defined("FPDF_FONTPATH")) {
			#define('FPDF_FONTPATH', Util::getRootPath()."ubiquitous/Fonts/");

			$this->AddFont("Ubuntu", "", "5e01bde68449bff64cefe374f81b7847_ubuntu-regular.php");
			$this->AddFont("Ubuntu", "B", "70fed3593f0725ddea7da8f1c62577c1_ubuntu-bold.php");
			$this->AddFont("Ubuntu", "I", "cfa4d284ee1dc737cb0fe903fbab1844_ubuntu-italic.php");
			$this->AddFont("Ubuntu", "BI", "c409dbcbee5b5ac6bf7b101817c7416a_ubuntu-bolditalic.php");

			$this->AddFont("Orbitron", "", "667a54623e1b9927fdf078125bbbf49b_orbitron-regular.php");
			$this->AddFont("Orbitron", "B", "c4c6025fc06df62e82ebf42b2709e6ae_orbitron-bold.php");
			$this->AddFont("Orbitron", "I", "c4c6025fc06df62e82ebf42b2709e6ae_orbitron-fakeItalic.php");
			$this->AddFont("Orbitron", "BI", "c4c6025fc06df62e82ebf42b2709e6ae_orbitron-fakeBoldItalic.php");

			$this->AddFont("Raleway", "", "ed7ad2408e498cae8fab623a755883f6_raleway-thin.php");
			$this->AddFont("Raleway", "B", "ed7ad2408e498cae8fab623a755883f6_raleway-thin-fakeBold.php");
			$this->AddFont("Raleway", "I", "ed7ad2408e498cae8fab623a755883f6_raleway-thin-fakeItalic.php");
			$this->AddFont("Raleway", "BI", "ed7ad2408e498cae8fab623a755883f6_raleway-thin-fakeBoldItalic.php");
		}
		
		parent::__construct($orientation, $unit, $format, $copy);

	}

	private $FontSizeH = array("h1" => 15, "h2" => 13, "h3" => 12, "h4" => 10, "h5" => 10, "h6" => 10);
	private $FontDecoH = array("h1" => "B", "h2" => "B", "h3" => "B", "h4" => "B", "h5" => "B", "h6" => "B");
	private $FontLnH = array("h1" => 10, "h2" => 8, "h3" => 7, "h4" => 5, "h5" => 5, "h6" => 5);

	private $styleStack = array();
	private $sizeStack = array();
	private $colorStack = array("000000");
	private $alignStack = array("left");
	protected $heightStack = array(3);
	protected $paragraph = 0;
	protected $fontStack = array("Helvetica");
	protected $inHTML = false;

	protected function stackFont(array $font){
		if(count($this->fontStack) > 0)
			$this->fontStack[count($this->fontStack) - 1] = $font[0];
		else
			$this->fontStack[] = $font[0];
	}
	
	private function translateXML($xml){
		$dom = dom_import_simplexml($xml);
		
		#if($this->alignStack[count($this->alignStack) - 1] == "center")
		#	$this->SetXY($this->GetStringWidth($xml.""), $this->GetY());

		foreach($dom->childNodes as $child){
			if($child->nodeType == XML_TEXT_NODE){
				$this->inHTML = $this->getFont();
				$this->Write($this->heightStack[count($this->heightStack) - 1] / 1.9, utf8_decode($child->nodeValue));
				$this->inHTML = false;
				#$this->Write(5, utf8_decode($child->nodeValue));
			} else {
				$p = simplexml_import_dom($child);
				$this->startTag($p);
				$this->translateXML($p);
				$this->endTag($p);
			}
		}
	}

	protected function getFont(){
		return array($this->FontFamily, $this->FontStyle, $this->FontSizePt);
	}
	
	private function startTag($xml){
		if($xml->getName() == "p"){
			if($this->paragraph > 0)
				$this->Ln(4);#$this->heightStack[count($this->heightStack) - 1] * 2);
			
			array_push($this->heightStack, $this->findMaxStyle("font-size", $xml));
			
			$this->paragraph++;
		}

		if(preg_match_all("/h([0-9])/", $xml->getName(), $m)){
			$this->Ln($this->FontLnH[$m[0][0]]);
			array_push($this->styleStack, $this->FontDecoH[$m[0][0]]);
			array_push($this->sizeStack, $this->FontSizeH[$m[0][0]]);
		}

		if($xml->getName() == "strong")
			array_push($this->styleStack, "B");

		if($xml->getName() == "b")
			array_push($this->styleStack, "B");

		if($xml->getName() == "em")
			array_push($this->styleStack, "I");

		if($xml->getName() == "i")
			array_push($this->styleStack, "I");

		if($xml->getName() == "u")
			array_push($this->styleStack, "U");

		foreach($xml->attributes() AS $k => $a){
			if($k == "style"){
				$styles = explode(";", $a);
				foreach($styles AS $S){
					if(stripos($S, "font-size:") !== false)
						array_push($this->sizeStack, trim(str_ireplace(array("font-size:", "pt"), "", $S)));

					if(stripos($S, "text-decoration:") !== false)
						array_push($this->styleStack, "U");

					if(stripos($S, "color:") !== false)
						array_push($this->colorStack, trim(str_ireplace(array("color:", "#"), "", $S)));

					if(stripos($S, "text-align:") !== false)
						array_push($this->alignStack, trim(str_ireplace("text-align:", "", $S)));

					if(stripos(trim($S), "font-family:") === 0){
						$font = trim(trim(str_ireplace(array("font-family:", ";"), "", $S)), "\"'");
						if(stripos($font, ",") !== false){
							$ex = explode(",", $font);
							$font = trim($ex[0], "\"'");
						}
						
						if(strtolower($font) == "times new roman")
							$font = "times";
						
						if(strtolower($font) == "courier new")
							$font = "courier";
						
						array_push($this->fontStack, $font);
					}
				}
			}
		}
		$this->SetHTMLTextColor($this->colorStack[count($this->colorStack) - 1]);
		$this->SetFont($this->fontStack[count($this->fontStack) - 1], implode("", $this->styleStack), $this->sizeStack[count($this->sizeStack) - 1]);
			
	}

	private function endTag($xml){
		if($xml->getName() == "br"){
			$this->ln(5);
			return;
		}
		
		if($xml->getName() == "p"){
			array_pop($this->heightStack);
			$this->ln(5);
		}

		if($xml->getName() == "strong")
			array_pop($this->styleStack);

		if($xml->getName() == "b")
			array_pop($this->styleStack);

		if($xml->getName() == "em")
			array_pop($this->styleStack);

		if($xml->getName() == "i")
			array_pop($this->styleStack);

		if($xml->getName() == "u")
			array_pop($this->styleStack);

		if(preg_match_all("/h([0-9])/", $xml->getName(), $m)){
			array_pop($this->styleStack);
			array_pop($this->sizeStack);
			$this->ln(5);
		}

		foreach($xml->attributes() AS $k => $a){
			if($k == "style"){
				$styles = explode(";", $a);
				foreach($styles AS $S){
					if(stripos($S, "font-size:") !== false)
						array_pop($this->sizeStack);
					
					if(stripos($S, "text-decoration:") !== false)
						array_pop($this->styleStack);

					if(stripos($S, "color:") !== false)
						array_pop($this->colorStack);

					if(stripos($S, "text-align:") !== false)
						array_pop($this->alignStack);

					if(stripos($S, "font-family:") !== false)
						array_pop($this->fontStack);
				}
			}
		}
		
		$this->SetHTMLTextColor($this->colorStack[count($this->colorStack) - 1]);
		$this->SetFont($this->fontStack[count($this->fontStack) - 1], implode("", $this->styleStack), $this->sizeStack[count($this->sizeStack) - 1]);
	}

	public function WriteHTML($html) {
		if(trim($html) == "") return;

		$this->sizeStack[] = $this->getFontSize();
		
		$html = str_replace("\n", "", $html);
		$html = preg_replace('/<!--\[if[^\]]*]>.*?<!\[endif\]-->/i', '', $html);
		
		$bad = array("&gt;", "&lt;", "&amp;");
		$good = array("::gt::", "::lt::", "::amp::");
		
		libxml_use_internal_errors(true);
		try {
			$xml = new SimpleXMLElement(str_replace($good, $bad, "<phynx>".html_entity_decode(str_replace($bad, $good, $html), ENT_NOQUOTES, "UTF-8")."</phynx>"));
		} catch (Exception $e){
			try {
				$tidy = new tidy();
				$tidy->parseString($html, array("show-body-only" => true, "output-xhtml" => true, "wrap" => 0), 'utf8');
				$tidy->cleanRepair();
				$xml = new SimpleXMLElement(str_replace($good, $bad, "<phynx>".html_entity_decode(str_replace($bad, $good, $tidy), ENT_NOQUOTES, "UTF-8")."</phynx>"));
			} catch (Exception $e){
				$errors = "";
				foreach(libxml_get_errors() as $error) {
					#echo "\t", $error->message;
					$errors .= htmlentities(trim($error->message))."<br />";
				}
				$xml = new SimpleXMLElement("<phynx><p style=\"color:#dd0000;\">Der Textbaustein konnte nicht geladen werden:<br />$errors</p></phynx>");
			}
		}

		$this->translateXML($xml);
	}

	private function findMaxStyle($style, SimpleXMLElement $xml){
		$return = $this->sizeStack[0];

		foreach($xml->attributes() AS $k => $a){
			if($k == "style"){
				$styles = explode(";", $a);
				foreach($styles AS $S){
					if(stripos($S, "$style:") !== false){
						$foundSize = trim(str_replace(array("$style:", "pt"), "", $S)) * 1;
						if($foundSize > $return)
							$return = $foundSize;
					}

				}
			}
		}

		foreach($xml->children() AS $C){
			$childSize = $this->findMaxStyle($style, $C);
			if($childSize > $return)
				$return= $childSize;
		}

		return $return;
	}

	private function SetHTMLTextColor($htmlHex){
		parent::SetTextColor(hexdec(substr($htmlHex, 0, 2)), hexdec(substr($htmlHex, 2, 2)), hexdec(substr($htmlHex, 4, 2)));
	}
}
?>
