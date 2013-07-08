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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class nicEditGUI {
	public function editInPopup($formID, $fieldName, $variablesCallback = null){
		$ITA = new HTMLInput("nicEditor", "textarea");
		$ITA->id("nicEditor");
		$ITA->style("width:".($variablesCallback != null ? "800" : "1000")."px;height:300px;");
		
		if($variablesCallback != null)
			echo "<div style=\"float:right;width:190px;margin:5px;\">
					<p><small id=\"tinyMCEVarsDescription\"></small></p>
					<p style=\"margin-top:5px;\" id=\"tinyMCEVars\"></p></div>";

		echo "<div style=\"width:".($variablesCallback != null ? "800" : "1000")."px;\">".$ITA."</div>";
		
		echo OnEvent::script("
\$j('#nicEditor').val(\$j('#$formID [name=$fieldName]').val().makeHTML());

setTimeout(function(){
	new nicEditor({
		iconsPath : './libraries/nicEdit/nicEditorIconsTiny.gif',
		buttonList : ['save','bold','italic','underline'],
		maxHeight : 400,

		onSave : function(content, id, instance) {
			if(content.substr(0, 3) != '<p ' && content.substr(0, 3) != '<p>')
				content = '<p>'+content+'</p>';
			
			\$j('#$formID [name=$fieldName]').val(content.replace(/<br>/g, '<br />'));
			".OnEvent::closePopup("nicEdit")."
		}
	}).panelInstance('nicEditor');".($variablesCallback != null ? "$variablesCallback('$fieldName');" : "")."}, 100);");
	}
	
	public function openInPopup($className, $classID, $fieldName){
		$C = new $className($classID);
		
		$ITA = new HTMLInput("nicEditor", "textarea");
		$ITA->id("nicEditor");
		$ITA->style("width:998px;height:300px;");
		
		echo $ITA;
		
		echo OnEvent::script("
\$j('#nicEditor').val('".addslashes($C->A($fieldName))."'.makeHTML());

setTimeout(function(){
	new nicEditor({
		iconsPath : './libraries/nicEdit/nicEditorIconsTiny.gif',
		buttonList : ['save','bold','italic','underline'],
		maxHeight : 400,

		onSave : function(content, id, instance) {
			if(content.substr(0, 3) != '<p ' && content.substr(0, 3) != '<p>')
				content = '<p>'+content+'</p>';
			
			".OnEvent::rme($C, "saveMultiEditField", array("'$fieldName'", "content.replace(/<br>/g, '<br />')"))."

			".OnEvent::closePopup("nicEdit")."
		}
	}).panelInstance('nicEditor');}, 100);");
	}
}#nicEditors.findEditor('nicEditor').nicCommand('insertHTML', 'test' );
?>