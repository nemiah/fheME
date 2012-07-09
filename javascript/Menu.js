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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

mouseIsOver = new Array();
lastHighLight = null;

/*
var appMenu = {
	isInit: false,

	show: function(){
		if(!appMenu.isInit)
			appMenu.init();

		$("appMenuContainer").style.display = "";
	},

	hide: function(){
		$("appMenuContainer").style.display = "none";
		$('appMenuDisplayedContainer').style.display = 'none';
	},

	init: function(){
		Sortable.create("appMenuHidden", {
			handle: "appMenuHandle",
			constraint: "vertical",
			containment: ["appMenuDisplayed", "appMenuHidden"],
			dropOnEmpty:true,
			onUpdate: appMenu.update,
			onChange: appMenu.change
		});
		Sortable.create("appMenuDisplayed", {
			handle: "appMenuHandle",
			constraint: "vertical",
			containment: ["appMenuDisplayed", "appMenuHidden"],
			dropOnEmpty:true,
			onUpdate: appMenu.update,
			onChange: appMenu.change
		});
	},

	change: function(){
		if($('appMenuHidden').children.length == 1)
			$('appMenu_emptyList').style.display = "";
		else
			$('appMenu_emptyList').style.display = "none";
	},

	update: function(element){
		
		var eid = element.id ? element.id : element.target.id; //added for compatibility with jQuery
		
		var entries = null;

		entries = Sortable.serialize(eid).replace(/appMenuHidden/g,"").replace(/appMenuDisplayed/g,"").replace(/appMenu/g,"").replace(/&/g,";").replace(/\[\]\=/g,"");

		
		if(eid == "appMenuHidden"){
			var fore = entries.split(";");

			for(var i = 0; i < fore.length; i++)
				if($(fore[i]+'MenuEntry')) {
					$(fore[i]+'MenuEntry').style.display = 'none';
					$(fore[i]+'TabMinimizer').style.display = 'none';
				}

		}
		if(eid == "appMenuDisplayed"){
			var fore = entries.split(";");

			for(var i = 0; i < fore.length; i++)
				if($(fore[i]+'MenuEntry')) {
					$(fore[i]+'MenuEntry').style.display = 'block';
					$(fore[i]+'TabMinimizer').style.display = 'block';
				}
		}
		
		rmeP("Menu", "", "saveAppMenuOrder", [eid, entries]);
	}
}*/

var Menu = {
	refresh: function(onSuccessFunction){
		new Ajax.Request("./interface/loadFrame.php?p=Menu&id=-1", {
		method: 'get',
		onSuccess: function(transport) {
			$('navigation').update(transport.responseText);
			setHighLight($(lastHighLight.id));
			
			if(typeof onSuccessFunction == "function")
				onSuccessFunction();
		}});
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
	new Ajax.Request("./interface/loadFrame.php?p=Menu&id=-1", {
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
	}});
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
