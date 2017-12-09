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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

mouseIsOver = new Array();
lastHighLight = null;

var Menu = {
	onTimeout: function(){ window.location.reload(); },
	
	refresh: function(onSuccessFunction){
		contentManager.loadFrame("navigation", "Menu", -1, 0, "bps", function(){
			
			Menu.setHighLight($(lastHighLight.id));
			
			if(typeof onSuccessFunction == "function")
				onSuccessFunction();
		});
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
	},
	
	toggleTab: function(pluginName){
		if(pluginName == "morePlugins") {
			alert("Dieses Tab kann nicht minimiert werden.");
			return;
		}
		contentManager.rmePCR("Menu", '', 'toggleTab', pluginName, "Menu.refresh();");
	},

	loadMenu: function(){
		contentManager.loadFrame("navigation", "Menu", -1, 0, "", function(transport){
			contentManager.emptyFrame("contentLeft");

			if(transport.responseText == "-1"){
				userControl.doTestLogin();
				Overlay.show();
				return;
			} else Overlay.hide();

			if($('morePluginsMenuEntry')){
				contentManager.loadFrame('contentLeft','morePlugins', -1, 0,'morePluginsGUI;-');
				Menu.setHighLight($('morePluginsMenuEntry'));
			}

			if(typeof Util.querySt('plugin') != 'undefined'){
				$j("#"+Util.querySt('plugin')+'MenuEntry div').trigger(Touch.trigger);
			} else
				contentManager.loadDesktop();

			contentManager.loadJS();
			contentManager.loadTitle();
		});
	},
	
	setHighLight: function(obj){
		if(lastHighLight != null) lastHighLight.className = lastHighLight.className.replace(/ *theOne/,"");
		obj.className += " theOne";
		lastHighLight = obj;
	}
}


/*function showMenu(name){
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
}*/