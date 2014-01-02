/**
 *
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

var trinityDB = {

	overlay: '<div class="lightOverlay" id="trinityDBOverlay" style="display:none;">\n\
		\n\
			<div id=\"trinityDBLoading\" style=\"font-family:Roboto;font-size:30px;padding:10px;height:128px;width:500px;\">\n\
				<img src=\"./images/loading.svg\" style=\"float:left;margin-right:20px;margin-top:-25px;height:128px;width:128px;\" />Die Verbindung<br />wird aufgebaut...\n\
			</div>\n\
		\n\
		</div>',
			
	show: function(id, mode){
		$j('#trinityDBOverlay').remove();//Clean up the hard way!
		
		$j('body').append(trinityDB.overlay);
		
		
		$j('#trinityDBLoading').css('margin-top', ($j(window).height() / 2) - ($j('#trinityDBLoading').outerHeight() / 2));
		$j('#trinityDBLoading').css('margin-left', ($j(window).width() / 2) - ($j('#trinityDBLoading').outerWidth() / 2));
		
		$j('#trinityDBOverlay').css("position", "absolute");
		/*$j('#UPnPOverlay').on("click", function(){
			$j('#UPnPOverlay').remove();
		});*/
		
		$j('#trinityDBOverlay').fadeIn();
		contentManager.rmePCR("mtrinityDB", "-1", "load", [id, mode], function(transport){
			$j('#trinityDBOverlay').html(transport.responseText);
			
		});

	},
	
	hide: function(){
		$j('#trinityDBOverlay').fadeOut("fast", function(){
			$j('#trinityDBOverlay').remove();
		});
	},
};