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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */

class FormattedTextPDF extends FPDI {
	#function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false){

	#	parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

	#}
	

	private $FontSizeH = array("h1" => 15, "h2" => 13, "h3" => 12, "h4" => 10, "h5" => 10, "h6" => 10);
	private $FontDecoH = array("h1" => "B", "h2" => "B", "h3" => "B", "h4" => "B", "h5" => "B", "h6" => "B");
	private $FontLnH = array("h1" => 10, "h2" => 8, "h3" => 7, "h4" => 5, "h5" => 5, "h6" => 5);

	private $styleStack = array();
	private $sizeStack = array();
	private $colorStack = array("000000");
	private $alignStack = array("left");
	protected $heightStack = array(3);
	public $paragraph = 0;
	protected $fontStack = array("Helvetica");
	protected $inHTML = false;
	protected $heightFactor = 1.9;
	
	public function stackFont(array $font){
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
				$this->Write($this->heightStack[count($this->heightStack) - 1] / $this->heightFactor, utf8_decode($child->nodeValue));
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
		if($xml == null)
			return;
		
		if($xml->getName() == "p"){
			if($this->paragraph > 0)
				$this->Ln(4);#$this->heightStack[count($this->heightStack) - 1] * 2);
			
			array_push($this->heightStack, $this->findMaxStyle("font-size", $xml));
			$this->heightStack[0] = $this->heightStack[count($this->heightStack) - 1];
			
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

		if($xml->getName() == "hr")
			$this->Line($this->GetMargin("L") , $this->GetY() + 2, $this->w - $this->GetMargin("R") , $this->GetY() + 2);

		if($xml->getName() == "br" AND isset($xml->attributes()["pagebreak"]) AND $xml->attributes()["pagebreak"] == true)
			$this->AddPage();
		
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
		$this->SetFont($this->fontStack[count($this->fontStack) - 1], implode("", array_unique($this->styleStack)), $this->sizeStack[count($this->sizeStack) - 1]);
			
	}

	private function endTag($xml){
		if($xml == null)
			return;
		
		if($xml->getName() == "br"){
			#print_r($this->heightStack);
			#die($this->heightStack[count($this->heightStack) - 1] * 0.5);
			$this->ln($this->heightStack[count($this->heightStack) - 1] * 0.5);
			return;
		}
		
		if($xml->getName() == "p"){
			$this->ln($this->heightStack[count($this->heightStack) - 1] * 0.4);
			array_pop($this->heightStack);
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
		$this->SetFont($this->fontStack[count($this->fontStack) - 1], implode("", array_unique($this->styleStack)), $this->sizeStack[count($this->sizeStack) - 1]);
	}

	public function WriteHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='') {
		if(trim($html) == "") return;
		
		if($this instanceof TCPDF)
			return parent::writeHTML($html, $ln, $fill, $reseth, $cell, $align);
		
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
