/*
 *
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
var TextEditor = {
	isInit: false,
	forField: null,
	textarea: null,
	isBase64: false,
	open: false,
	forForm: null,
	changed: false,
	
	init: function(){
		var editorContainer = document.createElement("div");
		editorContainer.id = "TextEditor";
		editorContainer.style.display = "none";
		editorContainer.className = "borderColor1 backgroundColor0";
		
		var editorHeader = document.createElement("div");
		editorHeader.id = "editorHeader";
		editorHeader.className = "backgroundColor1";
				
		var editorTAContainer = document.createElement("div");
		editorTAContainer.id = "editorTAContainer";
		editorTAContainer.style.display = "none";
		
		var editorTextarea = document.createElement("textarea");
		editorTextarea.id = "editorTextarea";
		
		var saveButton = document.createElement("input");
		saveButton.type = "button";
		saveButton.id = "editorSaveButton";
		saveButton.className = "backgroundColor2";
		saveButton.value = "Editor schließen";
		
		var saveButton2 = document.createElement("input");
		saveButton2.type = "button";
		saveButton2.id = "editorSaveButton2";
		saveButton2.className = "backgroundColor2";
		saveButton2.value = "Änderungen speichern";
		
		$j(saveButton).click(TextEditor.close);
		$j(saveButton2).click(TextEditor.backgroundSave);
		$j(editorTextarea).keydown(function(){ TextEditor.changed = true; });
		
		//Event.observe(saveButton, "click", TextEditor.close);
		//Event.observe(saveButton2, "click", TextEditor.backgroundSave);
		
		editorTAContainer.appendChild(editorTextarea);
		editorTAContainer.appendChild(saveButton2);
		editorTAContainer.appendChild(saveButton);
		
		editorContainer.appendChild(editorHeader);
		editorContainer.appendChild(editorTAContainer);

		var b = document.getElementsByTagName("body");
		b[0].appendChild(editorContainer);
		
		TextEditor.textarea = editorTextarea;
		$('editorHeader').innerHTML = "<a href=\"javascript:TextEditor.close();\" class=\"closeTextEditor backgroundColor0 borderColor0\" />X</a>TextEditor";

		TextEditor.isInit = true;
	},
	
	show64: function(field, form) {
		if(TextEditor.open) return;
		if(!TextEditor.isInit) TextEditor.init();
		TextEditor.forField = field;
		TextEditor.isBase64 = true;
		TextEditor.forForm = form;
		TextEditor.changed = false;
		
		TextEditor.textarea.value = Base64.decode($(field).value);
		TextEditor.open = true;
		if(typeof Effect == "object"){
			new Effect.SlideDown('TextEditor', {duration:0.5});
			setTimeout("new Effect.SlideDown('editorTAContainer', {duration:0.5})",400);
		} else {
			TextEditor.style.display = "block";
			editorTAContainer.style.display = "block";
		}
	},
	
	show: function(field, form){
		if(TextEditor.open) return;
		if(!TextEditor.isInit) TextEditor.init();
		TextEditor.forField = field;
		TextEditor.isBase64 = false;
		TextEditor.forForm = form;
		TextEditor.changed = false;
		
		TextEditor.textarea.value = $(field).value;
		TextEditor.open = true;
		
		if(typeof Effect == "object"){
			new Effect.SlideDown('TextEditor', {duration:0.5});
			setTimeout("new Effect.SlideDown('editorTAContainer', {duration:0.5})",400);
		} else {
			TextEditor.style.display = "block";
			editorTAContainer.style.display = "block";
		}
	},

	save: function(){
		if(TextEditor.isBase64) $(TextEditor.forField).value = Base64.encode(TextEditor.textarea.value);
		else $(TextEditor.forField).value = TextEditor.textarea.value;
		TextEditor.changed = false;
		TextEditor.hide();
	},

	backgroundSave: function(){
		if(TextEditor.isBase64) $(TextEditor.forField).value = Base64.encode(TextEditor.textarea.value);
		else $(TextEditor.forField).value = TextEditor.textarea.value;
		TextEditor.changed = false;
		$(TextEditor.forForm).currentSaveButton.click();
	},

	hide: function(){
		TextEditor.open = false;
		if(typeof Effect == "object"){
			new Effect.SlideUp('editorTAContainer', {duration:0.5});
			setTimeout("new Effect.SlideUp('TextEditor', {duration:0.5})",400);
		} else {
			TextEditor.style.display = "block";
			editorTAContainer.style.display = "block";
		}
	},

	close: function(){
		if(TextEditor.changed){
			c = confirm("Wirklich schließen? Ungespeicherte Änderungen werden nicht übernommen.");
			if(!c) return;
		}
		
		TextEditor.hide();
	}
}