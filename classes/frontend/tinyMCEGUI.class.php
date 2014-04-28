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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class tinyMCEGUI {
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
				extended_valid_elements : "blockquote[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type],div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]",
				language : "de",
				entity_encoding : "raw"'.($saveCallback != null ? ',
				save_onsavecallback : '.$saveCallback.'' : "").'
			});';
	}
	
	public static function editorDokument($tinyMCEID, $saveCallback, $buttons = null, $css = null){
		if($buttons == null)
			$buttons = "save | undo redo | pastetext | styleselect fontsizeselect fontselect | bold italic underline forecolor | hr fullscreen code";
		
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
				font_formats: "Helvetica=helvetica;Courier=courier;Times New Roman=times new roman;Ubuntu=Ubuntu;Orbitron=Orbitron;Raleway=Raleway",
				paste_as_text: true,
				browser_spellcheck : true,
				content_css : "./styles/tinymce/office.css",
				convert_urls : false,
				extended_valid_elements : "blockquote[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type],div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]",
				language : "de",
				entity_encoding : "raw",
				save_onsavecallback : '.$saveCallback.'

			});';
	}
	
	public function editInPopup($formID, $fieldName, $variablesCallback = null){
		$tinyMCEID = "tinyMCEEditor".rand(100, 9000000);
		
		$ITA = new HTMLInput("tinyMCEEditor", "textarea");
		$ITA->id($tinyMCEID);
		$ITA->style("width:".($variablesCallback != null ? "830" : "1000")."px;height:300px;");
		
		if($variablesCallback != null)
			echo "<div style=\"float:right;width:160px;margin:5px;\">
					<p><small id=\"tinyMCEVarsDescription\"></small></p>
					<p style=\"margin-top:5px;\" id=\"tinyMCEVars\"></p></div>";

		echo "<div style=\"width:".($variablesCallback != null ? "830" : "1000")."px;\">".$ITA."</div>";
		
		
		$buttons = "save | undo redo | pastetext | styleselect fontsizeselect fontselect | bold italic underline forecolor | hr code";
		echo OnEvent::script("
setTimeout(function(){
	\$j('#$tinyMCEID').val(\$j('#$formID [name=$fieldName]').val());
	".$this->editorDokument($tinyMCEID, "function(content){\$j('#$formID [name=$fieldName]').val(content.getContent()).trigger('change'); ".OnEvent::closePopup("nicEdit")."}", $buttons)."
			".($variablesCallback != null ? "$variablesCallback('$fieldName');" : "")."
		}, 100);");
	}
}
?>