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

var phynxContextMenu = {
	container: null,
	fakeContainer: null,
	headerText: "<span onclick=\"phynxContextMenu.stop(); return false;\" class=\"closeContextMenu iconic x\" /></span>",
	toButton: null,
	goUp: false,
	
	init: function(){
		//var b = document.getElementsByTagName("body");
		
		var cMDiv = Builder.node('div', {id:"cMDiv", "class":"contextMenu backgroundColor0", style:"display:none;width:200px;"});
		var fakecMDiv = Builder.node('div', {id:"fakecMDiv", style:"top:-10000px;width:200px;", "class":'contextMenu backgroundColor0'});
		
		var cMHeader = Builder.node('div', {id:"cMHeader", "class":"backgroundColor1"});
		var fakecMHeader = Builder.node('div', {id:"fakecMHeader", "class":"backgroundColor1 cMHeader"});
		
		var cMData = Builder.node('div', {id:"cMData"});
		var fakecMData = Builder.node('div', {id:"fakecMData"});
		
		cMDiv.appendChild(cMHeader);
		cMDiv.appendChild(cMData);
		
		fakecMDiv.appendChild(fakecMHeader);
		fakecMDiv.appendChild(fakecMData);
		
		
		$('contentLeft').appendChild(cMDiv);
		$('contentLeft').appendChild(fakecMDiv);
		//phynxContextMenu.toButton.parentNode.appendChild(cMDiv);
		phynxContextMenu.container = cMDiv;
		phynxContextMenu.fakeContainer = fakecMDiv;

		$('cMDiv').style.position = "absolute";
		fakecMDiv.style.position = "absolute";
		
		new Draggable($('cMDiv'), {handler: $('cMHeader')});
		$('cMHeader').innerHTML = phynxContextMenu.headerText+"contextMenu";
	},
	
	reInit: function(){
		phynxContextMenu.container.style.display = 'none';
	},
	
	remove: function(){
		//var b = document.getElementsByTagName("body");
		//if($(phynxContextMenu.container.id)) phynxContextMenu.toButton.parentNode.removeChild(phynxContextMenu.container);
		if(phynxContextMenu.container != null && $(phynxContextMenu.container.id)){
			$('contentLeft').removeChild(phynxContextMenu.container);
		}
		if(phynxContextMenu.fakeContainer != null && $(phynxContextMenu.fakeContainer.id)){
			$('contentLeft').removeChild(phynxContextMenu.fakeContainer);
		}
			
		phynxContextMenu.container = null;
		phynxContextMenu.fakeContainer = null;
		
		phynxContextMenu.toButton = null;
	},
	
	stop: function(transport){
		if(transport && transport.responseText != "") alert(transport.responseText);
	
		new Effect.Fade(phynxContextMenu.container,{duration: 0.4});
		setTimeout("phynxContextMenu.remove();",450);
		
	},
	
	saveSelection: function(saveTo, identifier, key, onSuccessFunction){
		new Ajax.Request('./interface/rme.php?class='+saveTo+"&method=saveContextMenu&constructor='-1'&parameters='"+identifier+"','"+key+"'", {onSuccess: function(transport){
			if(checkResponse(transport)){
				phynxContextMenu.stop(transport);
			
				if(typeof onSuccessFunction != "undefined")
					eval(onSuccessFunction);
			}
		}});
	},
	
	appear: function(transport, options){
		$('cMData').update(transport.responseText);
		$('fakecMData').update(transport.responseText);
		
		if(phynxContextMenu.goUp) $j('#cMDiv').css("top", (parseInt($j('#cMDiv').css("top")) - phynxContextMenu.fakeContainer.offsetHeight)+"px");
		
		$j('#cMDiv').css("width", options.width ? options.width : "200px");
		
		
		new Effect.Appear(phynxContextMenu.container,{duration: 0.4});
	},
	
	update: function(targetClass, identifier, label){
		$('cMHeader').innerHTML = phynxContextMenu.headerText+""+label;
		contentManager.rmePCR(targetClass, '', "getContextMenuHTML", [identifier], function(transport){$('cMData').update(transport.responseText);});
	},
	
	start: function(toButton, targetClass, identifier, label, leftOrRight, upOrDown, options){
		if(typeof options == "undefined")
			options = {};
		
		if(!$('cMHeader')) 
			phynxContextMenu.container = null;

		phynxContextMenu.toButton = toButton;
		phynxContextMenu.goUp = (upOrDown && upOrDown == "up");
	
		if(phynxContextMenu.container == null) phynxContextMenu.init();
		else phynxContextMenu.reInit();

		$j('#cMHeader').html(phynxContextMenu.headerText+""+label);
		$j('#fakecMHeader').html(phynxContextMenu.headerText+""+label);
		
		var topPos = ($j(toButton).offset().top + $j(toButton).height() / 2);
		var leftPos = $j(toButton).offset().left  + $j(toButton).width() / 2;
		if(Interface.isDesktop){
			leftPos -= $j('#navTabsWrapper').outerWidth();
			topPos -= parseInt($j('#desktopWrapper').css("margin-top")) + $j(window).scrollTop();
		}
		//console.log(leftPos);
		//console.log(topPos);
		$j('#cMDiv').css("top", topPos+"px");
		if(!leftOrRight || leftOrRight == "right")
			$j('#cMDiv').css("left", leftPos+"px");
		else if(leftOrRight && leftOrRight == "left")
			$j('#cMDiv').css("left", (leftPos - $('cMDiv').style.width.replace(/px/,""))+"px");
		
		
		contentManager.rmePCR(targetClass, "", "getContextMenuHTML", [identifier], function(transport){
			phynxContextMenu.appear(transport, options);
		});
		
		//new Ajax.Request('./interface/rme.php?class='+targetClass+"&method=getContextMenuHTML&constructor=''&parameters='"+identifier+"'", {onSuccess: phynxContextMenu.appear});
	}
}