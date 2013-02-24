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
var Util = {
	/*
	 * Kopiert vom phpBB-Forum
	 */
	insert_text: function(text, spaces, inputElement){
		if(!inputElement) return;

		var textarea = inputElement;

		if (spaces) {
			text = ' ' + text + ' ';
		}

		if (!isNaN(textarea.selectionStart)) {
			var sel_start = textarea.selectionStart;
			var sel_end = textarea.selectionEnd;

			Util.mozWrap(textarea, text, '')
			textarea.selectionStart = sel_start + text.length;
			textarea.selectionEnd = sel_end + text.length;
		} else if (textarea.createTextRange && textarea.caretPos) {
			if (baseHeight != textarea.caretPos.boundingHeight) {
				textarea.focus();
				Util.storeCaret(textarea);
			}

			var caret_pos = textarea.caretPos;
			caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) == ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
		} else {
			textarea.value = textarea.value + text;
		}

		inputElement.focus();

	},

	/**
	* Insert at Caret position. Code from
	* http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
	*/
	storeCaret: function(textEl)
	{
		if (textEl.createTextRange)
		{
			textEl.caretPos = document.selection.createRange().duplicate();
		}
	},

	/**
	* From http://www.massless.org/mozedit/
	*/
	mozWrap: function(txtarea, open, close)
	{
		var selLength = txtarea.textLength;
		var selStart = txtarea.selectionStart;
		var selEnd = txtarea.selectionEnd;
		var scrollTop = txtarea.scrollTop;

		if (selEnd == 1 || selEnd == 2)
		{
			selEnd = selLength;
		}

		var s1 = (txtarea.value).substring(0,selStart);
		var s2 = (txtarea.value).substring(selStart, selEnd)
		var s3 = (txtarea.value).substring(selEnd, selLength);

		txtarea.value = s1 + open + s2 + close + s3;
		txtarea.selectionStart = selEnd + open.length + close.length;
		txtarea.selectionEnd = txtarea.selectionStart;
		txtarea.focus();
		txtarea.scrollTop = scrollTop;

		return;
	},

	/*
	Die folgenden drei Funktionen wurden kopiert von kostenlose-javascripts.de
	http://www.kostenlose-javascripts.de/javascripts/verschiedenes/passwortgenerator.html
	*/
	getRandomNum: function(lbound, ubound) {
		return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
	},

	getRandomChar: function(number, lower, upper, other, extra) {
		var numberChars = "0123456789";
		var lowerChars = "abcdefghijklmnopqrstuvwxyz";
		var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		var otherChars = "!@#$*()-_[{]}|;:./";
		var charSet = extra;

		if (number == true)
			charSet += numberChars;

		if (lower == true)
			charSet += lowerChars;

		if (upper == true)
			charSet += upperChars;

		if (other == true)
			charSet += otherChars;

		return charSet.charAt(Util.getRandomNum(0, charSet.length));
	},

	getPassword: function(length, extraChars, firstNumber, firstLower, firstUpper, firstOther, latterNumber, latterLower, latterUpper, latterOther) {
		var rc = "";
		if (length > 0)
			rc = rc + Util.getRandomChar(firstNumber, firstLower, firstUpper, firstOther, extraChars);

		for (var idx = 1; idx < length; ++idx) {
			rc = rc + Util.getRandomChar(latterNumber, latterLower, latterUpper, latterOther, extraChars);
		}

		return rc;
	},
	
	Button: function(label, image, options){
		if(typeof options == "undefined")
			options = {};
		
		if(typeof options.type == "undefined")
			options.type = "bigButton";
	
		var id = "";
		if(options.id)
			id = "id=\""+options.id+"\"";
	
		var style = "";
		if(options.style)
			style = "style=\""+options.style+"\"";
	
		var onclick = "";
		if(options.onclick)
			onclick = "onclick=\""+options.onclick+"\"";
	
		var classes = "";
		if(options["class"])
			classes = "class=\""+options["class"]+"\"";
	
		var html = "";
		
		if(options.type == "icon")
			html = "<img "+id+" "+style+" "+onclick+" "+classes+" src=\""+image+"\" title=\""+label+"\" />";
		
		return html;
	}
}