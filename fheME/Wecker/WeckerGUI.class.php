<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class WeckerGUI extends Wecker implements iGUIHTML2 {
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->setParser("WeckerTime", "Util::CLTimeParser");
	}
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Wecker");
	
		$gui->type("WeckerDeviceID", "select", anyC::get("Device"), "DeviceName");
		$gui->type("WeckerIsActive", "checkbox");
		$gui->type("WeckerRepeat", "checkbox");
		#$gui->type("WeckerFallback", "file");
		$gui->type("WeckerRuntime", "select", array(10 => "10 Minuten", 20 => "20 Minuten", 30 => "30 Minuten", 40 => "40 Minuten", 50 => "50 Minuten", 60 => "1 Stunde"));
		
		$gui->type("WeckerMo", "checkbox");
		$gui->type("WeckerDi", "checkbox");
		$gui->type("WeckerMi", "checkbox");
		$gui->type("WeckerDo", "checkbox");
		$gui->type("WeckerFr", "checkbox");
		$gui->type("WeckerSa", "checkbox");
		$gui->type("WeckerSo", "checkbox");
		
		$gui->type("WeckerRepeatAfter", "select", array(60 => "1 Minute", 5 * 60 => "5 Minuten", 10 * 60 => "10 Minuten", 15 * 60 => "15 Minuten", 20 * 60 => "20 Minuten"));
		$gui->type("WeckerVolume", "select", array(10 => "10%", 20 => "20%", 30 => "30%", 40 => "40%", 50 => "50%", 60 => "60%", 70 => "70%", 80 => "80%", 90 => "90%", 100 => "100%"));
		$gui->space("WeckerTime");
		$gui->space("WeckerRepeat");
		
		$gui->parser("WeckerFallback", "WeckerGUI::parserFallback");
		
		#$gui->parser("WeckerMo", "WeckerGUI::parserTage");
		
		return $gui->getEditHTML();
	}
	
	public static function parserTage($w, $E){
		
	}
	
	public static function parserFallback($w, $l, $E){
		$I = new HTMLInput("WeckerFallback", "text", $w);
		$I->style("margin-top:10px;");
		
		$IF = new HTMLInput("WeckerFallbackUpload", "file");
		$IF->onchange(OnEvent::rme($E, "processUpload", array("fileName"), "\$j('[name=WeckerFallback]').val(fileName).trigger('change');"));
		
		return $IF.$I;
	}
	
	public function processUpload($fileName){
		$ex = explode(".", strtolower($fileName));
		
		$mime = null;
		if($ex[count($ex) - 1] == "ogg")
			$mime = "ogg";
		
		if($ex[count($ex) - 1] == "mp3")
			$mime = "mp3";
		
		if($mime == null)
			Red::alertD("Datei unbekannt. Bitte verwenden Sie ogg oder mp3-Dateien.");
		
		$tempDir = Util::getTempFilename();
		
		unlink($tempDir);
		$tempDir = dirname($tempDir);
		
		$filePath = $tempDir."/".$fileName.".tmp";
		
		echo FileStorage::getFilesDir().$fileName;
		if(!copy($filePath, FileStorage::getFilesDir().$fileName))
			Red::errorD("Der Upload ist fehlgeschlagen!");
		
		unlink($filePath);
	}
}
?>