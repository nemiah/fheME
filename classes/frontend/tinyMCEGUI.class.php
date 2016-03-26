<?php
/*
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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class tinyMCEGUI {
	private $ID;
	public function __construct($ID) {
		$this->ID = $ID;
	}
	
	public static function editorMail($tinyMCEID, $saveCallback = null, $buttons = null, $css = null){
		if($buttons == null)
			$buttons = "undo redo | pastetext | styleselect fontsizeselect | bold italic underline | bullist numlist table link | fullscreen code";
		
		return '
			$j("#'.$tinyMCEID.'").tinymce({
				menubar: false,
				statusbar: false,
				toolbar1: "'.$buttons.'",
				plugins: [
					"lists link image print preview hr",
					"code fullscreen",
					"save table",
					"paste textcolor"
				],
				paste_as_text: true,
				browser_spellcheck : true,
				content_css : "./styles/tinymce/email.css",
				convert_urls : false,
				extended_valid_elements : "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],blockquote[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type],div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]",
				language : "de",
				paste_data_images:true,
				entity_encoding : "raw"'.($saveCallback != null ? ',
				save_onsavecallback : '.$saveCallback.'' : "").'
			});';
	}
	
	public static function editorDokument($tinyMCEID, $saveCallback, $buttons = null, $css = "./styles/tinymce/office.css", $picturesDir = null, $onInit = ""){
		if($buttons == null)
			$buttons = "save | undo redo | pastetext | styleselect fontsizeselect fontselect | bold italic underline forecolor | hr fullscreen code";
		
		$B = new Button("Bilder", "new", "icon");
		$B->sidePanel("tinyMCE", "-1", "sidePanelAttachments", array("'$picturesDir'"));
		
		$fonts = "";
		if(file_exists(Util::getRootPath()."ubiquitous/Fonts/"))
			$fonts .= ";Ubuntu=Ubuntu;Orbitron=Orbitron;Raleway=Raleway";
		
		try {
			$AC = anyC::get("Vorlage");
			$AC->addAssocV3("VorlageNewFonts", "!=", "");
			while($V = $AC->n()){
				$newFonts = json_decode($V->A("VorlageNewFonts"));

				foreach($newFonts AS $f){
					if(!file_exists(FileStorage::getFilesDir().$f->file))
						continue;

					if(strpos($fonts, $f->name) !== false)
						continue;

					$fonts .= ";".$f->name."=".$f->name;
				}
			}
		} catch (ClassNotFoundException $e){
			
		}
		
		return '
			$j("#'.$tinyMCEID.'").tinymce({
				menubar: false,
				statusbar: false,
				toolbar1: "'.$buttons.'",
				plugins: [
					"lists link image print preview hr",
					"code fullscreen noneditable",
					"save table",
					"paste textcolor"
				],
				style_formats:[
					{
						title: "Headers",
						items: [
							{title: "Header 1",format: "h1"},
							{title: "Header 2",format: "h2"},
							{title: "Header 3",format: "h3"},
							{title: "Header 4",format: "h4"},
							{title: "Header 5",format: "h5"},
							{title: "Header 6",format: "h6"}
						]
					}
				],
				font_formats: "Helvetica=helvetica;Courier=courier;Times New Roman=times new roman'.$fonts.'",
				fontsize_formats: "6pt 7pt 8pt 9pt 10pt 11pt 12pt 26pt 36pt",
				paste_as_text: true,
				browser_spellcheck : true,
				content_css : "'.$css.'",
				convert_urls : false,
				extended_valid_elements : "blockquote[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type],div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]",
				language : "de",
				entity_encoding : "raw",
				save_onsavecallback : '.$saveCallback.',
				init_instance_callback: function(ed){ '.$onInit.' },
				setup : function(ed) {
					ed.addButton("phynximage", {
						title : "Bilder",
						onclick : function() {
							'.$B->getAction().'
						}
					});
				}
			});';
	}
	
	public function activeTextbausteinFont(){
		$S = Stammdaten::getActiveStammdaten();
		if(!$S)
			die("");
		
		$V = $S->A("ownTemplate");
		$V = new $V($S);
		
		header("Content-Type: text/css");
		
		
		echo "body {
	font-family:".$V->fontTextbausteine[0].";
}";
	}
	
	public function editInPopup($formID, $fieldName, $variablesCallback = null, $picturesDir = null){
		$tinyMCEID = "tinyMCEEditor".rand(100, 9000000);
		
		$ITA = new HTMLInput("tinyMCEEditor", "textarea");
		$ITA->id($tinyMCEID);
		$ITA->style("width:".($variablesCallback != null ? "830" : "1000")."px;height:300px;");
		
		if($variablesCallback != null)
			echo "<div style=\"float:right;width:160px;margin:5px;height:324px;overflow-y:auto;overflow-x:hidden;\">
					<p><small id=\"tinyMCEVarsDescription\"></small></p>
					<p style=\"margin-top:5px;\" id=\"tinyMCEVars\"></p></div>";

		echo "<div style=\"width:".($variablesCallback != null ? "830" : "1000")."px;\">".$ITA."</div>";
		
		$buttons = "save | undo redo | pastetext | styleselect fontsizeselect fontselect | bold italic underline forecolor | hr code";
		if($picturesDir AND Session::isPluginLoaded("mFile")){
			$buttons .= " table phynximage";
			$buttons = str_replace("fontselect", "", $buttons);
		}
		
		$onInit = "";
		if($fieldName == "textbausteinOben" OR $fieldName == "textbausteinUnten" OR $fieldName == "zahlungsbedingungen")
			$onInit = 'ed.dom.loadCSS("./interface/rme.php?rand="+Math.random()+"&class=tinyMCE&construct=-1&method=activeTextbausteinFont");';
		
		echo OnEvent::script("
setTimeout(function(){
	\$j('#$tinyMCEID').val(\$j('#$formID [name=$fieldName]').val());
	".$this->editorDokument($tinyMCEID, "function(content){\$j('#$formID [name=$fieldName]').val(content.getContent()).trigger('change'); ".OnEvent::closePopup("tinyMCE").OnEvent::closePopup("nicEdit")."}", $buttons, "./styles/tinymce/office.css", $picturesDir, $onInit)."
			".($variablesCallback != null ? "$variablesCallback('$fieldName');" : "")."
		}, 100);");
	}
	
	public function openInPopup($className, $classID, $fieldName){
		$C = new $className($classID);
		
		$tinyMCEID = "tinyMCEEditor".rand(100, 9000000);
		
		$ITA = new HTMLInput("tinyMCEEditor", "textarea", $C->A($fieldName));
		$ITA->id($tinyMCEID);
		$ITA->style("width:1000px;height:300px;");


		echo "<div style=\"width:1000px;\">".$ITA."</div>";
		
		
		$buttons = "save | undo redo | pastetext | styleselect fontsizeselect fontselect | bold italic underline forecolor | hr code fullscreen";
		echo OnEvent::script("
			\$j('#$tinyMCEID').css('height', contentManager.maxHeight());
setTimeout(function(){
	".$this->editorDokument($tinyMCEID, "function(content){".OnEvent::rme($C, "saveMultiEditField", array("'$fieldName'", "content.getContent()"))."".OnEvent::closePopup("tinyMCE")."}", $buttons)."
			
		}, 100);");
	}
	
	public function sidePanelAttachments($filesDir){
		$I = new HTMLInput("TBAttachments", "file");
		$I->onchange(OnEvent::rme($this, "processAttachmentUpload", array("'$filesDir'", "fileName"), " ".OnEvent::reloadSidePanel("tinyMCE")));
		echo "<div style=\"padding:5px;height:50px;\">".$I."</div></div>";
		
		if(!file_exists(FileStorage::getFilesDir()."$filesDir"))
			mkdir(FileStorage::getFilesDir()."$filesDir");
		
		$T = new HTMLTable(2, "Bilder");
		$dir = new DirectoryIterator(FileStorage::getFilesDir()."$filesDir");
		foreach ($dir as $file) {
			if($file->isDot()) continue;
			if($file->isDir()) continue;
			
			$BI = new Button("Datei löschen", "./images/i2/insert.png", "icon");
			
			$BD = new Button("Datei löschen", "./images/i2/delete.gif", "icon");
			$BD->style("float:right;margin-left:5px;");
			$BD->rmePCR("tinyMCE", "", "deleteAttachment", array("'$filesDir'", "'".$file->getFilename()."'"), OnEvent::reloadSidePanel("tinyMCE"));
			
			$T->addRow(array($BI, "$BD<small style=\"color:grey;float:right;margin-top:4px;\">".Util::formatByte($file->getSize())." </small>".(strlen($file->getFilename()) > 15 ? substr($file->getFilename(), 0, 15)."..." : $file->getFilename())));
			$T->addRowStyle("cursor:pointer;");
			
			$T->addRowEvent("click", "contentManager.tinyMCEAddImage('".DBImageGUI::imageLink("tinyMCEGUI", $filesDir, $file->getFilename(), true)."');");
		}
		
		echo $T;
	}
	
	public function loadMe(){
		
	}
	
	public function A($name){
		if($this->ID != "Textbausteine")
			return;
		
		return DBImageGUI::stringifyS("image/".Util::ext($name), FileStorage::getFilesDir().$this->ID."/$name");
	}
	
	public function processAttachmentUpload($filesDir, $fileName){
		$uloadedFile = Util::getTempDir().$fileName.".tmp";
		
		if(copy($uloadedFile, FileStorage::getFilesDir()."$filesDir/$fileName"))
			unlink($uloadedFile);
		else
			Red::errorD("Fehler beim Upload der Datei!");
	}
	
	public function deleteAttachment($filesDir, $fileName){
		unlink(FileStorage::getFilesDir()."$filesDir/".$fileName);
	}
	
	public static function fixImages($html){
		preg_match_all("/src\=\"\.\.\/interface\/loadFrame\.php\?p=DBImage&amp;id=tinyMCEGUI:::[a-zA-Z0-9]*:::([a-zA-Z0-9:\.@_-]*)\"/ismU", $html, $matches);
		if(isset($matches[1]))
			foreach($matches[1] AS $k => $imageUrl)
				$html = str_replace($matches[0][$k], "src=\"$imageUrl\"", $html);
			
		return $html;
	}
	
	public static function findImages($html){
		$images = array();
		
		preg_match_all("/src\=\"\.\.\/interface\/loadFrame\.php\?p=DBImage&amp;id=tinyMCEGUI:::([a-zA-Z0-9:\.@_-]*)\"/ismU", $html, $matches);
		if(isset($matches[1]))
			foreach($matches[1] AS $k => $imageUrl)
				$images[] = FileStorage::getFilesDir().str_replace(":::", "/", $imageUrl);
			
		
		$images = array_unique($images);
		
		return $images;
	}
}
?>