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
var DesktopLink = {
	isInit: false,

	createNew: function(targetClassName, targetClassID, targetFrame, application){
		
		//rmeP("mUserdata", "", "setUserdata", [targetClassName+";"+targetClassID+";"+targetFrame,"SymbolName",application+"DesktopLink"], "DesktopLink.loadContent(true);");
		rmeP("DesktopLink", "", "createNew", [targetClassName, targetClassID, targetFrame], "if(checkResponse(transport)) DesktopLink.loadContent(true);");
	},

	toggle: function(){
		if($("DesktopLink").style.display == "none") DesktopLink.display();
		else DesktopLink.hide();
	},

	showWrapper: function(){
		if($('DesktopLinkWrapper').style.display == "none")
			new Effect.BlindDown("DesktopLinkWrapper", {duration: 0.2});
	},

	hideWrapper: function(){
		if($('DesktopLinkWrapper').style.display != "none"){
			new Effect.BlindUp("DesktopLinkWrapper", {duration: 0.2});
			DesktopLink.hide();
		}
	},

	display: function(instant){
		if(typeof instant == "undefined") instant = false;

		if(!instant) new Effect.BlindDown("DesktopLink", {duration: 0.2});
		else $("DesktopLink").style.display = 'block';
	},

	hide: function(){
		new Effect.BlindUp("DesktopLink", {duration: 0.2});
	},

	init: function(){
		if(DesktopLink.isInit)
			return;

		var DLW = Builder.node("div", {"id": "DesktopLinkWrapper", "class" : "backgroundColor1 borderColor1", "style" : "display:none;"});
		var DLH = Builder.node("div", {"id": "DesktopLinkHandle", "class" : "backgroundColor1 borderColor1", "style" : ""});
		var DLC = Builder.node("div", {"id": "DesktopLink", "class" : "backgroundColor0 borderColor1", "style" : "display:none;"});

		$("wrapper").insertBefore(DLW, $('wrapperTable'));
		$("DesktopLinkWrapper").appendChild(DLH);
		$("DesktopLinkWrapper").appendChild(DLC);

		Event.observe('DesktopLinkHandle', 'click', function() {
			DesktopLink.toggle();
		});

		/*Event.observe('DesktopLink', 'mouseout', function() {
			DesktopLink.hide();
		});*/

		DesktopLink.loadContent();
		DesktopLink.isInit = true;
	},

	loadContent: function(blindDown){
		if(typeof blindDown == "undefined") blindDown = false;
		contentManager.loadFrame("DesktopLink", "DesktopLink", "", "", "", function(transport){
			if(transport.responseText != "") {
				if(blindDown)
					DesktopLink.display(true);
				DesktopLink.showWrapper();

				Sortable.create('DesktopLinkElements', {constraint: "horizontal", handle: "DesktopLinkHandler", onUpdate: DesktopLink.update});
			}
			else 
				DesktopLink.hideWrapper();
		});
	},

	update: function(){
		rmeP("DesktopLink", "", "updateOrder", Sortable.serialize("DesktopLinkElements").replace(/\[\]/gi,"").replace(/&/g,";").replace(/DesktopLinkElements=/g,""));
	}
}