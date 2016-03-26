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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
var Popup = {
	windowsOpen: 0,
	zIndex: 2500,
	
	lastPopups: Array(),
	lastSidePanels: Array(),
	linked: Array(),
	attached: Array(),
	
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
		
		eval("Popup.displayNamed('"+name+"', '"+title+"', { responseText: '' }, '"+targetPlugin+"', "+(typeof options == "undefined" ? "{}" : options)+");");
		if(typeof options == "string")
			eval("options = "+options+";");
		
		if(typeof options == "undefined" || typeof options.loader == "undefined" || options.loader)
			Popup.loading(targetPlugin, name);
		
		contentManager.rmePCR(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, function(transport){
			Popup.update(transport, targetPlugin, name);
		}, bps, true, function(){ Popup.close(targetPlugin, name); });
		 
		Popup.lastPopups[targetPlugin] = [title, targetPlugin, targetPluginID, targetPluginMethod, arrayCopy, null];
	},

	loading: function(targetPlugin, name){
		Popup.update({ responseText: "<p style=\"color:grey;\"><img src=\"./images/loading.svg\" style=\"height:32px;width:32px;float:left;margin-right:10px;\" />Die Daten<br />werden geladen...</p>" }, targetPlugin, name);
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
		contentManager.rmePCR(targetPlugin, values[2], values[3], arrayCopy, 'Popup.displayNamed(\'edit\', \''+values[0]+'\', transport, \''+targetPlugin+'\', {}, true);', bps);
	},

	display: function(name, transport){
		var ID = Math.random();

		Popup.create(ID,"rand",name);
		Popup.update(transport, ID, "rand");
	},

	displayNamed: function(name, title, transport, type, options, ignoreWidth){
		if(typeof ignoreWidth == "undefined")
			ignoreWidth = false;
		
		if(options && typeof options.ignoreWidth != "undefined")
			ignoreWidth = options.ignoreWidth;
		
		if(typeof type == "undefined")
			type = "";
		
		Popup.create(type,name,title, options, ignoreWidth);
		Popup.update(transport, type, name, options);
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
			var targetContainer = $j('#'+targetPluginContainer).parent().prop("id");
			//if($('#'+targetPluginContainer).parent().attr("id") == "windowsPersistent")
				
			$j('#'+targetContainer).append('<div id="'+targetPluginContainer+'SidePanel" style="z-index:'+$j('#'+targetPluginContainer).css("z-index")+';display:none;top:'+($j("#"+targetPluginContainer).css("top").replace("px", "") * 1)+'px;left:'+($j("#"+targetPluginContainer).position().left + $j("#"+targetPluginContainer).width() + 10)+'px;" class="backgroundColor0 popupSidePanel"></div>');

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
			$j('#'+targetPluginContainer+'SidePanel').html(transport.responseText);
			$j('#'+targetPluginContainer+'SidePanel').fadeIn();
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

	create: function(ID, type, name, options, ignoreWidth){
		var size = Overlay.getPageSize(true);
		var width = 400;
		var hasX = true;
		var persistent = false;
		var targetContainer = "windows";
		
		
		if(Touch.use){
			if(typeof options != "object")
				options = {};
			options.remember = false;
			options.top = 0;
			//options.fullscreen = true;
			//options.width = "100%";
			options.height = $j(window).height();
			options.hPosition = "center";
			options.absolute = true;
			options.hasMinimize = false;
		}
		
		var top = null;
		var right = null;
		var left = null;
		var hasMinimize = false;
		var fullscreen = false;
		var absolute = false;
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

			if(options.position){
				if(options.position == "left"){
					left = $j('#contentLeft').offset().left + $j('#contentLeft').width();
					top = $j('#contentLeft').offset().top + parseInt($j('#contentLeft').css('padding-top'));
				}
			}

			if(typeof options.top != "undefined")
				top = options.top;

			if(options.left)
				left = options.left;

			if(options.right)
				right = options.right;
			
			if(options.absolute)
				absolute = options.absolute;
			
			if(options.persistent)
				persistent = options.persistent;
			
			if(options.blackout)
				Overlay.showDark();
			
			if(typeof options.hasMinimize == "boolean")
				hasMinimize = options.hasMinimize;
			
			if(options.fullscreen)
				fullscreen = options.fullscreen;
			
			if(options.height)
				height = options.height;
			
			if(options.linkTo)
				Popup.linked.push([options.linkTo, ID, type]);
			
			if(options.attach){
				if($j('#'+type+'Details'+ID).length)
					return;
				
				Popup.attached.push([options.attach, ID, type]);
				
				var parentLeft = $j('#editDetails'+options.attach).position().left;
				if($j('#editDetails'+options.attach).position().left > width / 2) {
					var newLeft = $j('#editDetails'+options.attach).position().left - (width / 2);
					
					$j('#editDetails'+options.attach).animate({'left' : newLeft});
					parentLeft = newLeft;
				}
				
				top = $j('#editDetails'+options.attach).position().top;
				left = parentLeft + $j('#editDetails'+options.attach).outerWidth() + 20;
			}
				
		}
		
		if($(type+'Details'+ID)){
			if(typeof ignoreWidth == "undefined" || !ignoreWidth){
				$j('#'+type+'DetailsContent'+ID).css("width", width+"px");
				$j("#"+type+'Details'+ID).animate({"width": width+"px"}, 200, function(){ $j('#'+type+'DetailsContent'+ID).css("width", ''); });
			}
			
			if(name != "")
				$j("#"+type+'Details'+ID).find('.popupTitle').html(name);
			
			return;
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
			
			if(top < 0)
				top = 0;
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


		var element = "<div id=\""+type+'Details'+ID+"\" style=\""+(absolute ? "position:absolute;" : "")+'display:none;top:'+top+'px;'+(right != null ? 'right:'+right : 'left:'+left)+'px;width:'+width+(width.toString().indexOf("%") > -1 ? "" : "px")+';z-index:'+Popup.zIndex+"\" class=\"popup\">\n\
			<div class=\"backgroundColor1 cMHeader popupHeader\" id=\""+type+'DetailsHandler'+ID+"\">\n\
				<span id=\""+type+"DetailsCloseWindow"+ID+"\" style=\"cursor:pointer;"+(hasX ? "" : "display:none;")+"\" class=\"closeContextMenu iconic x\"></span>\n\
				"+(hasMinimize ? "<span id=\""+type+"DetailsMinimizeWindow"+ID+"\" style=\"cursor:pointer;"+(hasX ? "" : "display:none;")+"\" class=\"minimizeContextMenu iconic upload\"></span><span id=\""+type+"DetailsRestoreWindow"+ID+"\" style=\"display:none;cursor:pointer;margin-right:40px;"+(hasX ? "" : "display:none;")+"\" class=\"minimizeContextMenu iconic download\"></span>" : "")+"<span class=\"popupTitle\">"+name+"</span>\n\
			</div>\n\
			<div class=\"backgroundColor0\" style=\"clear:both;\" id=\""+type+'DetailsContentWrapper'+ID+"\">\n\
			<div id=\""+type+'DetailsContent'+ID+"\" class=\"popupContent\"></div></div>\n\
		</div>";

		if(fullscreen){
			element = "<div class=\"FSWrapper\" style=\"background-color:#888;\">"+element+"</div>";
		}

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
		
		if(fullscreen){
			var elem = $j('#'+type+'Details'+ID).parent()[0];
			if (elem.requestFullscreen) {
				elem.requestFullscreen();
			} else if (elem.msRequestFullscreen) {
				elem.msRequestFullscreen();
			} else if (elem.mozRequestFullScreen) {
				elem.mozRequestFullScreen();
			} else if (elem.webkitRequestFullscreen) {
				elem.webkitRequestFullscreen();
			}
		}
		//Event.observe(type+'Details'+ID, 'click', function(event) {Popup.updateZ(event.target);});

	},

	closeLinked: function(toFrame){
		Popup.linked.forEach(function(entry, index) {
			if(entry == null)
				return true;
			
			if(entry[0] != toFrame)
				return true;
			
			Popup.linked[index] = null;
			
			Popup.close(entry[1], entry[2]);
		});
		
		Popup.linked = Popup.linked.filter(function(element){
			if(element == null)
				return false;
			
			return true;
		});
	},

	close: function(ID, type){
		if(!$j("#"+type+'Details'+ID).length)
			return;
		
		if($j("#"+type+'Details'+ID).find(":focus").length)
			$j("#"+type+'Details'+ID).find(":focus").trigger("blur");
		
		var hasTinyMCE = $j("#"+type+'Details'+ID+" textarea[name=tinyMCEEditor], #"+type+'Details'+ID+" .tinyMCEEditor");
		if(hasTinyMCE.length)
			tinymce.EditorManager.execCommand('mceRemoveEditor',true, hasTinyMCE.attr("id"));
		
		var hasNicEdit = $j("#"+type+'Details'+ID+" textarea[name=nicEdit]");
		if(hasNicEdit.length)
			new nicEditor().removeInstance("nicEdit");
		
		
		Popup.sidePanelClose(ID, type);
		
		//Popup.windowsOpen--;
		if($j("#"+type+'Details'+ID).length)
			$j("#"+type+'Details'+ID).fadeOut(400, function(){
				if($j(this).parent().hasClass("FSWrapper"))
					$j(this).parent().remove();
				$j(this).remove();
			});//$('windows').removeChild($(type+'Details'+ID));
		Overlay.hideDark(0.1);
		
		Popup.attached.forEach(function(entry, index) {
			if(entry == null)
				return true;
			
			if(entry[0] != ID)
				return true;
			
			Popup.attached[index] = null;
			
			Popup.close(entry[1], entry[2]);
		});
		
		Popup.attached = Popup.attached.filter(function(element){
			if(element == null)
				return false;
			
			return true;
		});
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

	update: function(transport, ID, type, options){
		var exists = $j('#'+type+'Details'+ID).length;
		
		if(!exists)
			Popup.create(ID, type);
		
		if(!checkResponse(transport))
			return;
		
		if(exists){
			var hasTinyMCE = $j("#"+type+'Details'+ID+" textarea[name=tinyMCEEditor], #"+type+'Details'+ID+" .tinyMCEEditor");
			if(hasTinyMCE.length)
				tinymce.EditorManager.execCommand('mceRemoveEditor',true, hasTinyMCE.attr("id"));
		}
		
		$(type+'DetailsContent'+ID).update(transport.responseText);
		Popup.fixHeight();
		window.setTimeout(Popup.fixHeight, 400);
		
			
		Popup.show(ID, type);
	},
	
	fixHeight: function(){
		$j('.popup').each(function(k, v){
			var top = parseInt($j(v).css("top"));
			
			$j(v).find('.popupContent').css("max-height", $j(window).height() - 25 - 20).css("overflow", "auto");
			
			if($j(v).outerHeight() + top <= $j(window).height())
				return true;
			
			if(top > 10)
				$j(v).css("top", 10);
			
			//console.log(v);
		});
		/*if($j("#"+type+'Details'+ID).outerHeight() > $j(window).height()){
			if(parseInt($j("#"+type+'Details'+ID).css("top")) > 10)
				$j("#"+type+'Details'+ID).css("top", 10);
			
			$j("#"+container).css("max-height", $j(window).height() - 25 - 20).css("overflow", "auto");
			
		}*/
		
	},

	show: function(ID, type){
		$j('#'+type+'Details'+ID).fadeIn();
		//if($(type+'Details'+ID).style.display == "none")
		//	new Effect.Appear(type+'Details'+ID,{duration: 0.4});
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

$j(window).on("resize orientationChanged", function(){
	//alert("resize!");
	//$j('.FSWrapper').css('height', $j(window).height());
	Popup.fixHeight();
});