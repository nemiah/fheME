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
if(!defined("PHYNX_USE_TCPDF"))
	define("PHYNX_USE_TCPDF", false);

class open3ABericht extends FormattedTextPDF {
	function Header(){
		$name = Applications::activeApplicationLabel();
		
		$this->setXY(10, 5);
		$this->SetFont("Arial","",9);
		if(Environment::getS("renameApplication:$name", $name) != "nil")
			$this->Cell(0, 5,T::_("Dieser Bericht wurde erstellt mit")." ".Environment::getS("renameApplication:$name", $name), 0, 0, "R");
		$this->Line(10,10,$this->w - 10,10);
		$this->setXY(10, 15);
	}

	function Cell8($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='') {
		$this->Cell($w, $h, Util::utf8_decode($txt), $border, $ln, $align, $fill, $link);
	}

	function MultiCell8($w, $h, $txt, $border=0, $align='J', $fill=0) {
		$this->MultiCell($w, $h, Util::utf8_decode($txt), $border, $align, $fill);
	}
	
	function Footer(){
		$this->SetDrawColor(0);
		$this->SetFont("Arial","",9);
		$this->SetXY(10, -10);
		$this->Cell(100, 5, Util::CLFormatDate(time()));
		$this->Cell(0, 5, T::_("Seite")." ".$this->PageNo()."/{nb}", 0, 0, "R");
		$this->Line(10,$this->GetY(),$this->w - 10,$this->GetY());
	}
	
	function Output($filename = "", $mode = ""){
		$this->AliasNbPages();
		parent::Output($filename, $mode);
	}	
	
	function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5){

		$wide = $baseline;
		$narrow = $baseline / 3 ;
		$gap = $narrow;

		$barChar['0'] = 'nnnwwnwnn';
		$barChar['1'] = 'wnnwnnnnw';
		$barChar['2'] = 'nnwwnnnnw';
		$barChar['3'] = 'wnwwnnnnn';
		$barChar['4'] = 'nnnwwnnnw';
		$barChar['5'] = 'wnnwwnnnn';
		$barChar['6'] = 'nnwwwnnnn';
		$barChar['7'] = 'nnnwnnwnw';
		$barChar['8'] = 'wnnwnnwnn';
		$barChar['9'] = 'nnwwnnwnn';
		$barChar['A'] = 'wnnnnwnnw';
		$barChar['B'] = 'nnwnnwnnw';
		$barChar['C'] = 'wnwnnwnnn';
		$barChar['D'] = 'nnnnwwnnw';
		$barChar['E'] = 'wnnnwwnnn';
		$barChar['F'] = 'nnwnwwnnn';
		$barChar['G'] = 'nnnnnwwnw';
		$barChar['H'] = 'wnnnnwwnn';
		$barChar['I'] = 'nnwnnwwnn';
		$barChar['J'] = 'nnnnwwwnn';
		$barChar['K'] = 'wnnnnnnww';
		$barChar['L'] = 'nnwnnnnww';
		$barChar['M'] = 'wnwnnnnwn';
		$barChar['N'] = 'nnnnwnnww';
		$barChar['O'] = 'wnnnwnnwn';
		$barChar['P'] = 'nnwnwnnwn';
		$barChar['Q'] = 'nnnnnnwww';
		$barChar['R'] = 'wnnnnnwwn';
		$barChar['S'] = 'nnwnnnwwn';
		$barChar['T'] = 'nnnnwnwwn';
		$barChar['U'] = 'wwnnnnnnw';
		$barChar['V'] = 'nwwnnnnnw';
		$barChar['W'] = 'wwwnnnnnn';
		$barChar['X'] = 'nwnnwnnnw';
		$barChar['Y'] = 'wwnnwnnnn';
		$barChar['Z'] = 'nwwnwnnnn';
		$barChar['-'] = 'nwnnnnwnw';
		$barChar['.'] = 'wwnnnnwnn';
		$barChar[' '] = 'nwwnnnwnn';
		$barChar['*'] = 'nwnnwnwnn';
		$barChar['$'] = 'nwnwnwnnn';
		$barChar['/'] = 'nwnwnnnwn';
		$barChar['+'] = 'nwnnnwnwn';
		$barChar['%'] = 'nnnwnwnwn';

		$this->SetFont('Arial', '', 10);
		#$this->Text($xpos, $ypos + $height + 4, $code);
		$this->SetFillColor(0);

		$code = '*'.strtoupper($code).'*';
		for($i=0; $i<strlen($code); $i++){
			$char = $code[$i];

			if(!isset($barChar[$char]))
				$this->Error('Invalid character in barcode: '.$char);

			$seq = $barChar[$char];
			for($bar=0; $bar<9; $bar++){
				if($seq[$bar] == 'n')
					$lineWidth = $narrow;
				else
					$lineWidth = $wide;

				if($bar % 2 == 0)
					$this->Rect($xpos, $ypos, $lineWidth, $height, 'F');

				$xpos += $lineWidth;
			}

			$xpos += $gap;
		}
	}
}
 ?>