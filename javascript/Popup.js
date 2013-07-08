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
var Popup = {
	windowsOpen: 0,
	zIndex: 2500,
	
	lastPopups: Array(),
	lastSidePanels: Array(),
	
	presets: {
		large: {hPosition: "center", width:1000},
		center: {hPosition: "center"}
	},

	load: function(title, targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, bps, name, options){
		if(typeof name == "undefined")
			name = "edit";
		
		if(typeof targetPluginMethodParameters == "undefined")
			targetPluginMethodParameters = new Array();
		
		var arrayCopy = targetPluginMethodParameters.slice(0, targetPluginMethodParameters.length); //because targetPluginMethodParameters is only a reference
		 
		contentManager.rmePCR(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, 'Popup.displayNamed(\''+name+'\', \''+title+'\', transport, \''+targetPlugin+'\''+(typeof options != "undefined" ? ", "+options : "")+');', bps);
		 
		Popup.lastPopups[targetPlugin] = [title, targetPlugin, targetPluginID, targetPluginMethod, arrayCopy, null];
	},

	refresh: function(targetPlugin, bps, firstParameter){
		var values = Popup.lastPopups[targetPlugin];
		var arrayCopy = values[4].slice(0, values[4].length); //because targetPluginMethodParameters is only a reference
		if(typeof firstParameter != "undefined"){
			arrayCopy[0] = firstParameter;
			values[5] = firstParameter;
		}
		if(values[5] != null)
			arrayCopy[0] = values[5];
		//Popup.lastPopups[targetPlugin] = [title, targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters];
		contentManager.rmePCR(targetPlugin, values[2], values[3], arrayCopy, 'Popup.displayNamed(\'edit\', \''+values[0]+'\', transport, \''+targetPlugin+'\');', bps);
	},

	display: function(name, transport){
		var ID = Math.random();

		Popup.create(ID,"rand",name);
		Popup.update(transport, ID, "rand");
	},

	displayNamed: function(name, title, transport, type, options){
		if(typeof type == "undefined")
			type = "";
		
		Popup.create(type,name,title, options);
		Popup.update(transport, type, name);
	},

	sidePanel: function(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, popupName){
		if(typeof popupName == "undefined")
			popupName = "edit";
		
		targetPluginContainer = popupName+"Details"+targetPlugin;
		
		if($j('#'+targetPluginContainer+'SidePanel').length > 0 && Popup.lastSidePanels[targetPlugin][2] == targetPluginMethod){
			Popup.sidePanelClose(targetPlugin, popupName);
			return;
		}
	
		if($j('#'+targetPluginContainer+'SidePanel').length == 0){
			$j('#windows').append('<div id="'+targetPluginContainer+'SidePanel" style="display:none;top:'+($j("#"+targetPluginContainer).css("top").replace("px", "") * 1)+'px;left:'+($j("#"+targetPluginContainer).position().left + $j("#"+targetPluginContainer).width() + 10)+'px;" class="backgroundColor0 popupSidePanel"></div>');

			$j("#"+targetPluginContainer).bind("dragstart", function(event, ui) {
				$j('#'+targetPluginContainer+'SidePanel').fadeOut();
			});

			$j("#"+targetPluginContainer).bind("dragstop", function(event, ui) {
				$j('#'+targetPluginContainer+'SidePanel').css({top: ($j("#"+targetPluginContainer).css("top").replace("px", "") * 1), left: $j("#"+targetPluginContainer).position().left + $j("#"+targetPluginContainer).width() + 10}).fadeIn();
			});
		}
		//$j("#"+targetPluginContainer).bind("DOMNodeRemoved", function(event, ui) {
			//$j('#'+targetPluginContainer+'SidePanel').remove();
		//});
		
		Popup.lastSidePanels[targetPlugin] = [targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters.slice(0, targetPluginMethodParameters.length)];
		
		contentManager.rmePCR(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, function(transport){
			$j('#'+targetPluginContainer+'SidePanel').html(transport.responseText).fadeIn();
		});
	},

	sidePanelClose: function(parentWindowID, popupName){
		if(typeof popupName == "undefined")
			popupName = "edit";
		
		if($j('#'+popupName+'Details'+parentWindowID+'SidePanel').length == 0)
			return;
		
		$j('#'+popupName+'Details'+parentWindowID+'SidePanel').fadeOut(300, function(){$j(this).remove();});
	},
	
	sidePanelRefresh: function(targetPlugin, popupName){
		if(typeof popupName == "undefined")
			popupName = "edit";
		
		var values = Popup.lastSidePanels[targetPlugin];
		
		contentManager.rmePCR(targetPlugin, values[1], values[2], values[3].slice(0, values[3].length), function(transport){$j('#'+popupName+'Details'+targetPlugin+'SidePanel').html(transport.responseText)});
	},

	create: function(ID, type, name, options){
		if($(type+'Details'+ID)) return;
		var size = Overlay.getPageSize(true);
		var width = 400;
		var hasX = true;
		var persistent = false;
		var targetContainer = "windows";
		
		var top = null;
		var right = null;
		var left = null;
		var hasMinimize = false;
		if(typeof options == "object"){
			if(options.width)
				width = options.width;

			if(options.hPosition && options.hPosition == "center")
				right = size[0] / 2 - width / 2;

			if(options.hPosition && options.hPosition == "right")
				right = 10;

			if(options.hPosition && options.hPosition == "left")
				right = size[0] - width - 2 - 10;

			if(typeof options.hasX == "boolean")
				hasX = options.hasX;

			if(options.top)
				top = options.top;

			if(options.left)
				left = options.left;
			
			if(options.persistent)
				persistent = options.persistent;
			
			if(options.blackout)
				Overlay.showDark();
			
			if(typeof options.hasMinimize == "boolean")
				hasMinimize = options.hasMinimize;
		}
		
		if(persistent)
			targetContainer = "windowsPersistent";
		
		
		if(top == null)
			top = size[0] <= 1124 ? (66 + $(targetContainer).childNodes.length * 40) : (100 + $(targetContainer).childNodes.length * 40);
		
		if(right == null && left == null)
			right = size[0] <= 1124 ? (0) : (410 + $(targetContainer).childNodes.length * 20);
		
		//if(left != null)
		//	right = size[0] - width - left;
			
		//if($(targetContainer).firstChild == null) Popup.windowsOpen = 0;
		
		if(typeof options == "object" && options.remember && $j.jStorage.get('phynxPopupPosition'+type+'Details'+ID, null) !== null){
			var pos = $j.jStorage.get('phynxPopupPosition'+type+'Details'+ID);
			right = null;
			left = pos.left;
			top = pos.top;
			if(top > $j(window).height() - 40)
				top = 20;
		}
		
		/*var element = Builder.node(
			"div",
			{
				id: type+'Details'+ID,
				style: 'display:none;top:'+top+'px;'+(right != null ? 'right:'+right : 'left:'+left)+'px;width:'+width+'px;z-index:'+Popup.zIndex,
				"class": "popup"
			}, [
				Builder.node("div", {"class": "backgroundColor1 cMHeader", id: type+'DetailsHandler'+ID}, [
					Builder.node("a", {id: type+"DetailsCloseWindow"+ID, "class": "closeContextMenu backgroundColor0 borderColor0", style:"cursor:pointer;"+(hasX ? "" : "display:none;")}, ["X"])
					, name]),
				Builder.node("div", {"class": "backgroundColor0", style: "clear:both;", id: type+'DetailsContentWrapper'+ID}, [
					Builder.node("div", {id: type+'DetailsContent'+ID})
				])
				
			]);*/


		var element = "<div id=\""+type+'Details'+ID+"\" style=\""+'display:none;top:'+top+'px;'+(right != null ? 'right:'+right : 'left:'+left)+'px;width:'+width+'px;z-index:'+Popup.zIndex+"\" class=\"popup\">\n\
			<div class=\"backgroundColor1 cMHeader\" id=\""+type+'DetailsHandler'+ID+"\">\n\
				<span id=\""+type+"DetailsCloseWindow"+ID+"\" style=\"cursor:pointer;"+(hasX ? "" : "display:none;")+"\" class=\"closeContextMenu iconic x\"></span>\n\
				"+(hasMinimize ? "<span id=\""+type+"DetailsMinimizeWindow"+ID+"\" style=\"cursor:pointer;"+(hasX ? "" : "display:none;")+"\" class=\"minimizeContextMenu iconic upload\"></span><span id=\""+type+"DetailsRestoreWindow"+ID+"\" style=\"display:none;cursor:pointer;margin-right:40px;"+(hasX ? "" : "display:none;")+"\" class=\"minimizeContextMenu iconic download\"></span>" : "")+name+"\n\
			</div>\n\
			<div class=\"backgroundColor0\" style=\"clear:both;\" id=\""+type+'DetailsContentWrapper'+ID+"\"><div id=\""+type+'DetailsContent'+ID+"\"></div></div>\n\
		</div>";

		$j("#"+targetContainer).append(element);
		
		//new Draggable($(type+'Details'+ID), {handle: $(type+'DetailsHandler'+ID)});
		$j("#"+type+'Details'+ID).draggable({
			handle: $j('#'+type+'DetailsHandler'+ID),
			containment: "window",
			start: function(){
				if($j('#'+type+'DetailsHandler'+ID).data("minimized"))
					return;
				
				$j('#'+type+'DetailsContentWrapper'+ID).css('height', $j('#'+type+'DetailsContent'+ID).height());
				$j('#'+type+'DetailsContent'+ID).fadeOut("fast");
			},
			stop: function(){
				if($j('#'+type+'DetailsHandler'+ID).data("minimized"))
					return;
				
				$j('#'+type+'DetailsContent'+ID).fadeIn("fast", function(){
					$j('#'+type+'DetailsContentWrapper'+ID).css('height', '');
				});
					
				if(typeof options == "object" && options.remember)
					$j.jStorage.set('phynxPopupPosition'+type+'Details'+ID, $j("#"+type+'Details'+ID).position());
				
			}
		});
		Event.observe(type+'DetailsCloseWindow'+ID, 'click', function() {Popup.close(ID, type);});
		if(hasMinimize){
			Event.observe(type+'DetailsMinimizeWindow'+ID, 'click', function() {Popup.minimize(ID, type);});
			Event.observe(type+'DetailsRestoreWindow'+ID, 'click', function() {Popup.restore(ID, type);});
		}
		//Event.observe(type+'Details'+ID, 'click', function(event) {Popup.updateZ(event.target);});

	},

	close: function(ID, type){
		//new Effect.Fade(type+'Details'+ID,{duration: 0.4});
		var hasTinyMCE = $j("#"+type+'Details'+ID+" textarea[name=tinyMCEEditor]");
		if(hasTinyMCE.length){
			tinyMCE.execCommand("mceFocus", false, hasTinyMCE.attr("id"));                    
			tinyMCE.execCommand("mceRemoveControl", false, hasTinyMCE.attr("id"));
		}
		
		var hasNicEdit = $j("#"+type+'Details'+ID+" textarea[name=nicEdit]");
		if(hasNicEdit.length)
			new nicEditor().removeInstance("nicEdit");
		
		
		Popup.sidePanelClose(ID, type);
		
		//Popup.windowsOpen--;
		if($j("#"+type+'Details'+ID).length)
			$j("#"+type+'Details'+ID).fadeOut(400, function(){
				$j(this).remove();
			});//$('windows').removeChild($(type+'Details'+ID));
		Overlay.hideDark(0.1);
	},

	minimize: function(ID, type){
		$j('#'+type+"DetailsCloseWindow"+ID).hide();
		$j('#'+type+"DetailsMinimizeWindow"+ID).hide();
		$j('#'+type+"DetailsRestoreWindow"+ID).show();
		$j('#'+type+'DetailsContent'+ID).slideUp("fast");
		$j('#'+type+'DetailsHandler'+ID).data("minimized", true);
	},
			
	restore: function(ID, type){
		$j('#'+type+"DetailsCloseWindow"+ID).show();
		$j('#'+type+"DetailsMinimizeWindow"+ID).show();
		$j('#'+type+"DetailsRestoreWindow"+ID).hide();
		$j('#'+type+'DetailsContent'+ID).slideDown("fast");
		$j('#'+type+'DetailsHandler'+ID).data("minimized", false);
	},

	update: function(transport, ID, type){
		if(!$(type+'Details'+ID)) Popup.create(ID, type);
		if(!checkResponse(transport)) return;

		$(type+'DetailsContent'+ID).update(transport.responseText);
		Popup.show(ID, type);
		//Popup.windowsOpen++;
	},

	show: function(ID, type){
		if($(type+'Details'+ID).style.display == "none")
			new Effect.Appear(type+'Details'+ID,{duration: 0.4});
	},
	
	closeNonPersistent: function(){
		$j.each($j('#windows').children(), function(k, v){
			var P = $j(v).attr("id").split("Details");
			Popup.close(P[1], P[0]);
		});
	},
	
	closePersistent: function(){
		$j.each($j('#windowsPersistent').children(), function(k, v){
			var P = $j(v).attr("id").split("Details");
			Popup.close(P[1], P[0]);
		});
	}
}