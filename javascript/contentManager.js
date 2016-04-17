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

var lastLoadedLeft        = -1;
var lastLoadedLeftPlugin  = "";
var lastLoadedLeftPage    = 0;

var lastLoadedRight		  = -1;
var lastLoadedRightPlugin = "";
var lastLoadedRightPage   = 0;

var lastLoadedScreen		  = -1;
var lastLoadedScreenPlugin = "";
var lastLoadedScreenPage   = 0;

var contentManager = {
	oldValue: null,
	emptyContentBelow: true,
	lastLoaded: [],
	isAltUser: false,
	updateTitle: true,
	currentPlugin: null,
	historyLeft: [],
	historyRight: [],
	layout: "",
	
	maxHeight: function(){
		if(contentManager.layout == "desktop")
			return $j('#wrapper').height() - $j('#wrapperTable').height() - 20; // 20 px padding on contentLeft and contentRight
		
		if(contentManager.layout == "vertical")
			return $j(window).height() - $j('#footer').height() - 30; // 20 px padding on contentLeft and contentRight
		
		return ($j(window).height() - $j('#navTabsWrapper').outerHeight() - $j('#footer').height() - 30);
	},
			
	maxWidth: function(getWindow){
		if(!getWindow && $j('#desktopWrapper').length > 0)
			return $j('#wrapper').width();
		
		
		if(contentManager.layout == "vertical")
			return ($j(window).width() -$j('#navigation').outerWidth() - 1 - $j('#phim:visible').outerWidth());
	
		return ($j(window).width() - $j('#phim:visible').outerWidth());
	},
			
	scrollTable: function(tableID){
		var header_table = $j( '<table aria-hidden="true" id=\"head'+tableID+'\"><thead><tr><td></td></tr></thead></table>' );
		
		var scroll_div = '<div id=\"body'+tableID+'\" style="height: 120px;overflow-y: auto;"></div>';

		$j('table#'+tableID).before( header_table ).before( scroll_div );

		var columnWidths = [];
		var $targetDataTable = $j('table#'+tableID);
		var $targetHeaderTable = $j("table#head"+tableID);

		$j($targetDataTable).find('thead tr th').each(function (index) {
			columnWidths[index] = $j(this).width();
		});

		$j('div#body'+tableID).prepend($targetDataTable);
		$j('div#body'+tableID).css("width", $j($targetDataTable).width());
		//.width($j($targetDataTable).width());
			
		$j($targetDataTable).css('width', '100%');
		$j($targetDataTable).children('caption, thead, tfoot').hide();

		$j($targetHeaderTable).find('thead').replaceWith( $j( $targetDataTable ).children('caption, thead').clone().show() );

		var height = contentManager.maxHeight() - $j($targetHeaderTable).outerHeight() - $j("table#foot"+tableID+":visible").outerHeight();

		$j($targetHeaderTable).closest('.browserContainer').find('.browserContainerSubHeight').each(function(k, v){
			height -= $j(v).outerHeight();
		});

		$j('div#body'+tableID).css('height', height);
		
		$j($targetHeaderTable).closest('.browserContainer').css("position", "fixed");
		
		if($j('#contentRight').find("#"+tableID).length)
			$j('#contentRight').append("<div style=\"height:"+contentManager.maxHeight()+"px\"></div>");
		
		if($j('#contentLeft').find("#"+tableID).length)
			$j('#contentLeft').append("<div style=\"height:"+contentManager.maxHeight()+"px\"></div>");
	},
	
	init: function(layout){
		Interface.init();
		Overlay.init();
		contentManager.layout = layout;
		
		loadMenu();
		$('contentLeft').update();
		//if (document.cookie == "") document.cookie = "CookieTest = Erfolgreich"
		if (!navigator.cookieEnabled) alert("In Ihrem Browser sind Cookies deaktiviert.\nBitte aktivieren Sie Cookies, damit diese Anwendung funktioniert.");
		//DesktopLink.init();
		
		if($j.jStorage.get('phynxHideNavigation', false))
			$j('#navigation').hide();
		else
			$j('#buttonHideNavigation').css("transition", "all 2s ease-in-out").css("transform", "rotate(180deg)");
		
		$j('#buttonHideNavigation').click(function(){
			$j('#navigation').toggle();
			var newValue = !$j.jStorage.get('phynxHideNavigation', false);
			$j.jStorage.set('phynxHideNavigation', newValue);
			
			$j('#buttonHideNavigation').css("transition", "all 1s ease-out").css("transform", "rotate("+(newValue ? 0 : 180)+"deg)");
			
			$j(window).trigger("resize");
		});
	},
	
	lastLoaded: function(where, id, plugin, page){
		if(where == "left"){
			lastLoadedLeft = id;
			
			if(typeof plugin != "undefined")
				lastLoadedLeftPlugin = plugin;
			
			if(typeof page == "undefined")
				lastLoadedLeftPage = page;
		}
	},
	
	newSession: function(physion, application, plugin, cloud, title, icon){
		if(typeof cloud == "undefined")
			cloud = "";
		
		if(typeof title == "undefined")
			title = "";
		
		if(typeof icon == "undefined")
			icon = "";
		
		Popup.load("Neue Sitzung", "Util", "-1", "newSession", [physion, application, plugin, cloud, title, icon]);
	},
	
	contentBelow: function(content){
		if(content){
			contentManager.emptyContentBelow = false;
			$j('#contentBelowContent').html(content);
			
			$j('#contentBelow').slideDown(500)
		} else {
			contentManager.emptyContentBelow = true;
			$j('#contentBelow').slideUp(500, function(){
				if(contentManager.emptyContentBelow)
					$j('#contentBelowContent').html("");
			});
		}
	},
	
	/*backupLeftID: null,
	backupLeftPage: null,
	backupLeftPlugin:null,

	backupRightID: null,
	backupRightPlugin: null,
	backupRightPage: null,*/

	rootPath: null,

	backupFrames: new Object(),

	autoLogoutInhibitor: null,

	switchApplication: function(){
		Popup.closeNonPersistent();
		Popup.closePersistent();
		Menu.refresh();
		contentManager.emptyFrame("contentScreen");
		contentManager.loadDesktop();
		contentManager.loadJS();
		contentManager.loadTitle();
		contentManager.clearHistory();
	},

	clearHistory: function(){
		contentManager.historyLeft = [];
		contentManager.historyRight = []
	},

	selectRow: function(currentElement, group){
		$j(currentElement).closest('table').find(".lastSelected").removeClass("lastSelected");
		if(typeof group != "undefined")
			$j(".LSGroup"+group).removeClass("lastSelected");
		
		if($j(currentElement).prop("tagName") != "TR")
			$j(currentElement).closest('tr').addClass("lastSelected"+(typeof group != "undefined" ? " LSGroup"+group : ""));
		else
			$j(currentElement).addClass("lastSelected"+(typeof group != "undefined" ? " LSGroup"+group : ""));
	},

	loadDesktop: function(){
		contentManager.emptyFrame('contentLeft');
		contentManager.emptyFrame('contentRight');
		contentManager.loadFrame("contentScreen", "Desktop", 1, 0, "");
		/*
		new Ajax.Request('./interface/loadFrame.php?p=Desktop&id=1', {
		method: 'get',
		onSuccess: function(transport) {
			if(transport.responseText.search(/^error:/) == -1){
				lastLoadedRightPlugin = "Desktop";
				lastLoadedRight = 1;
				$('contentRight').update(transport.responseText);

				if($('DesktopMenuEntry')) setHighLight($('DesktopMenuEntry'));

				if(!$('morePluginsMenuEntry')) {
					new Ajax.Request('./interface/loadFrame.php?p=Desktop&id=2', {
						onSuccess: function(transport){
							$('contentLeft').update(transport.responseText);
						}
					});
					lastLoadedLeftPlugin = "Desktop";
					lastLoadedLeft = 2;
				}
			}
		}});*/
	},
	
	loadPlugin: function(targetFrame, targetPlugin, bps, withId, options){
		var page = 0;
		if(contentManager.historyRight[targetPlugin])
			page = contentManager.historyRight[targetPlugin][0];
		
		contentManager.loadFrame(targetFrame, targetPlugin, -1, page, bps, function(){
			if(typeof withId != "undefined" && withId != null){
				contentManager.loadFrame("contentLeft", (typeof options != "undefined" && options.single) ? options.single : targetPlugin.substr(1), withId);
				return;
			}
			
			if(targetFrame == "contentRight"){
				var historyPlugin = targetPlugin;
				if(historyPlugin == "Auftraege")
					historyPlugin = "mAuftrag";

				if(historyPlugin == "Adressen")
					historyPlugin = "mAdresse";

				if(historyPlugin == "Kategorien")
					historyPlugin = "mKategorie";

				if(historyPlugin == "Textbausteine")
					historyPlugin = "mTextbaustein";

				if(historyPlugin == "ObjekteL")
					historyPlugin = "mObjektL";

				if(contentManager.historyLeft[historyPlugin] && contentManager.historyLeft[historyPlugin][1] != -1){
					var found = false;
					if($j('#Browser'+historyPlugin+contentManager.historyLeft[historyPlugin][1]).length)
						found = true;

					if($j('#BrowserMain'+contentManager.historyLeft[historyPlugin][1]).length)
						found = true;

					if(found)
						contentManager.loadFrame("contentLeft", contentManager.historyLeft[historyPlugin][0], contentManager.historyLeft[historyPlugin][1], 0, "", function(){
							$j('#Browser'+historyPlugin+contentManager.historyLeft[historyPlugin][1]).addClass("lastSelected");
							$j('#BrowserMain'+contentManager.historyLeft[historyPlugin][1]).addClass("lastSelected");
						});

				}
			}
		},
		false,
		{
			doBefore: function(){
				if(typeof options == "object"){
					if(typeof options.doBefore == "function")
						options.doBefore();
				}
				
				contentManager.emptyFrame('contentLeft');
				if(targetFrame != "contentRight")
					contentManager.emptyFrame('contentRight');
				
				if(targetFrame != "contentScreen")
					contentManager.emptyFrame('contentScreen');
				contentManager.emptyFrame('contentBelow');
			}
		});
		
		Popup.closeNonPersistent();
		
		if($(targetPlugin+'MenuEntry'))
			setHighLight($(targetPlugin+'MenuEntry'));
	},

	loadJS: function(){
		new Ajax.Request("./interface/loadFrame.php?p=JSLoader&id=-1", {
    		method: "get",
    		onSuccess: function(transport){
    			$('DynamicJS').update('');
    			scripts = transport.responseText.split("\n");
    			for(i=0;i<scripts.length;i++) {
    				if(scripts[i] == "") continue;
    				s = document.createElement('script');

    				//src = document.createAttribute("src")
    				//src.nodeValue = scripts[i];
    				s.setAttribute("src", scripts[i]);

    				//t = document.createAttribute("type")
    				//t.nodeValue = "text/javascript";
    				s.setAttribute("type", "text/javascript");

    				$('DynamicJS').appendChild(s);
    			}
    		}
    	});
	},

	loadTitle: function(){
		if(!contentManager.updateTitle)
			return;
		
		contentManager.rmePCR("Menu", "-1", "getActiveApplicationName", "",  function(transport){
			if(!Interface.isDesktop) document.title = transport.responseText;
			else $("wrapperHandler").update(transport.responseText);
    	});
		
    	//new Ajax.Request("./interface/rme.php?class=Menu&method=getActiveApplicationName&constructor=&parameters=",{onSuccess: });
	},

	setRoot: function(path){
		contentManager.rootPath = path;
	},

	getRoot: function(){
		if(contentManager.rootPath == null)
			return "./";

		return contentManager.rootPath;
	},

	updateLine: function(FormID, elementID, CollectorClass){
		
		var CC = null;
		if(typeof CollectorClass != "undefined") CC = CollectorClass;
		else
			if($(FormID))
				CC = $(FormID).CollectorClass.value;
		

		if(CC == null)
			return;

		if(elementID != "-1"){
			new Ajax.Request('./interface/loadFrame.php?p='+CC+'&id='+elementID+'&type=main', {
				method:'get', 
				onSuccess: function(transport){
					if(checkResponse(transport)) {
						if($('BrowserMain'+elementID))
							$('BrowserMain'+elementID).update(transport.responseText);

						else if($('Browser'+CC+elementID))
							$('Browser'+CC+elementID).update(transport.responseText);

						else if($('BrowserMainD'+elementID))
							$('BrowserMainD'+elementID).update(transport.responseText);
					}
				}});
		} else {
			contentManager.reloadFrameRight();
			if(TextEditor.open) TextEditor.hide();
		}
	},

	rightSelection: function(isMultiSelection, selectPlugin, callingPlugin, callingPluginID, callingPluginFunction, addBPS){
		contentManager.loadFrame('contentRight', selectPlugin, -1, 0, selectPlugin+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+callingPlugin+','+callingPluginID+','+callingPluginFunction+','+lastLoadedRightPlugin+(addBPS ? ";"+addBPS : ""));
		/*loadFrameV2(
			'contentRight',
			pluginRight,
			pluginRight+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+calledPlugin+','+calledPluginID+','+calledPluginFunction+','+lastLoadedRightPlugin+',contentLeft,'+lastLoadedLeftPlugin+','+pluginLeftID);
			*/
	},

	leftSelection: function(isMultiSelection, pluginRight, calledPlugin, calledPluginID, calledPluginFunction){
		contentManager.loadFrame('contentLeft', pluginRight, -1, 0, pluginRight+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+calledPlugin+','+calledPluginID+','+calledPluginFunction+','+lastLoadedRightPlugin);
		/*
			'contentLeft',
			pluginRight,
			pluginRight+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+calledPlugin+','+calledPluginID+','+calledPluginFunction+','+lastLoadedRightPlugin+',contentLeft,'+lastLoadedLeftPlugin+','+pluginLeftID);*/
	},

	customSelection: function(targetFrame, callingPluginID, selectPlugin, selectJSFunction, addBPS, options){
		var opt = "";
		if(typeof options != "undefined"){
			if(options.noExitButton)
				opt += ",noExitButton";
		}
		
		contentManager.loadFrame(targetFrame, selectPlugin, -1, 0, selectPlugin+'GUI;selectionMode:customSelection,'+selectJSFunction+','+callingPluginID+opt+((typeof addBPS != "undefined" && addBPS != "") ? ";"+addBPS : ""));
	},

	setLeftFrame: function(plugin, id){
		lastLoadedLeft        = id;
		lastLoadedLeftPlugin  = plugin;
	},

	reloadOnNew: function(transport, plugin){
		contentManager.setLeftFrame(plugin, transport.responseText);
		contentManager.reloadFrame('contentLeft');
		contentManager.reloadFrame('contentRight');
	},

	reloadFrame: function(targetFrame, bps, page){
		if(targetFrame == "contentLeft" && lastLoadedLeftPlugin != "")
			contentManager.loadFrame("contentLeft", lastLoadedLeftPlugin, lastLoadedLeft, typeof page != "undefined" ? page : lastLoadedLeftPage, bps);

		if(targetFrame == "contentRight" && lastLoadedRightPlugin != ""){
			var selectedID = null;
			
			if($j('#contentRight tr.lastSelected').length > 0)
				selectedID = $j('#contentRight tr.lastSelected').attr("id");
			
			contentManager.loadFrame("contentRight", lastLoadedRightPlugin, lastLoadedRight, typeof page != "undefined" ? page : lastLoadedRightPage, bps, function(){
				if(selectedID != null)
					contentManager.selectRow($j('#'+selectedID));
			});
			
		}
		
		if(targetFrame == "contentScreen" && lastLoadedScreenPlugin != "")
			contentManager.loadFrame("contentScreen", lastLoadedScreenPlugin, lastLoadedScreen, typeof page != "undefined" ? page : lastLoadedScreenPage, bps);
		
		if(targetFrame != "contentRight" && targetFrame != "contentLeft" && targetFrame != "contentScreen" && typeof contentManager.lastLoaded[targetFrame] != "undefined")
			contentManager.loadFrame(targetFrame, contentManager.lastLoaded[targetFrame][0], contentManager.lastLoaded[targetFrame][1], contentManager.lastLoaded[targetFrame][2]);
	},
	
	reloadFrameLeft: function(bps){
		contentManager.reloadFrame("contentLeft", bps);
	},
	
	reloadFrameRight: function(bps){
		contentManager.reloadFrame("contentRight", bps);
	},

	backupFrame: function(targetFrame, backupName, force){
		if(typeof force == "undefined") force = false;

		if(typeof contentManager.backupFrames[backupName] != "undefined" && contentManager.backupFrames[backupName] != null && !force) return;

		if(targetFrame == "contentLeft"){
			contentManager.backupFrames[backupName] = [lastLoadedLeft, lastLoadedLeftPlugin, lastLoadedLeftPage];
		}
		if(targetFrame == "contentRight"){
			contentManager.backupFrames[backupName] = [lastLoadedRight, lastLoadedRightPlugin, lastLoadedRightPage];
		}

	},

	restoreFrame: function(targetFrame, backupName, force, onSuccessFunction){
		if(typeof force == "undefined") force = false;

		if(typeof contentManager.backupFrames[backupName] == "undefined" || contentManager.backupFrames[backupName] == null) {
			alert("Backup unknown");
			return;
		}
		if(contentManager.backupFrames[backupName][0] != -1 || (targetFrame == 'contentRight' && contentManager.backupFrames[backupName][1] != "") || force){
			if(contentManager.backupFrames[backupName][1] == "")
				contentManager.emptyFrame(targetFrame);
			else
				contentManager.loadFrame(targetFrame, contentManager.backupFrames[backupName][1], contentManager.backupFrames[backupName][0], contentManager.backupFrames[backupName][2],contentManager.backupFrames[backupName][1]+"GUI;-",onSuccessFunction,true);
		} else
			contentManager.emptyFrame(targetFrame);

		contentManager.backupFrames[backupName] = null;
	},

	emptyFrame: function(targetFrame){
		Popup.closeLinked(targetFrame);
		
		if(targetFrame == "contentLeft"){
			lastLoadedLeft        = -1;
			lastLoadedLeftPlugin  = "";
			lastLoadedLeftPage    = 0;
		}
		if(targetFrame == "contentRight"){
			lastLoadedRight       = -1;
			lastLoadedRightPlugin = "";
			lastLoadedRightPage   = 0;
		}
		if(targetFrame == "contentScreen"){
			lastLoadedScreen      = -1;
			lastLoadedScreenPlugin = "";
			lastLoadedScreenPage   = 0;
		}
		
		if(targetFrame != "contentRight" && targetFrame != "contentLeft" && targetFrame != "contentScreen")
			contentManager.lastLoaded[targetFrame] = ["", 0, -1];
		
		if(targetFrame == "contentBelow"){
			contentManager.contentBelow("");
			return;
		}
		
		$(targetFrame).update("");
	},

	forwardOnePage: function(targetFrame){
		if(targetFrame == "contentLeft")
			contentManager.loadFrame(targetFrame, lastLoadedLeftPlugin, lastLoadedLeft, (lastLoadedLeftPage * 1) + 1);
		
		if(targetFrame == "contentRight")
			contentManager.loadFrame(targetFrame, lastLoadedRightPlugin, lastLoadedRight, (lastLoadedRightPage * 1) + 1);

		if(targetFrame == "contentScreen")
			contentManager.loadFrame(targetFrame, lastLoadedScreenPlugin, lastLoadedScreen, (lastLoadedScreenPage * 1) + 1);
		
		if(targetFrame != "contentRight" && targetFrame != "contentLeft" && targetFrame != "contentScreen" && typeof contentManager.lastLoaded[targetFrame] != "undefined")
			contentManager.loadFrame(targetFrame, contentManager.lastLoaded[targetFrame][0], contentManager.lastLoaded[targetFrame][1], (contentManager.lastLoaded[targetFrame][2] * 1) + 1);
	},

	backwardOnePage: function(targetFrame){
		if(targetFrame == "contentLeft")
			contentManager.loadFrame(targetFrame, lastLoadedLeftPlugin, lastLoadedLeft, lastLoadedLeftPage - 1);

		if(targetFrame == "contentRight")
			contentManager.loadFrame(targetFrame, lastLoadedRightPlugin, lastLoadedRight, lastLoadedRightPage - 1);

		if(targetFrame == "contentScreen")
			contentManager.loadFrame(targetFrame, lastLoadedScreenPlugin, lastLoadedScreen, lastLoadedScreenPage - 1);
		
		if(targetFrame != "contentRight" && targetFrame != "contentLeft" && targetFrame != "contentScreen" && typeof contentManager.lastLoaded[targetFrame] != "undefined")
			contentManager.loadFrame(targetFrame, contentManager.lastLoaded[targetFrame][0], contentManager.lastLoaded[targetFrame][1], contentManager.lastLoaded[targetFrame][2] - 1);
	},

	loadPage: function(targetFrame, page){
		if(targetFrame == "contentLeft")
			contentManager.loadFrame(targetFrame, lastLoadedLeftPlugin, lastLoadedLeft, page);

		if(targetFrame == "contentRight")
			contentManager.loadFrame(targetFrame, lastLoadedRightPlugin, lastLoadedRight, page);

		if(targetFrame == "contentScreen")
			contentManager.loadFrame(targetFrame, lastLoadedScreenPlugin, lastLoadedScreen, page);
		
		if(targetFrame != "contentRight" && targetFrame != "contentLeft" && targetFrame != "contentScreen" && typeof contentManager.lastLoaded[targetFrame] != "undefined")
			contentManager.loadFrame(targetFrame, contentManager.lastLoaded[targetFrame][0], contentManager.lastLoaded[targetFrame][1], page);
	},

	saveSelection: function(classe, classId, saveFunction, idToSave, targetFrame, bps){
		contentManager.rmePCR(classe, classId, saveFunction, idToSave, function() {contentManager.reloadFrame(targetFrame);}, bps)
	},

	editInPopup: function(plugin, withId, title, bps, options){
		contentManager.loadContent(plugin, withId, 0, bps, function(transport) { 
			Popup.create(plugin, 'edit', title, options);
			Popup.update(transport, plugin, 'edit');
		});
	},

	loadInPopup: function(title, plugin, withId, page, bps){
		contentManager.loadContent(plugin, withId, page, bps, function(transport) { 
			Popup.displayNamed(plugin, title, transport);
		});
	},

	loadContent: function(plugin, withId, page, bps, onSuccessFunction, hideError){
		new Ajax.Request('./interface/loadFrame.php', {
			onSuccess: function(transport){
				if(checkResponse(transport, hideError) && typeof onSuccessFunction != "undefined" && onSuccessFunction != "") onSuccessFunction(transport);
			},
			method: "POST",
			parameters: 'p='+plugin+(typeof withId != "undefined" ? '&id='+withId : "")+((typeof bps != "undefined" && bps != "") ? '&bps='+bps : "")+((typeof page != "undefined" && page != "") ? '&page='+page : "")});
	},

	loadFrame: function(target, plugin, withId, page, bps, onSuccessFunction, hideError, options){
		if(typeof hideError == "undefined") hideError = false;


		if(typeof page == "undefined") page = 0;
		var arg = arguments;

		Popup.closeLinked(target);
		
		if(target == "contentRight"){
			lastLoadedRightPlugin = plugin;
			lastLoadedRightPage = page;
			lastLoadedRight = withId;
			contentManager.currentPlugin = plugin;
			contentManager.historyRight[plugin] = [page];
		}

		if(target == "contentLeft"){
			lastLoadedLeftPlugin = plugin;
			lastLoadedLeftPage = page;
			lastLoadedLeft = withId;
			contentManager.currentPlugin = plugin;
			contentManager.historyLeft["m"+plugin] = [plugin, withId];
		}

		if(target == "contentScreen"){
			lastLoadedScreenPlugin = plugin;
			lastLoadedScreenPage = page;
			contentManager.currentPlugin = plugin;
			lastLoadedScreen = withId;
		}
		
		if(target != "contentRight" && target != "contentLeft" && target != "contentScreen")
			contentManager.lastLoaded[target] = [plugin, withId, page];
		
		if(typeof bps != "undefined")
			bps = bps.replace(/;/, "&bpsPar[]=");
		
		new Ajax.Request('./interface/loadFrame.php?r='+Math.random(), {
			onSuccess: function(transport, textStatus, request){
				if(checkResponse(transport, hideError)) {

					if(typeof options == "object"){
						if(typeof options.doBefore == "function")
							options.doBefore();
					}

					$j("#"+target).html(transport.responseText);

					if(typeof onSuccessFunction != "undefined" && onSuccessFunction != "") onSuccessFunction(transport);

					Aspect.joinPoint("loaded", "contentManager.loadFrame", arg, transport.responseText);

				}
			},
				
			method: "POST",
			parameters: 'p='+plugin+(typeof withId != "undefined" ? '&id='+withId : "")+((typeof bps != "undefined" && bps != "") ? '&bps='+bps : "")+((typeof page != "undefined" && page != "") ? '&page='+page : "")+"&frame="+target});

	},

	rmePCR: function(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps, responseCheck, onFailureFunction){
		var arg = arguments;
		if(typeof responseCheck == "undefined")
			responseCheck = true;

		if(typeof targetMethodParameters != "string"){
			for(var i = 0; i < targetMethodParameters.length; i++)
				targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i]).replace(/\'/g,'\\\'')+"'";
				
			targetMethodParameters = targetMethodParameters.join(",");
		}
		else targetMethodParameters = "'"+targetMethodParameters.replace(/\'/g,'\\\'')+"'";

		new Ajax.Request(contentManager.getRoot()+"interface/rme.php?rand="+Math.random(), {
		method: 'post',
		parameters: "class="+targetClass+"&construct="+encodeURIComponent(targetClassId)+"&method="+targetMethod+"&parameters="+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""),
		onSuccess: function(transport) {
			var check = checkResponse(transport);
			if(!responseCheck || check) {
				
				if(transport.responseText.charAt(0) == "{" && transport.responseText.charAt(transport.responseText.length - 1) == "}")
					transport.responseData = jQuery.parseJSON(transport.responseText);
				
				if(transport.responseText.charAt(0) == "[" && transport.responseText.charAt(transport.responseText.length - 1) == "]")
					transport.responseData = jQuery.parseJSON(transport.responseText);
				
				if(typeof onSuccessFunction == "string")
					eval(onSuccessFunction);

				if(typeof onSuccessFunction == "function")
					onSuccessFunction(transport);

				Aspect.joinPoint("loaded", "contentManager.rmePCR", arg, transport.responseText);
			}
			if(!check && typeof onFailureFunction == "function")
				onFailureFunction();
		}});
	},

	iframeRme: function(targetClass, targetClassId, targetMethod, targetMethodParameters, targetFrame, bps){
		if(typeof targetMethodParameters != "string"){
			for(var i=0;i<targetMethodParameters.length;i++)
				targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";

			targetMethodParameters = targetMethodParameters.join(",");
		}
		else targetMethodParameters = "'"+targetMethodParameters+"'";

			$j('#'+targetFrame).attr("src", contentManager.getRoot()+'interface/rme.php?class='+targetClass+'&constructor='+targetClassId+'&method='+targetMethod+'&parameters='+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : "")+"&r="+Math.random()+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""));

	},

	startAutoLogoutInhibitor: function(){
		if(contentManager.autoLogoutInhibitor) return;

		contentManager.autoLogoutInhibitor = true;

		new PeriodicalExecuter(function(pe) {
			contentManager.rmePCR('Menu','','autoLogoutInhibitor','');
		}, 300);
	},

	newClassButton: function(newClass, onSuccessFunction, targetFrame, bps){

		if(typeof targetFrame == "undefined") targetFrame = "contentLeft";

		contentManager.loadFrame(targetFrame, newClass, -1, 0, bps, onSuccessFunction);
/*
		new Ajax.Request('./interface/loadFrame.php?p='+newClass+'&id=-1'+(typeof bps != "undefined" ? "&bps="+bps : ""), {
		method: 'get',
		onSuccess: function(transport) {
			if(checkResponse(transport)) {
				if(typeof targetFrame == "undefined") targetFrame = "contentLeft";
				$(targetFrame).update(transport.responseText);
				//lastLoadedLeft = -1;
				//lastLoadedLeftPlugin = newClass;

				if(typeof onsuccessFunction != "undefined" && onsuccessFunction != "") onsuccessFunction();
			}
		}});*/
	},

	/*toggleFormFields: function(mode, fields, formID){
		if(mode == "hide"){
			for (var f = 0; f < fields.length; f++) {
				cField = $(fields[f]);
				if(typeof formID != "undefined")
					for(var i = 0; i < $(formID).elements.length; i++)
						if($(formID).elements[i].name == fields[f]) cField = $(formID).elements[i];


				if(cField && cField.parentNode && cField.parentNode)
					cField.parentNode.parentNode.style.display = "none";
				else alert(fields[f]+" does not exist!");
			}
		}

		if(mode == "show"){
			for (var f = 0; f < fields.length; f++) {
				cField = $(fields[f]);
				if(typeof formID != "undefined")
					for(var i = 0; i < $(formID).elements.length; i++)
						if($(formID).elements[i].name == fields[f]) cField = $(formID).elements[i];

				if(cField && cField.parentNode && cField.parentNode)
					cField.parentNode.parentNode.style.display = "";
				else alert(fields[f]+" does not exist!");
			}
		}
	},*/

	toggleFormFields: function(mode, fields, formID){
		if(typeof formID == "undefined")
			formID = "";
		else
			formID = "#"+formID+" ";

		if(mode == "hide")
			for (var f = 0; f < fields.length; f++) {
				var fieldS = $j(formID+'select[name='+fields[f]+'],'+formID+'input[name='+fields[f]+'],'+formID+'textarea[name='+fields[f]+'],'+formID+'span[name='+fields[f]+']').parent().parent();
				fieldS.css("display", "none");
				if(fieldS.prev().hasClass("FormSeparatorWithLabel") || fieldS.prev().hasClass("FormSeparatorWithoutLabel"))
					fieldS.prev().css("display", "none");
			}

		if(mode == "show")
			for (var f = 0; f < fields.length; f++) {
				var fieldS = $j(formID+'select[name='+fields[f]+'],'+formID+'input[name='+fields[f]+'],'+formID+'textarea[name='+fields[f]+'],'+formID+'span[name='+fields[f]+']').parent().parent();
				fieldS.css("display", "");
				
				if(fieldS.prev().hasClass("FormSeparatorWithLabel") || fieldS.prev().hasClass("FormSeparatorWithoutLabel"))
					fieldS.prev().css("display", "");
			}
	},
	
	toggleFormFieldsTest: function(test, showOnTrue, showOnFalse, formID, showOnly){
		if(typeof showOnly == "undefined")
			showOnly = false;
		
		if(test){
			contentManager.toggleFormFields("show", showOnTrue, formID);
			if(!(showOnly))
				contentManager.toggleFormFields("hide", showOnFalse, formID);
		} else {
			if(!(showOnly))
				contentManager.toggleFormFields("hide", showOnTrue, formID);
			contentManager.toggleFormFields("show", showOnFalse, formID);
		}
	},
	
	formContent: function(formID){
		var fields = $j('#'+formID).serializeArray();
		
		$j.each(fields, function(key, value){ 
			if($j('#'+formID+' input[name='+value.name+']:checked').length > 0) value.value = '1'; 
		}); 
		
		$j.each($j('#'+formID+' input[type=checkbox]:not(:checked)'), function(key, value){ 
			fields.push({name: value.name, value: '0'}); 
		});
		
		return fields;
	},
	
	timeInput: function(event, timeInputID){
		if(event.keyCode == 8)
			return;
		
		if(event.keyCode == 9)
			return;
		
		
		
		if($j('#'+timeInputID).val().length == 2 && $j('#'+timeInputID).val().lastIndexOf(':') == -1){
			if($j('#'+timeInputID).val() < 24)
				$j('#'+timeInputID).val($j('#'+timeInputID).val()+':');
			else
				$j('#'+timeInputID).val($j('#'+timeInputID).val()[0]+':'+$j('#'+timeInputID).val()[1]);
		}
		
		$j('#'+timeInputID).val($j('#'+timeInputID).val().replace(/:+/, ":").replace(/[^0-9:]/g, ""));
	},
	
	connectedTimeInput: function(event, timeInput1ID, timeInput2ID){
		if(event.keyCode == 8)
			return;
		
		if(event.keyCode == 9)
			return;

		contentManager.timeInput(event, timeInput1ID);
		
		if($j('#'+timeInput1ID).val().lastIndexOf(':') == -1)
			return;
		
		var split = $j('#'+timeInput1ID).val().split(":");
		
		if(split[0] >= 23){
			$j('#'+timeInput2ID).val($j('#'+timeInput1ID).val());
			return;
		}
		
		if(split[0][0] == 0)
			split[0] = split[0][1];
		
		var hour = parseInt(split[0])+1;
		if(hour < 10)
			hour = "0"+hour;
		
		if(!split[1])
			split[1] = "0";
		
		var minutes = parseInt(split[1]);
		
		if(minutes < 10)
			minutes = "0"+minutes;
		
		$j('#'+timeInput2ID).val(hour+":"+minutes);
	},
	
	tinyMCEAddImage: function(src){
		tinymce.activeEditor.selection.setContent('<img src="'+src+'">');
	}
	/*,
	tinyMCEFileBrowser: function(field_name, url, type, win) {
		
		//alert("Field_Name: " + field_name + "nURL: " + url + "nType: " + type + "nWin: " + win); // debug/testing
		
		tinyMCE.activeEditor.windowManager.open({
			file : "./interface/rme.php?rand="+Math.random()+"&class=FileManager&construct=&method=getTinyMCEManager&parameters='"+field_name+"','"+type+"'",
			title : 'Dateimanager',
			width : 800,  // Your dimensions may differ - toy around with them!
			height : 450,
			resizable : "yes",
			inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
			close_previous : "no"
		}, {
			window : win,
			input : field_name
		});
		return false;
	}*/
}