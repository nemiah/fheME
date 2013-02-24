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
var notificationArea = {
	isInit: false,
	
	init: function(){
		if(notificationArea.isInit)
			return;

		var DLW = Builder.node("div", {"id": "notificationArea", "style" : "display:none;"});
		//var DLH = Builder.node("div", {"id": "DesktopLinkHandle", "class" : "backgroundColor1 borderColor1", "style" : ""});
		//var DLC = Builder.node("div", {"id": "DesktopLink", "class" : "backgroundColor0 borderColor1", "style" : "display:none;"});

		$("wrapper").insertBefore(DLW, $('wrapperTable'));
		//$("DesktopLinkWrapper").appendChild(DLH);
		//$("DesktopLinkWrapper").appendChild(DLC);

		/*Event.observe('DesktopLinkHandle', 'click', function() {
			DesktopLink.toggle();
		});*/

		/*Event.observe('DesktopLink', 'mouseout', function() {
			DesktopLink.hide();
		});*/

		notificationArea.loadContent();
		notificationArea.isInit = true;
	},

	loadContent: function(){
		//contentManager.rmePCR("Menu", "-1", "getNotificationArea", "", function(){ });
	}
}
Event.observe(window, 'load', function() {
	notificationArea.init();
});