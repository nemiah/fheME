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

mouseIsOver = new Array();
lastHighLight = null;

var Menu = {
	refresh: function(onSuccessFunction){
		contentManager.loadFrame("navigation", "Menu", -1, 0, "bps", function(){
			
			setHighLight($(lastHighLight.id));
			
			if(typeof onSuccessFunction == "function")
				onSuccessFunction();
		});
		/*new Ajax.Request("./interface/loadFrame.php?p=Menu&id=-1", {
		method: 'get',
		onSuccess: function(transport) {
			$('navigation').update(transport.responseText);
			setHighLight($(lastHighLight.id));
			
			if(typeof onSuccessFunction == "function")
				onSuccessFunction();
		}});*/
	},
	
	showTab: function(plugin){
		contentManager.rmePCR("Menu", "-1", "showTab", plugin, function(){ 
			Menu.refresh(); 
			
			contentManager.rmePCR("Spellbook", "-1", "getSortable", "1", function(transport){
				$j('#containerSortTabs').html(transport.responseText);
			});
		});
	},
	
	hideTab: function(plugin){
		contentManager.rmePCR("Menu", "-1", "hideTab", plugin, function(){ 
			Menu.refresh(); 
			
			contentManager.rmePCR("Spellbook", "-1", "getSortable", "1", function(transport){
				$j('#containerSortTabs').html(transport.responseText);
			});
		});
	}
}

function toggleTab(pluginName){
	if(pluginName == "morePlugins") {
		alert("Dieses Tab kann nicht minimiert werden.");
		return;
	}
	contentManager.rmePCR("Menu", '', 'toggleTab', pluginName, "Menu.refresh();");
}

function querySt(ji) {
	hu = window.location.search.substring(1);
	gy = hu.split('&');
	for (i=0;i<gy.length;i++) {
		ft = gy[i].split('=');
		
		if (ft[0] == ji)
			return ft[1];
	}
}

function loadMenu(){
	contentManager.loadFrame("navigation", "Menu", -1, 0, "", function(transport){
		//$('contentLeft').update('');
		//Popup.closeNonPersistent();
		contentManager.emptyFrame("contentLeft");
		
		if(transport.responseText == "-1"){
			userControl.doTestLogin();
			Overlay.show();
			return;
		} else Overlay.hide();
		
    	//if(!checkResponse(transport)) return;
    	
    	//$j('#navigation').html(transport.responseText);
    	
    	if($('morePluginsMenuEntry')){
    		contentManager.loadFrame('contentLeft','morePlugins', -1, 0,'morePluginsGUI;-');
    		setHighLight($('morePluginsMenuEntry'));
    	}
    	
    	if(typeof querySt('plugin') != 'undefined'){
			$j("#"+querySt('plugin')+'MenuEntry div').trigger("click");
    	} else
			contentManager.loadDesktop();
    	
		contentManager.loadJS();
		contentManager.loadTitle();
	});
	
	/*new Ajax.Request("./interface/loadFrame.php?p=Menu&id=-1", {
	method: 'get',
	onSuccess: function(transport) {
		$('contentLeft').update('');
		//Popup.closeNonPersistent();
		
		if(transport.responseText == "-1"){
			userControl.doTestLogin();
			Overlay.show();
			return;
		} else Overlay.hide();
		
    	if(!checkResponse(transport)) return;
    	
    	$j('#navigation').html(transport.responseText);
    	
    	if($('morePluginsMenuEntry')){
    		contentManager.loadFrame('contentLeft','morePlugins', -1, 0,'morePluginsGUI;-');
    		setHighLight($('morePluginsMenuEntry'));
    	}
    	
    	if(typeof querySt('plugin') != 'undefined'){
			$j("#"+querySt('plugin')+'MenuEntry div').trigger("click");
    	} else
			contentManager.loadDesktop();
    	
		contentManager.loadJS();

		contentManager.loadTitle();
    	//if($('messageLayer')) 
    	//if(typeof loadMessages == 'function') loadMessages();
	}});*/
}


function showMenu(name){
	mouseIsOver[name] = true;
	//$(name).style.display='block';
	new Effect.Appear(name,{duration:0.1}); 
}

function setMouseOut(name){
	mouseIsOver[name] = false;
	setTimeout("hideMenu('"+name+"')",1000);
}

function hideMenu(name){
	if(mouseIsOver[name] == false) 
	//$(name).style.display='none';//
	new Effect.Fade(name,{duration:0.1}); 
	else setTimeout("hideMenu('"+name+"')",1000);
}

function setHighLight(obj){
	if(lastHighLight != null) lastHighLight.className = lastHighLight.className.replace(/ *theOne/,"");
	obj.className += " theOne";
	lastHighLight = obj;
}