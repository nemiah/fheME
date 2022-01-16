<?php

/********************************************************************************
 * Software: FPDF                                                               *
 * Version:  1.53                                                               *
 * Date:     2004-12-31                                                         *
 * Author:   Olivier PLATHEY                                                    *
 * License:  Freeware                                                           *
 *                                                                              *
 * You may use and modify this software as you wish.                            *
 *******************************************************************************/

if(!defined('FPDF_VERSION'))
	define('FPDF_VERSION','1.53.1');

class FPDF {
	protected $page;			   //current page number
	protected $n;				  //current object number
	protected $offsets;			//array of object offsets
	protected $buffer;			 //buffer holding in-memory PDF
	protected $pages;			  //array containing pages
	protected $state;			  //current document state
	protected $compress;		   //compression flag
	protected $DefOrientation;	 //default orientation
	protected $CurOrientation;	 //current orientation
	protected $OrientationChanges; //array indicating orientation changes
	public $k;				  //scale factor (number of points in user unit)
	protected $fwPt, $fhPt;		 //dimensions of page format in points
	protected $fw, $fh;			 //dimensions of page format in user unit
	protected $wPt, $hPt;		   //current dimensions of page in points
	public $w, $h;			   //current dimensions of page in user unit
	protected $lMargin;			//left margin
	protected $tMargin;			//top margin
	protected $rMargin;			//right margin
	protected $bMargin;			//page break margin
	protected $cMargin;			//cell margin
	protected $x, $y;			   //current position in user unit for cell positioning
	protected $lasth;			  //height of last cell printed
	protected $LineWidth;		  //line width in user unit
	protected $CoreFonts;		  //array of standard font names
	protected $fonts = array();	  //array of used fonts
	protected $FontFiles = array();  //array of font files
	protected $diffs = array();	  //array of encoding differences
	protected $images;			 //array of used images
	protected $PageLinks;		  //array of links in pages
	protected $links;			  //array of internal links
	protected $FontFamily;		 //current font family
	protected $FontStyle;		  //current font style
	protected $underline;		  //underlining flag
	protected $CurrentFont;		//current font info
	protected $FontSizePt;		 //current font size in points
	protected $FontSize;		   //current font size in user unit
	protected $DrawColor;		  //commands for drawing color
	protected $FillColor;		  //commands for filling color
	protected $TextColor;		  //commands for text color
	protected $ColorFlag;		  //indicates whether fill and text colors are different
	protected $ws;				 //word spacing
	protected $AutoPageBreak;	  //automatic page breaking
	protected $PageBreakTrigger;   //threshold used to trigger page breaks
	protected $InFooter;		   //flag set when processing footer
	protected $ZoomMode;		   //zoom display mode
	protected $LayoutMode;		 //layout display mode
	protected $title;			  //title
	protected $subject;			//subject
	protected $author;			 //author
	protected $keywords;		   //keywords
	protected $creator;			//creator
	protected $AliasNbPages;	   //alias for total number of pages
	public $PDFVersion;		 //PDF version number
	#protected $HasCopy;			 //
	protected $IsCopy;
	protected $replaceKeyword;
	protected $replaceWith;
	protected $fakePage;

	function strlen($str){
		return mb_strlen($str, "ISO-8859-15");
	}
	
	function substr($str, $start, $length = null){
		if($length === null)
			$length = mb_strlen ($str, "ISO-8859-15");
		
		return mb_substr($str, $start, $length, "ISO-8859-15");
	}
	
	function strpos($haystack, $needle, $offset = 0){
		return mb_strpos($haystack, $needle, $offset, "ISO-8859-15");
	}
	
	function __construct($orientation='P', $unit='mm', $format='A4') {
		//Some checks
		$this->_dochecks();
		//Initialization of properties
		$this->page = 0;
		$this->fakePage = 0;
		$this->n = 2;
		$this->buffer = '';
		$this->pages = array();
		$this->OrientationChanges = array();
		$this->state = 0;
		#$this->fonts=array();
		#$this->FontFiles=array();
		#$this->diffs=array();
		$this->images = array();
		$this->links = array();
		$this->InFooter = false;
		$this->lasth = 0;
		$this->FontFamily = '';
		$this->FontStyle = '';
		$this->FontSizePt = 12;
		$this->underline = false;
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->ws = 0;
		//Standard fonts
		$this->CoreFonts = array('courier' => 'Courier', 'courierB' => 'Courier-Bold', 'courierI' => 'Courier-Oblique', 'courierBI' => 'Courier-BoldOblique',
			'helvetica' => 'Helvetica', 'helveticaB' => 'Helvetica-Bold', 'helveticaI' => 'Helvetica-Oblique', 'helveticaBI' => 'Helvetica-BoldOblique',
			'times' => 'Times-Roman', 'timesB' => 'Times-Bold', 'timesI' => 'Times-Italic', 'timesBI' => 'Times-BoldItalic',
			'symbol' => 'Symbol', 'zapfdingbats' => 'ZapfDingbats');
		//Scale factor
		if ($unit == 'pt')
			$this->k = 1;
		elseif ($unit == 'mm')
			$this->k = 72 / 25.4;
		elseif ($unit == 'cm')
			$this->k = 72 / 2.54;
		elseif ($unit == 'in')
			$this->k = 72;
		else
			$this->Error('Incorrect unit: ' . $unit);
		//Page format
		if (is_string($format)) {
			$format = strtolower($format);
			if ($format == 'a3')
				$format = array(841.89, 1190.55);
			elseif ($format == 'a4')
				$format = array(595.28, 841.89);
			elseif ($format == 'a5')
				$format = array(420.94, 595.28);
			elseif ($format == 'letter')
				$format = array(612, 792);
			elseif ($format == 'legal')
				$format = array(612, 1008);
			else
				$this->Error('Unknown page format: ' . $format);
			$this->fwPt = $format[0];
			$this->fhPt = $format[1];
		}
		else {
			$this->fwPt = $format[0] * $this->k;
			$this->fhPt = $format[1] * $this->k;
		}
		$this->fw = $this->fwPt / $this->k;
		$this->fh = $this->fhPt / $this->k;
		//Page orientation
		$orientation = strtolower($orientation);
		if ($orientation == 'p' || $orientation == 'portrait') {
			$this->DefOrientation = 'P';
			$this->wPt = $this->fwPt;
			$this->hPt = $this->fhPt;
		} elseif ($orientation == 'l' || $orientation == 'landscape') {
			$this->DefOrientation = 'L';
			$this->wPt = $this->fhPt;
			$this->hPt = $this->fwPt;
		}
		else
			$this->Error('Incorrect orientation: ' . $orientation);
		$this->CurOrientation = $this->DefOrientation;
		$this->w = $this->wPt / $this->k;
		$this->h = $this->hPt / $this->k;
		//Page margins (1 cm)
		$margin = 28.35 / $this->k;
		$this->SetMargins($margin, $margin);
		//Interior cell margin (1 mm)
		$this->cMargin = $margin / 10;
		//Line width (0.2 mm)
		$this->LineWidth = .567 / $this->k;
		//Automatic page break
		$this->SetAutoPageBreak(true, 2 * $margin);
		//Full width display mode
		$this->SetDisplayMode('fullwidth');
		//Enable compression
		$this->SetCompression(true);
		//Set default PDF version number
		$this->PDFVersion = '1.3';
		#$this->HasCopy = $copy;
		$this->IsCopy = 0;
		$this->replaceKeyword = array();
		$this->replaceWith = array();
	}

	function SetDash($black=false, $white=false) {
		if ($black and $white)
			$s = sprintf('[%.3f %.3f] 0 d', $black * $this->k, $white * $this->k);
		else
			$s='[] 0 d';
		$this->_out($s);
	}

	function AddCopyPage() {
		$this->IsCopy += 1;
	}

	function AddReplacement($keyword, $with) {
		$this->replaceKeyword[] = $keyword;
		$this->replaceWith[] = $with;
	}

	function SetMargins($left, $top, $right=-1) {
		//Set left, top and right margins
		$this->lMargin = $left;
		$this->tMargin = $top;
		if ($right == -1)
			$right = $left;
		$this->rMargin = $right;
	}

	function GetMargin($where) {
		switch (strtoupper($where)) {
			case "R":
				return $this->rMargin;
			break;
			case "L":
				return $this->lMargin;
			break;
			case "T":
				return $this->tMargin;
			break;
			case "B":
				return $this->bMargin;
			break;
		}
	}

	function Cell8($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='') {
		$this->Cell($w, $h, utf8_decode($txt), $border, $ln, $align, $fill, $link);
	}

	function MultiCell8($w, $h, $txt, $border=0, $align='J', $fill=0) {
		$this->MultiCell($w, $h, utf8_decode($txt), $border, $align, $fill);
	}

	function SetLeftMargin($margin) {
		//Set left margin
		$this->lMargin = $margin;
		if ($this->page > 0 && $this->x < $margin)
			$this->x = $margin;
	}

	function SetTopMargin($margin) {
		//Set top margin
		$this->tMargin = $margin;
	}

	function SetRightMargin($margin) {
		//Set right margin
		$this->rMargin = $margin;
	}

	function SetAutoPageBreak($auto, $margin=0) {
		//Set auto page break mode and triggering margin
		$this->AutoPageBreak = $auto;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	function SetDisplayMode($zoom, $layout='continuous') {
		//Set display mode in viewer
		if ($zoom == 'fullpage' || $zoom == 'fullwidth' || $zoom == 'real' || $zoom == 'default' || !is_string($zoom))
			$this->ZoomMode = $zoom;
		else
			$this->Error('Incorrect zoom display mode: ' . $zoom);
		if ($layout == 'single' || $layout == 'continuous' || $layout == 'two' || $layout == 'default')
			$this->LayoutMode = $layout;
		else
			$this->Error('Incorrect layout display mode: ' . $layout);
	}

	function SetCompression($compress) {
		//Set page compression
		if (function_exists('gzcompress'))
			$this->compress = $compress;
		else
			$this->compress = false;
	}

	function SetTitle($title) {
		//Title of document
		$this->title = $title;
	}

	function SetSubject($subject) {
		//Subject of document
		$this->subject = $subject;
	}

	function SetAuthor($author) {
		//Author of document
		$this->author = $author;
	}

	function SetKeywords($keywords) {
		//Keywords of document
		$this->keywords = $keywords;
	}

	function SetCreator($creator) {
		//Creator of document
		$this->creator = $creator;
	}

	function AliasNbPages($alias='{nb}') {
		//Define an alias for total number of pages
		$this->AliasNbPages = $alias;
	}

	function Error($msg) {
		//Fatal error
		#if(defined("PHYNX_VIA_INTERFACE"))
			throw new Exception($msg);
		
		#die('FPDF error:' . $msg);
	}

	function Open() {
		//Begin document
		$this->state = 1;
	}

	function Close() {
		//Terminate document
		if ($this->state == 3)
			return;
		if ($this->page == 0)
			$this->AddPage();
		//Page footer
		$this->InFooter = true;
		$this->Footer();
		$this->InFooter = false;
		//Close page
		$this->_endpage();
		//Close document
		$this->_enddoc();
	}

	function AddPage($orientation='', $resetFakePageCounter = false) {
		//Start a new page
		if ($this->state == 0)
			$this->Open();
		$family = $this->FontFamily;
		$style = $this->FontStyle . ($this->underline ? 'U' : '');
		$size = $this->FontSizePt;
		$lw = $this->LineWidth;
		$dc = $this->DrawColor;
		$fc = $this->FillColor;
		$tc = $this->TextColor;
		$cf = $this->ColorFlag;
		if ($this->page > 0) {
			//Page footer
			$this->InFooter = true;
			$this->Footer();
			$this->InFooter = false;
			//Close page
			$this->_endpage();
		}
		if ($resetFakePageCounter)
			$this->fakePage = 0;
		//Start new page
		$this->_beginpage($orientation);
		//Set line cap style to square
		$this->_out('2 J');
		//Set line width
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2f w', $lw * $this->k));
		//Set font
		if ($family)
			$this->SetFont($family, $style, $size);
		//Set colors
		$this->DrawColor = $dc;
		if ($dc != '0 G')
			$this->_out($dc);
		$this->FillColor = $fc;
		if ($fc != '0 g')
			$this->_out($fc);
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		//Page header
		$this->Header();
		//Restore line width
		if ($this->LineWidth != $lw) {
			$this->LineWidth = $lw;
			$this->_out(sprintf('%.2f w', $lw * $this->k));
		}
		//Restore font
		if ($family)
			$this->SetFont($family, $style, $size);
		//Restore colors
		if ($this->DrawColor != $dc) {
			$this->DrawColor = $dc;
			$this->_out($dc);
		}
		if ($this->FillColor != $fc) {
			$this->FillColor = $fc;
			$this->_out($fc);
		}
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
	}

	function Header() {
		//To be implemented in your own inherited class
	}

	function Footer() {
		//To be implemented in your own inherited class
	}

	function PageNo() {
		//Get current page number
		return $this->page;
	}

	function SetDrawColor($r, $g=-1, $b=-1) {
		//Set color for all stroking operations
		if (($r == 0 && $g == 0 && $b == 0) || $g == -1)
			$this->DrawColor = sprintf('%.3f G', $r / 255);
		else
			$this->DrawColor = sprintf('%.3f %.3f %.3f RG', $r / 255, $g / 255, $b / 255);
		if ($this->page > 0)
			$this->_out($this->DrawColor);
	}

	function SetFillColor($r, $g=-1, $b=-1) {
		//Set color for all filling operations
		if (($r == 0 && $g == 0 && $b == 0) || $g == -1)
			$this->FillColor = sprintf('%.3f g', $r / 255);
		else
			$this->FillColor = sprintf('%.3f %.3f %.3f rg', $r / 255, $g / 255, $b / 255);
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
		if ($this->page > 0)
			$this->_out($this->FillColor);
	}

	function SetTextColor($r, $g=-1, $b=-1) {
		//Set color for text
		if (($r == 0 && $g == 0 && $b == 0) || $g == -1)
			$this->TextColor = sprintf('%.3f g', $r / 255);
		else
			$this->TextColor = sprintf('%.3f %.3f %.3f rg', $r / 255, $g / 255, $b / 255);
		$this->ColorFlag = ($this->FillColor != $this->TextColor);
	}

	function GetStringWidth($s) {
		//Get width of a string in the current font
		$s = (string) $s;
		$cw = &$this->CurrentFont['cw'];
		$w = 0;
		$l = $this->strlen($s);
		for ($i = 0; $i < $l; $i++)
			$w+=$cw[$s[$i]];
		return $w * $this->FontSize / 1000;
	}

	function SetLineWidth($width) {
		//Set line width
		$this->LineWidth = $width;
		if ($this->page > 0)
			$this->_out(sprintf('%.2f w', $width * $this->k));
	}

	function Line($x1, $y1, $x2, $y2) {
		//Draw a line
		$this->_out(sprintf('%.2f %.2f m %.2f %.2f l S', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k));
	}

	function Rect($x, $y, $w, $h, $style='') {
		//Draw a rectangle
		if ($style == 'F')
			$op = 'f';
		elseif ($style == 'FD' || $style == 'DF')
			$op = 'B';
		else
			$op='S';
		$this->_out(sprintf('%.2f %.2f %.2f %.2f re %s', $x * $this->k, ($this->h - $y) * $this->k, $w * $this->k, -$h * $this->k, $op));
	}

	function AddFont($family, $style='', $file='') {
		//Add a TrueType or Type1 font
		$family = strtolower($family);
		if ($file == '')
			$file = str_replace(' ', '', $family) . strtolower($style) . '.php';
		if ($family == 'arial')
			$family = 'helvetica';
		$style = strtoupper($style);
		if ($style == 'IB')
			$style = 'BI';
		$fontkey = $family . $style;
		if (isset($this->fonts[$fontkey]))
			$this->Error('Font already added: ' . $family . ' ' . $style);
		
		#$dir = dirname($file);
		
		#if($dir != "")
		#	include($file);
		#else
		$fontFile = $this->_getfontpath($file);
		if(!file_exists($fontFile))
			$this->Error("Font definition file does not exist! ($fontFile)");
		
		include($fontFile);
		
		if (!isset($name))
			$this->Error('Could not include font definition file '.$file." (path: ".$fontFile.")");
		
		$i = count($this->fonts) + 1;
		$this->fonts[$fontkey] = array('i' => $i, 'type' => $type, 'name' => $name, 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw, 'enc' => $enc, 'file' => $file);
		if ($diff) {
			//Search existing encodings
			$d = 0;
			$nb = count($this->diffs);
			for ($i = 1; $i <= $nb; $i++) {
				if ($this->diffs[$i] == $diff) {
					$d = $i;
					break;
				}
			}
			if ($d == 0) {
				$d = $nb + 1;
				$this->diffs[$d] = $diff;
			}
			$this->fonts[$fontkey]['diff'] = $d;
		}
		if ($file) {
			if ($type == 'TrueType')
				$this->FontFiles[$file] = array('length1' => $originalsize);
			else
				$this->FontFiles[$file] = array('length1' => $size1, 'length2' => $size2);
		}
	}

	function SetFont($family, $style='', $size=0) {
		//Select a font; size given in points
		global $fpdf_charwidths;

		$family = strtolower($family);
		if ($family == '')
			$family = $this->FontFamily;
		if ($family == 'arial')
			$family = 'helvetica';
		elseif ($family == 'symbol' || $family == 'zapfdingbats')
			$style = '';
		$style = strtoupper($style);
		if ($this->strpos($style, 'U') !== false) {
			$this->underline = true;
			$style = str_replace('U', '', $style);
		}
		else
			$this->underline = false;
		if ($style == 'IB')
			$style = 'BI';
		if ($size == 0)
			$size = $this->FontSizePt;
		if ($size === null)
			$size = $this->FontSizePt;
		//Test if font is already selected
		if ($this->FontFamily == $family && $this->FontStyle == $style && $this->FontSizePt == $size)
			return;
		//Test if used for the first time
		$fontkey = $family . $style;
		if (!isset($this->fonts[$fontkey])) {
			//Check if one of the standard fonts
			if (isset($this->CoreFonts[$fontkey])) {
				if (!isset($fpdf_charwidths[$fontkey])) {
					//Load metric file
					$file = $family;
					if ($family == 'times' || $family == 'helvetica')
						$file.=strtolower($style);
					include($this->_getfontpath($file.'.php'));
					if (!isset($fpdf_charwidths[$fontkey]))
						$this->Error('Could not include font metric file');
				}
				$i = count($this->fonts) + 1;
				$this->fonts[$fontkey] = array('i' => $i, 'type' => 'core', 'name' => $this->CoreFonts[$fontkey], 'up' => -100, 'ut' => 50, 'cw' => $fpdf_charwidths[$fontkey]);
			}
			else
				$this->Error('Undefined font: ' . $family . ' ' . $style);
		}
		//Select it
		$this->FontFamily = $family;
		$this->FontStyle = $style;
		$this->FontSizePt = $size;
		$this->FontSize = $size / $this->k;
		$this->CurrentFont = &$this->fonts[$fontkey];
		if ($this->page > 0)
			$this->_out(sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
	}

	function GetFontSize(){
		return $this->FontSizePt;
	}
	
	function SetFontSize($size) {
		//Set font size in points
		if ($this->FontSizePt == $size)
			return;
		$this->FontSizePt = $size;
		$this->FontSize = $size / $this->k;
		if ($this->page > 0)
			$this->_out(sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
	}

	function AddLink() {
		//Create a new internal link
		$n = count($this->links) + 1;
		$this->links[$n] = array(0, 0);
		return $n;
	}

	function SetLink($link, $y=0, $page=-1) {
		//Set destination of internal link
		if ($y == -1)
			$y = $this->y;
		if ($page == -1)
			$page = $this->page;
		$this->links[$link] = array($page, $y);
	}

	function Link($x, $y, $w, $h, $link) {
		//Put a link on the page
		$this->PageLinks[$this->page][] = array($x * $this->k, $this->hPt - $y * $this->k, $w * $this->k, $h * $this->k, $link);
	}

	function Text($x, $y, $txt) {
		//Output a string
		$s = sprintf('BT %.2f %.2f Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
		if ($this->underline && $txt != '')
			$s.=' ' . $this->_dounderline($x, $y, $txt);
		if ($this->ColorFlag)
			$s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
		$this->_out($s);
	}

	function AcceptPageBreak() {
		//Accept automatic page break or not
		return $this->AutoPageBreak;
	}

	function LCell($marginRight, $height, $string, $border=0, $ln=0, $align='', $fill=0, $link='') {
		$this->Cell($this->GetStringWidth(utf8_decode($string)) + $marginRight, $height, utf8_decode($string), $border, $ln, $align, $fill, $link);
	}

	function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='') {
		//Output a cell
		$k = $this->k;
		if ($this->y + $h > $this->PageBreakTrigger && !$this->InFooter && $this->AcceptPageBreak()) {
			//Automatic page break
			$x = $this->x;
			$ws = $this->ws;
			if ($ws > 0) {
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			$this->AddPage($this->CurOrientation);
			$this->x = $x;
			if ($ws > 0) {
				$this->ws = $ws;
				$this->_out(sprintf('%.3f Tw', $ws * $k));
			}
		}
		if ($w == 0)
			$w = $this->w - $this->rMargin - $this->x;
		$s = '';
		if ($fill == 1 || $border == 1) {
			if ($fill == 1)
				$op = ($border == 1) ? 'B' : 'f';
			else
				$op='S';
			$s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
		}
		if (is_string($border)) {
			$x = $this->x;
			$y = $this->y;
			if ($this->strpos($border, 'L') !== false)
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
			if ($this->strpos($border, 'T') !== false)
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
			if ($this->strpos($border, 'R') !== false)
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
			if ($this->strpos($border, 'B') !== false)
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
		}
		if ($txt !== '') {
			if ($align == 'R')
				$dx = $w - $this->cMargin - $this->GetStringWidth($txt);
			elseif ($align == 'C')
				$dx = ($w - $this->GetStringWidth($txt)) / 2;
			else
				$dx=$this->cMargin;
			if ($this->ColorFlag)
				$s.='q ' . $this->TextColor . ' ';
			$txt2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
			$s.=sprintf('BT %.2f %.2f Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $txt2);
			if ($this->underline)
				$s.=' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
			if ($this->ColorFlag)
				$s.=' Q';
			if ($link)
				$this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
		}
		if ($s)
			$this->_out($s);
		$this->lasth = $h;
		if ($ln > 0) {
			//Go to next line
			$this->y+=$h;
			if ($ln == 1)
				$this->x = $this->lMargin;
		}
		else
			$this->x+=$w;
	}

	function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0) {
		//Output text with automatic or explicit line breaks
		$cw = &$this->CurrentFont['cw'];
		if ($w == 0)
			$w = $this->w - $this->rMargin - $this->x;
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$s = str_replace("\r", '', $txt);
		$nb = $this->strlen($s);
		if ($nb > 0 && $s[$nb - 1] == "\n")
			$nb--;
		$b = 0;
		if ($border) {
			if ($border == 1) {
				$border = 'LTRB';
				$b = 'LRT';
				$b2 = 'LR';
			} else {
				$b2 = '';
				if ($this->strpos($border, 'L') !== false)
					$b2.='L';
				if ($this->strpos($border, 'R') !== false)
					$b2.='R';
				$b = ($this->strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
			}
		}
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$ns = 0;
		$nl = 1;
		while ($i < $nb) {
			//Get next character
			$c = $s[$i];
			if ($c == "\n") {
				//Explicit line break
				if ($this->ws > 0) {
					$this->ws = 0;
					$this->_out('0 Tw');
				}
				$this->Cell($w, $h, $this->substr($s, $j, $i - $j), $b, 2, $align, $fill);
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				$ns = 0;
				$nl++;
				if ($border && $nl == 2)
					$b = $b2;
				continue;
			}
			if ($c == ' ') {
				$sep = $i;
				$ls = $l;
				$ns++;
			}
			$l+=$cw[$c];
			if ($l > $wmax) {
				//Automatic line break
				if ($sep == -1) {
					if ($i == $j)
						$i++;
					if ($this->ws > 0) {
						$this->ws = 0;
						$this->_out('0 Tw');
					}
					$this->Cell($w, $h, $this->substr($s, $j, $i - $j), $b, 2, $align, $fill);
				} else {
					if ($align == 'J') {
						$this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
						$this->_out(sprintf('%.3f Tw', $this->ws * $this->k));
					}
					$this->Cell($w, $h, $this->substr($s, $j, $sep - $j), $b, 2, $align, $fill);
					$i = $sep + 1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				$ns = 0;
				$nl++;
				if ($border && $nl == 2)
					$b = $b2;
			}
			else
				$i++;
		}
		//Last chunk
		if ($this->ws > 0) {
			$this->ws = 0;
			$this->_out('0 Tw');
		}
		if ($border && $this->strpos($border, 'B') !== false)
			$b.='B';
		$this->Cell($w, $h, $this->substr($s, $j, $i - $j), $b, 2, $align, $fill);
		$this->x = $this->lMargin;
	}

	function Write($h, $txt, $link='') {
		//Output text in flowing mode
		$cw = &$this->CurrentFont['cw'];
		$w = $this->w - $this->rMargin - $this->x;
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$s = str_replace("\r", '', $txt);
		$nb = $this->strlen($s);
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;
		while ($i < $nb) {
			//Get next character
			$c = $s[$i];
			if ($c == "\n") {
				//Explicit line break
				$this->Cell($w, $h, $this->substr($s, $j, $i - $j), 0, 2, '', 0, $link);
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				if ($nl == 1) {
					$this->x = $this->lMargin;
					$w = $this->w - $this->rMargin - $this->x;
					$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
				}
				$nl++;
				continue;
			}
			if ($c == ' ')
				$sep = $i;
			$l+=$cw[$c];
			if ($l > $wmax) {
				//Automatic line break
				if ($sep == -1) {
					if ($this->x > $this->lMargin) {
						//Move to next line
						$this->x = $this->lMargin;
						$this->y+=$h;
						$w = $this->w - $this->rMargin - $this->x;
						$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
						$i++;
						$nl++;
						continue;
					}
					if ($i == $j)
						$i++;
					$this->Cell($w, $h, $this->substr($s, $j, $i - $j), 0, 2, '', 0, $link);
				}
				else {
					$this->Cell($w, $h, $this->substr($s, $j, $sep - $j), 0, 2, '', 0, $link);
					$i = $sep + 1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				if ($nl == 1) {
					$this->x = $this->lMargin;
					$w = $this->w - $this->rMargin - $this->x;
					$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
				}
				$nl++;
			}
			else
				$i++;
		}
		//Last chunk
		if ($i != $j)
			$this->Cell($l / 1000 * $this->FontSize, $h, $this->substr($s, $j), 0, 0, '', 0, $link);
	}

	function Image($file, $x, $y, $w=0, $h=0, $type='', $link='') {
		//Put an image on the page
		if (!isset($this->images[$file])) {
			//First use of image, get info
			if ($type == '') {
				$pos = strrpos($file, '.');
				if (!$pos)
					$this->Error('Image file has no extension and no type was specified: ' . $file);
				$type = $this->substr($file, $pos + 1);
			}
			$type = strtolower($type);
			#$mqr=get_magic_quotes_runtime();
			#set_magic_quotes_runtime(0);
			if ($type == 'jpg' || $type == 'jpeg')
				$info = $this->_parsejpg($file);
			elseif ($type == 'png')
				$info = $this->_parsepng($file);
			else {
				//Allow for additional formats
				$mtd = '_parse' . $type;
				if (!method_exists($this, $mtd))
					$this->Error('Unsupported image type: ' . $type);
				$info = $this->$mtd($file);
			}
			#set_magic_quotes_runtime($mqr);
			$info['i'] = count($this->images) + 1;
			$this->images[$file] = $info;
		}
		else
			$info=$this->images[$file];
		//Automatic width and height calculation if needed
		if ($w == 0 && $h == 0) {
			//Put image at 72 dpi
			$w = $info['w'] / $this->k;
			$h = $info['h'] / $this->k;
		}
		if ($w == 0)
			$w = $h * $info['w'] / $info['h'];
		if ($h == 0)
			$h = $w * $info['h'] / $info['w'];
		$this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
		if ($link)
			$this->Link($x, $y, $w, $h, $link);
	}

	function Ln($h='') {
		//Line feed; default value is last cell height
		$this->x = $this->lMargin;
		if (is_string($h))
			$this->y+=$this->lasth;
		else
			$this->y+=$h;
	}

	function GetX() {
		//Get x position
		return $this->x;
	}

	function SetX($x) {
		//Set x position
		if ($x >= 0)
			$this->x = $x;
		else
			$this->x = $this->w + $x;
	}

	function GetY() {
		//Get y position
		return $this->y;
	}

	function SetY($y) {
		//Set y position and reset x
		$this->x = $this->lMargin;
		if ($y >= 0)
			$this->y = $y;
		else
			$this->y = $this->h + $y;
	}

	function SetXY($x, $y) {
		//Set x and y positions
		$this->SetY($y);
		$this->SetX($x);
	}

	function Output($name='', $dest='') {
		//Output PDF to some destination
		//Finish document if necessary
		if ($this->state < 3)
			$this->Close();
		//Normalize parameters
		if (is_bool($dest))
			$dest = $dest ? 'D' : 'F';
		$dest = strtoupper($dest);
		if ($dest == '') {
			if ($name == '') {
				$name = 'doc.pdf';
				$dest = 'I';
			}
			else
				$dest='F';
		}
		switch ($dest) {
			case 'I':
				//Send to standard output
				if (ob_get_contents ())
					$this->Error('Some data has already been output, can\'t send PDF file');
				if (php_sapi_name() != 'cli') {
					//We send to a browser
					header('Content-Type: application/pdf');
					if (headers_sent ())
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					header('Content-Length: ' . $this->strlen($this->buffer));
					header('Content-disposition: inline; filename="' . $name . '"');
				}
				echo $this->buffer;
				break;
			case 'D':
				//Download file
				if (ob_get_contents ())
					$this->Error('Some data has already been output, can\'t send PDF file');
				if (isset($_SERVER['HTTP_USER_AGENT']) && $this->strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
					header('Content-Type: application/force-download');
				else
					header('Content-Type: application/octet-stream');
				if (headers_sent ())
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				header('Content-Length: ' . $this->strlen($this->buffer));
				header('Content-disposition: attachment; filename="' . $name . '"');
				echo $this->buffer;
				break;
			case 'F':
				//Save to local file
				$f = fopen($name, 'wb');
				if (!$f)
					$this->Error('Die PDF-Datei kann nicht zwischengespeichert werden! ('.$name.')' . "<br /><br />Bitte machen Sie das Verzeichnis<br />system/IECache<br />sowie dessen Unterverzeichnisse durch den Webserver beschreibbar:<br /><br /><code>chmod -R 777 system/IECache</code><br /><br /><span style=\"color:red;\">Hinweis:</span> Dadurch werden die PDF Dateien <b>temporär</b> in einem von außerhalb erreichbaren Verzeichnis abgelegt und können eventuell von Suchmaschinen oder anderen Benutzern gefunden werden.<br />Wenn Sie dies nicht möchten, verwenden Sie bitte den <a href=\"http://www.mozilla.com/de/firefox/\">Firefox-Browser</a>.".(ini_get("safe_mode") ? "<br /><br /><span style=\"color:red;\">Auf diesem Webserver wurde der Safe Mode aktiviert.<br />Bitte beachten Sie die Hinweise in der <a href=\"../system/info.php\">info.php-Datei</a></span>" : ""));
				fwrite($f, $this->buffer, $this->strlen($this->buffer));
				fclose($f);
				break;
			case 'S':
				//Return as a string
				return $this->buffer;
			default:
				$this->Error('Incorrect output destination: ' . $dest);
		}
		return '';
	}

	/*	 * *****************************************************************************
	 *                                                                              *
	 *                              Protected methods                               *
	 *                                                                              *
	 * ***************************************************************************** */

	protected function _dochecks() {
		//Check for locale-related bug
		if (1.1 == 1)
			$this->Error('Don\'t alter the locale before including class file');
		//Check for decimal separator
		if (sprintf('%.1f', 1.0) != '1.0')
			setlocale(LC_NUMERIC, 'C');
	}

	protected function _getfontpath($file = null) {
		if(file_exists($file))
			return $file;
		
		if(file_exists(Util::getRootPath()."specifics/$file"))
			return Util::getRootPath()."specifics/$file";
		
		if(file_exists(FileStorage::getFilesDir().$file))
			return FileStorage::getFilesDir().$file;
		
		if(file_exists(Util::getRootPath()."ubiquitous/Fonts/$file"))
			return Util::getRootPath()."ubiquitous/Fonts/$file";
		
		if(file_exists(dirname(__FILE__) . '/font/'.$file))
			return dirname(__FILE__) . '/font/'.$file;
		
		
		
		#if (!defined('FPDF_FONTPATH') && is_dir(dirname(__FILE__) . '/font'))
		#	define('FPDF_FONTPATH', dirname(__FILE__) . '/font/');
		#return defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
	}

	protected function _putpages() {
		$nb = $this->page;

		if (!empty($this->AliasNbPages)) {
			//Replace number of pages
			for ($n = 1; $n <= $nb; $n++)
				$this->pages[$n] = str_replace($this->AliasNbPages, $this->fakePage, $this->pages[$n]);
		}
		if (count($this->replaceKeyword) > 0) {
			#echo "hier";
			for ($n = 1; $n <= $nb; $n++
				)for ($i = 0; $i < count($this->replaceKeyword); $i++)
					$this->pages[$n] = str_replace($this->replaceKeyword[$i], $this->replaceWith[$i], $this->pages[$n]);
		}
		if ($this->DefOrientation == 'P') {
			$wPt = $this->fwPt;
			$hPt = $this->fhPt;
		} else {
			$wPt = $this->fhPt;
			$hPt = $this->fwPt;
		}
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		for ($n = 1; $n <= $nb; $n++) {
			//Page
			$this->_newobj();
			$this->_out('<</Type /Page');
			$this->_out('/Parent 1 0 R');
			if (isset($this->OrientationChanges[$n]))
				$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]', $hPt, $wPt));
			$this->_out('/Resources 2 0 R');
			if (isset($this->PageLinks[$n])) {
				//Links
				$annots = '/Annots [';
				foreach ($this->PageLinks[$n] as $pl) {
					$rect = sprintf('%.2f %.2f %.2f %.2f', $pl[0], $pl[1], $pl[0] + $pl[2], $pl[1] - $pl[3]);
					$annots.='<</Type /Annot /Subtype /Link /Rect [' . $rect . '] /Border [0 0 0] ';
					if (is_string($pl[4]))
						$annots.='/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
					else {
						$l = $this->links[$pl[4]];
						$h = isset($this->OrientationChanges[$l[0]]) ? $wPt : $hPt;
						$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]>>', 1 + 2 * $l[0], $h - $l[1] * $this->k);
					}
				}
				$this->_out($annots . ']');
			}
			$this->_out('/Contents ' . ($this->n + 1) . ' 0 R>>');
			$this->_out('endobj');
			//Page content
			$p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
			$this->_newobj();
			$this->_out('<<' . $filter . '/Length ' . $this->strlen($p) . '>>');
			$this->_putstream($p);
			$this->_out('endobj');
		}
		//Pages root
		$this->offsets[1] = $this->strlen($this->buffer);
		$this->_out('1 0 obj');
		$this->_out('<</Type /Pages');
		$kids = '/Kids [';
		for ($i = 0; $i < $nb; $i++)
			$kids.= ( 3 + 2 * $i) . ' 0 R ';
		$this->_out($kids . ']');
		$this->_out('/Count ' . $nb);
		$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]', $wPt, $hPt));
		$this->_out('>>');
		$this->_out('endobj');
	}

	protected function _putfonts() {
		$nf = $this->n;
		foreach ($this->diffs as $diff) {
			//Encodings
			$this->_newobj();
			$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [' . $diff . ']>>');
			$this->_out('endobj');
		}
		#$mqr=get_magic_quotes_runtime();
		#set_magic_quotes_runtime(0);
		foreach ($this->FontFiles as $file => $info) {
			//Font file embedding
			$this->_newobj();
			$this->FontFiles[$file]['n'] = $this->n;
			$font = '';
			$f = fopen($this->_getfontpath($file), 'rb', 1);
			if (!$f)
				$this->Error('Font file not found: '.$file);
			while (!feof($f))
				$font.=fread($f, 8192);
			fclose($f);
			$compressed = ($this->substr($file, -2) == '.z');
			if (!$compressed && isset($info['length2'])) {
				$header = (ord($font[0]) == 128);
				if ($header) {
					//Strip first binary header
					$font = $this->substr($font, 6);
				}
				if ($header && ord($font[$info['length1']]) == 128) {
					//Strip second binary header
					$font = $this->substr($font, 0, $info['length1']) . $this->substr($font, $info['length1'] + 6);
				}
			}
			$this->_out('<</Length ' . $this->strlen($font));
			if ($compressed)
				$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 ' . $info['length1']);
			if (isset($info['length2']))
				$this->_out('/Length2 ' . $info['length2'] . ' /Length3 0');
			$this->_out('>>');
			$this->_putstream($font);
			$this->_out('endobj');
		}
		#set_magic_quotes_runtime($mqr);
		foreach ($this->fonts as $k => $font) {
			//Font objects
			$this->fonts[$k]['n'] = $this->n + 1;
			$type = $font['type'];
			$name = $font['name'];
			if ($type == 'core') {
				//Standard font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /' . $name);
				$this->_out('/Subtype /Type1');
				if ($name != 'Symbol' && $name != 'ZapfDingbats')
					$this->_out('/Encoding /WinAnsiEncoding');
				$this->_out('>>');
				$this->_out('endobj');
			}
			elseif ($type == 'Type1' || $type == 'TrueType') {
				//Additional Type1 or TrueType font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /' . $name);
				$this->_out('/Subtype /' . $type);
				$this->_out('/FirstChar 32 /LastChar 255');
				$this->_out('/Widths ' . ($this->n + 1) . ' 0 R');
				$this->_out('/FontDescriptor ' . ($this->n + 2) . ' 0 R');
				if ($font['enc']) {
					if (isset($font['diff']))
						$this->_out('/Encoding ' . ($nf + $font['diff']) . ' 0 R');
					else
						$this->_out('/Encoding /WinAnsiEncoding');
				}
				$this->_out('>>');
				$this->_out('endobj');
				//Widths
				$this->_newobj();
				$cw = &$font['cw'];
				$s = '[';
				for ($i = 32; $i <= 255; $i++)
					$s.=$cw[chr($i)] . ' ';
				$this->_out($s . ']');
				$this->_out('endobj');
				//Descriptor
				$this->_newobj();
				$s = '<</Type /FontDescriptor /FontName /' . $name;
				foreach ($font['desc'] as $k => $v)
					$s.=' /' . $k . ' ' . $v;
				$file = $font['file'];
				if ($file)
					$s.=' /FontFile' . ($type == 'Type1' ? '' : '2') . ' ' . $this->FontFiles[$file]['n'] . ' 0 R';
				$this->_out($s . '>>');
				$this->_out('endobj');
			}
			else {
				//Allow for additional types
				$mtd = '_put' . strtolower($type);
				if (!method_exists($this, $mtd))
					$this->Error('Unsupported font type: ' . $type);
				$this->$mtd($font);
			}
		}
	}

	protected function _putimages() {
		$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
		#reset($this->images);
		#print_r($this->images);
		#die();
		#while (list($file, $info) = each($this->images)) {
		foreach($this->images as $file => $info){
			$this->_newobj();
			$this->images[$file]['n'] = $this->n;
			$this->_out('<</Type /XObject');
			$this->_out('/Subtype /Image');
			$this->_out('/Width ' . $info['w']);
			$this->_out('/Height ' . $info['h']);
			if ($info['cs'] == 'Indexed')
				$this->_out('/ColorSpace [/Indexed /DeviceRGB ' . ($this->strlen($info['pal']) / 3 - 1) . ' ' . ($this->n + 1) . ' 0 R]');
			else {
				$this->_out('/ColorSpace /' . $info['cs']);
				if ($info['cs'] == 'DeviceCMYK')
					$this->_out('/Decode [1 0 1 0 1 0 1 0]');
			}
			$this->_out('/BitsPerComponent ' . $info['bpc']);
			if (isset($info['f']))
				$this->_out('/Filter /' . $info['f']);
			if (isset($info['parms']))
				$this->_out($info['parms']);
			if (isset($info['trns']) && is_array($info['trns'])) {
				$trns = '';
				for ($i = 0; $i < count($info['trns']); $i++)
					$trns.=$info['trns'][$i] . ' ' . $info['trns'][$i] . ' ';
				$this->_out('/Mask [' . $trns . ']');
			}
			$this->_out('/Length ' . $this->strlen($info['data']) . '>>');
			$this->_putstream($info['data']);
			unset($this->images[$file]['data']);
			$this->_out('endobj');
			//Palette
			if ($info['cs'] == 'Indexed') {
				$this->_newobj();
				$pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
				$this->_out('<<' . $filter . '/Length ' . $this->strlen($pal) . '>>');
				$this->_putstream($pal);
				$this->_out('endobj');
			}
		}
	}

	protected function _putxobjectdict() {
		foreach ($this->images as $image)
			$this->_out('/I' . $image['i'] . ' ' . $image['n'] . ' 0 R');
	}

	protected function _putresourcedict() {
		$this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
		$this->_out('/Font <<');
		foreach ($this->fonts as $font)
			$this->_out('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
		$this->_out('>>');
		$this->_out('/XObject <<');
		$this->_putxobjectdict();
		$this->_out('>>');
	}

	protected function _putresources() {
		$this->_putfonts();
		$this->_putimages();
		//Resource dictionary
		$this->offsets[2] = $this->strlen($this->buffer);
		$this->_out('2 0 obj');
		$this->_out('<<');
		$this->_putresourcedict();
		$this->_out('>>');
		$this->_out('endobj');
	}

	protected function _putinfo() {
		$this->_out('/Producer ' . $this->_textstring('FPDF ' . FPDF_VERSION));
		if (!empty($this->title))
			$this->_out('/Title ' . $this->_textstring($this->title));
		if (!empty($this->subject))
			$this->_out('/Subject ' . $this->_textstring($this->subject));
		if (!empty($this->author))
			$this->_out('/Author ' . $this->_textstring($this->author));
		if (!empty($this->keywords))
			$this->_out('/Keywords ' . $this->_textstring($this->keywords));
		if (!empty($this->creator))
			$this->_out('/Creator ' . $this->_textstring($this->creator));
		$this->_out('/CreationDate ' . $this->_textstring('D:' . date('YmdHis')));
	}

	protected function _putcatalog() {
		$this->_out('/Type /Catalog');
		$this->_out('/Pages 1 0 R');
		if ($this->ZoomMode == 'fullpage')
			$this->_out('/OpenAction [3 0 R /Fit]');
		elseif ($this->ZoomMode == 'fullwidth')
			$this->_out('/OpenAction [3 0 R /FitH null]');
		elseif ($this->ZoomMode == 'real')
			$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
		elseif (!is_string($this->ZoomMode))
			$this->_out('/OpenAction [3 0 R /XYZ null null ' . ($this->ZoomMode / 100) . ']');
		if ($this->LayoutMode == 'single')
			$this->_out('/PageLayout /SinglePage');
		elseif ($this->LayoutMode == 'continuous')
			$this->_out('/PageLayout /OneColumn');
		elseif ($this->LayoutMode == 'two')
			$this->_out('/PageLayout /TwoColumnLeft');
	}

	protected function _putheader() {
		$this->_out('%PDF-' . $this->PDFVersion);
	}

	protected function _puttrailer() {
		$this->_out('/Size ' . ($this->n + 1));
		$this->_out('/Root ' . $this->n . ' 0 R');
		$this->_out('/Info ' . ($this->n - 1) . ' 0 R');
	}

	protected function _enddoc() {
		$this->_putheader();
		$this->_putpages();
		$this->_putresources();
		//Info
		$this->_newobj();
		$this->_out('<<');
		$this->_putinfo();
		$this->_out('>>');
		$this->_out('endobj');
		//Catalog
		$this->_newobj();
		$this->_out('<<');
		$this->_putcatalog();
		$this->_out('>>');
		$this->_out('endobj');
		//Cross-ref
		$o = $this->strlen($this->buffer);
		$this->_out('xref');
		$this->_out('0 ' . ($this->n + 1));
		$this->_out('0000000000 65535 f ');
		for ($i = 1; $i <= $this->n; $i++)
			$this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
		//Trailer
		$this->_out('trailer');
		$this->_out('<<');
		$this->_puttrailer();
		$this->_out('>>');
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->state = 3;
	}

	protected function _beginpage($orientation) {
		$this->page++;
		$this->fakePage++;
		$this->pages[$this->page] = '';
		$this->state = 2;
		$this->x = $this->lMargin;
		$this->y = $this->tMargin;
		$this->FontFamily = '';
		//Page orientation
		if (!$orientation)
			$orientation = $this->DefOrientation;
		else {
			$orientation = strtoupper($orientation[0]);
			if ($orientation != $this->DefOrientation)
				$this->OrientationChanges[$this->page] = true;
		}
		if ($orientation != $this->CurOrientation) {
			//Change orientation
			if ($orientation == 'P') {
				$this->wPt = $this->fwPt;
				$this->hPt = $this->fhPt;
				$this->w = $this->fw;
				$this->h = $this->fh;
			} else {
				$this->wPt = $this->fhPt;
				$this->hPt = $this->fwPt;
				$this->w = $this->fh;
				$this->h = $this->fw;
			}
			$this->PageBreakTrigger = $this->h - $this->bMargin;
			$this->CurOrientation = $orientation;
		}
	}

	protected function _endpage() {
		//End of page contents
		$this->state = 1;
	}

	protected function _newobj() {
		//Begin a new object
		$this->n++;
		$this->offsets[$this->n] = $this->strlen($this->buffer);
		$this->_out($this->n . ' 0 obj');
	}

	protected function _dounderline($x, $y, $txt) {
		//Underline text
		$up = $this->CurrentFont['up'];
		$ut = $this->CurrentFont['ut'];
		$w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ');
		return sprintf('%.2f %.2f %.2f %.2f re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$ut / 1000 * $this->FontSizePt);
	}

	protected function _parsejpg($file) {
		//Extract info from a JPEG file
		$a = GetImageSize($file);
		if (!$a)
			$this->Error('Missing or incorrect image file: ' . $file);
		if ($a[2] != 2)
			$this->Error('Not a JPEG file: ' . $file);
		if (!isset($a['channels']) || $a['channels'] == 3)
			$colspace = 'DeviceRGB';
		elseif ($a['channels'] == 4)
			$colspace = 'DeviceCMYK';
		else
			$colspace='DeviceGray';
		$bpc = isset($a['bits']) ? $a['bits'] : 8;
		//Read whole file
		$f = fopen($file, 'rb');
		$data = '';
		while (!feof($f))
			$data.=fread($f, 4096);
		fclose($f);
		return array('w' => $a[0], 'h' => $a[1], 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'DCTDecode', 'data' => $data);
	}

	protected function _parsepng($file) {
		//Extract info from a PNG file
		$f = fopen($file, 'rb');
		if (!$f)
			$this->Error('Can\'t open image file: ' . $file);
		//Check signature
		if (fread($f, 8) != chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10))
			$this->Error('Not a PNG file: ' . $file);
		//Read header chunk
		fread($f, 4);
		if (fread($f, 4) != 'IHDR')
			$this->Error('Incorrect PNG file: ' . $file);
		$w = $this->_freadint($f);
		$h = $this->_freadint($f);
		$bpc = ord(fread($f, 1));
		if ($bpc > 8)
			$this->Error('16-bit depth not supported: ' . $file);
		$ct = ord(fread($f, 1));
		if ($ct == 0)
			$colspace = 'DeviceGray';
		elseif ($ct == 2)
			$colspace = 'DeviceRGB';
		elseif ($ct == 3)
			$colspace = 'Indexed';
		else
			$this->Error('Alpha channel not supported: ' . $file);
		if (ord(fread($f, 1)) != 0)
			$this->Error('Unknown compression method: ' . $file);
		if (ord(fread($f, 1)) != 0)
			$this->Error('Unknown filter method: ' . $file);
		if (ord(fread($f, 1)) != 0)
			$this->Error('Interlacing not supported: ' . $file);
		fread($f, 4);
		$parms = '/DecodeParms <</Predictor 15 /Colors ' . ($ct == 2 ? 3 : 1) . ' /BitsPerComponent ' . $bpc . ' /Columns ' . $w . '>>';
		//Scan chunks looking for palette, transparency and image data
		$pal = '';
		$trns = '';
		$data = '';
		do {
			$n = $this->_freadint($f);
			$type = fread($f, 4);
			if ($type == 'PLTE') {
				//Read palette
				$pal = fread($f, $n);
				fread($f, 4);
			} elseif ($type == 'tRNS') {
				//Read transparency info
				$t = fread($f, $n);
				if ($ct == 0)
					$trns = array(ord($this->substr($t, 1, 1)));
				elseif ($ct == 2)
					$trns = array(ord($this->substr($t, 1, 1)), ord($this->substr($t, 3, 1)), ord($this->substr($t, 5, 1)));
				else {
					$pos = $this->strpos($t, chr(0));
					if ($pos !== false)
						$trns = array($pos);
				}
				fread($f, 4);
			}
			elseif ($type == 'IDAT') {
				//Read image data block
				$data.=fread($f, $n);
				fread($f, 4);
			} elseif ($type == 'IEND')
				break;
			else
				fread($f, $n + 4);
		}
		while ($n);
		if ($colspace == 'Indexed' && empty($pal))
			$this->Error('Missing palette in ' . $file);
		fclose($f);
		return array('w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'parms' => $parms, 'pal' => $pal, 'trns' => $trns, 'data' => $data);
	}

	protected function _freadint($f) {
		//Read a 4-byte integer from file
		$a = unpack('Ni', fread($f, 4));
		return $a['i'];
	}

	protected function _textstring($s) {
		//Format a text string
		return '(' . $this->_escape($s) . ')';
	}

	protected function _escape($s) {
		//Add \ before \, ( and )
		return str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $s)));
	}

	protected function _putstream($s) {
		$this->_out('stream');
		$this->_out($s);
		$this->_out('endstream');
	}

	protected function _out($s) {
		//Add a line to the document
		if ($this->state == 2)
			$this->pages[$this->page].=$s . "\n";
		else
			$this->buffer.=$s . "\n";
	}
    // (c) Xavier Nicolay
	// V1.0 : 2004-01-17
	//
    // CONSTRUCTOR
	//
    /*function MEM_IMAGE($orientation = 'P', $unit = 'mm', $format = 'A4') {
		$this->FPDF($orientation, $unit, $format);
		//Register var stream protocol (requires PHP>=4.3.2)
		if (function_exists('stream_wrapper_register'))
			stream_wrapper_register('var', 'VariableStream');
	}*/

	//
	// PRIVATE FUNCTIONS
	//
    private function _readstr($var, &$pos, $n) {
		//Read some bytes from string
		$string = $this->substr($var, $pos, $n);
		$pos += $n;
		return $string;
	}

	private function _readstr_int($var, &$pos) {
		//Read a 4-byte integer from string
		$i = ord($this->_readstr($var, $pos, 1)) << 24;
		$i+=ord($this->_readstr($var, $pos, 1)) << 16;
		$i+=ord($this->_readstr($var, $pos, 1)) << 8;
		$i+=ord($this->_readstr($var, $pos, 1));
		return $i;
	}

	private function parsemempng($var) {
		$pos = 0;
		//Check signature
		$sig = $this->_readstr($var, $pos, 8);
		if ($sig != chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10))
			$this->Error('Not a PNG image');
		//Read header chunk
		$this->_readstr($var, $pos, 4);
		$ihdr = $this->_readstr($var, $pos, 4);
		if ($ihdr != 'IHDR')
			$this->Error('Incorrect PNG Image');
		$w = $this->_readstr_int($var, $pos);
		$h = $this->_readstr_int($var, $pos);
		$bpc = ord($this->_readstr($var, $pos, 1));
		if ($bpc > 8)
			$this->Error('16-bit depth not supported: ' . $file);
		$ct = ord($this->_readstr($var, $pos, 1));
		if ($ct == 0)
			$colspace = 'DeviceGray';
		elseif ($ct == 2)
			$colspace = 'DeviceRGB';
		elseif ($ct == 3)
			$colspace = 'Indexed';
		else
			$this->Error('Alpha channel not supported: ' . $file);
		if (ord($this->_readstr($var, $pos, 1)) != 0)
			$this->Error('Unknown compression method: ' . $file);
		if (ord($this->_readstr($var, $pos, 1)) != 0)
			$this->Error('Unknown filter method: ' . $file);
		if (ord($this->_readstr($var, $pos, 1)) != 0)
			$this->Error('Interlacing not supported: ' . $file);
		$this->_readstr($var, $pos, 4);
		$parms = '/DecodeParms <</Predictor 15 /Colors ' . ($ct == 2 ? 3 : 1) . ' /BitsPerComponent ' . $bpc . ' /Columns ' . $w . '>>';
		//Scan chunks looking for palette, transparency and image data
		$pal = '';
		$trns = '';
		$data = '';
		do {
			$n = $this->_readstr_int($var, $pos);
			$type = $this->_readstr($var, $pos, 4);
			if ($type == 'PLTE') {
				//Read palette
				$pal = $this->_readstr($var, $pos, $n);
				$this->_readstr($var, $pos, 4);
			} elseif ($type == 'tRNS') {
				//Read transparency info
				$t = $this->_readstr($var, $pos, $n);
				if ($ct == 0)
					$trns = array(ord($this->substr($t, 1, 1)));
				elseif ($ct == 2)
					$trns = array(ord($this->substr($t, 1, 1)), ord($this->substr($t, 3, 1)), ord($this->substr($t, 5, 1)));
				else {
					$pos = $this->strpos($t, chr(0));
					if (is_int($pos))
						$trns = array($pos);
				}
				$this->_readstr($var, $pos, 4);
			}
			elseif ($type == 'IDAT') {
				//Read image data block
				$data.=$this->_readstr($var, $pos, $n);
				$this->_readstr($var, $pos, 4);
			} elseif ($type == 'IEND')
				break;
			else
				$this->_readstr($var, $pos, $n + 4);
		}
		while ($n);
		if ($colspace == 'Indexed' and empty($pal))
			$this->Error('Missing palette in ' . $file);
		return array('w' => $w,
			'h' => $h,
			'cs' => $colspace,
			'bpc' => $bpc,
			'f' => 'FlateDecode',
			'parms' => $parms,
			'pal' => $pal,
			'trns' => $trns,
			'data' => $data);
	}

	/*	 * ***************** */
	/* PUBLIC FUNCTIONS */
	/*	 * ***************** */

	public function ImageMem($data, $x, $y, $w = 0, $h = 0, $link = '') {
		//Put the PNG image stored in $data
		$id = md5($data);
		if (!isset($this->images[$id])) {
			$info = $this->parsemempng($data);
			$info['i'] = count($this->images) + 1;
			$this->images[$id] = $info;
		}
		else
			$info = $this->images[$id];

		//Automatic width and height calculation if needed
		if ($w == 0 and $h == 0) {
			//Put image at 72 dpi
			$w = $info['w'] / $this->k;
			$h = $info['h'] / $this->k;
		}
		if ($w == 0)
			$w = $h * $info['w'] / $info['h'];
		if ($h == 0)
			$h = $w * $info['h'] / $info['w'];
		$this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
		if ($link)
			$this->Link($x, $y, $w, $h, $link);
	}

	public function ImageGD($im, $x, $y, $w = 0, $h = 0, $link = '') {
		//Put the GD image $im
		ob_start();
		imagepng($im);
		$data = ob_get_contents();
		ob_end_clean();
		$this->ImageMem($data, $x, $y, $w, $h, $link);
	}
}

//Handle special IE contype request
if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'contype') {
	header('Content-Type: application/pdf');
	exit;
}
?>