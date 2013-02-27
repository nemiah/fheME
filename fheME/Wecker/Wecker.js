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

var Wecker = {
	show: function(){
		$j('body').append('<div class="darkOverlay" id="WetterOverlay" style="display:none;"></div>');
		
		$j("#WetterOverlay").hammer().on("swipeup", function(ev){
			//alert("touch dragdown release!");
			//console.log(ev);
			$j('#WetterOverlay').slideUp("fast", function(){
				$j('#WetterOverlay').remove();
			});
			ev.gesture.preventDefault();
			ev.stopPropagation();
		});
		
		$j('#WetterOverlay').slideDown("fast", function(){
			/*$j("#WetterOverlay").hammer().on("tap", function(){
				alert("tap!");
			});*/
		});
	}
}