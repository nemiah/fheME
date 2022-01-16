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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */

var phynxContextMenu = {
	container: null,
	fakeContainer: null,
	headerText: "<span onclick=\"phynxContextMenu.stop(); return false;\" class=\"closeContextMenu iconic x\" /></span>",
	toButton: null,
	goUp: false,
	
	init: function(){
		var cMDiv = "<div id='cMDiv' class='contextMenu backgroundColor0' style='position:absolute;display:none;width:200px;'><div id='cMHeader' class='backgroundColor1'></div><div id='cMData'></div></div>";
		var fakecMDiv = "<div id='fakecMDiv' class='contextMenu backgroundColor0' style='position:absolute;top:-10000px;width:200px'><div id='fakecMHeader' class='backgroundColor1 cMHeader'></div><div id='fakecMData'></div></div>";
		
		$j('#contentLeft').append(cMDiv);
		$j('#contentLeft').append(fakecMDiv);
		
		phynxContextMenu.container = $j('#cMDiv');
		phynxContextMenu.fakeContainer = $j('#fakecMDiv');

		$j("#cMDiv").draggable({
			handle: $j('#cMHeader')
		});
		
		//new Draggable($('cMDiv'), {handler: $('cMHeader')});
		$('cMHeader').innerHTML = phynxContextMenu.headerText+"contextMenu";
	},
	
	reInit: function(){
		phynxContextMenu.container.hide();
	},
	
	remove: function(){
		//var b = document.getElementsByTagName("body");
		//if($(phynxContextMenu.container.id)) phynxContextMenu.toButton.parentNode.removeChild(phynxContextMenu.container);
		/*if(phynxContextMenu.container != null && $(phynxContextMenu.container.id)){
			$j('#contentLeft').removeChild(phynxContextMenu.container);
		}
		if(phynxContextMenu.fakeContainer != null && $(phynxContextMenu.fakeContainer.id)){
			$j('#contentLeft').removeChild(phynxContextMenu.fakeContainer);
		}*/
		$j('#cMDiv').remove();
		$j('#fakecMDiv').remove();
			
		phynxContextMenu.container = null;
		phynxContextMenu.fakeContainer = null;
		
		phynxContextMenu.toButton = null;
	},
	
	stop: function(transport){
		if(transport && transport.responseText != "") alert(transport.responseText);
		
		if(phynxContextMenu.container === null)
			return;
		
		phynxContextMenu.container.fadeOut();
		setTimeout("phynxContextMenu.remove();",450);
		
	},
	
	saveSelection: function(saveTo, identifier, key, onSuccessFunction){
		contentManager.rmePCR(saveTo, "-1", "saveContextMenu", [identifier, key], onSuccessFunction);
	},
	
	appear: function(transport, options){
		$('cMData').update(transport.responseText);
		$('fakecMData').update(transport.responseText);
		//console.log($j('#cMDiv').css("top"));
		if(phynxContextMenu.goUp) 
			$j('#cMDiv').css("top", (parseInt($j('#cMDiv').css("top")) - $j('#fakecMDiv').outerHeight())+"px");
		
		//console.log($j('#cMDiv').css("top"));
		$j('#cMDiv').css("width", options.width ? options.width : "200px");
		
		phynxContextMenu.container.fadeIn();
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
	
		if(phynxContextMenu.container == null) 
			phynxContextMenu.init();
		else 
			phynxContextMenu.reInit();

		$j('#cMHeader').html(phynxContextMenu.headerText+""+label);
		$j('#fakecMHeader').html(phynxContextMenu.headerText+""+label);
		
		var topPos = ($j(toButton).offset().top + $j(toButton).height() / 2);
		var leftPos = $j(toButton).offset().left  + $j(toButton).width() / 2;
		if(contentManager.layout == "desktop"){
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
	}
}