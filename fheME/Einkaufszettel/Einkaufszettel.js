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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

var Einkaufszettel = {

}
/*
$j(window).keydown(function(event){
	if(!$j('#EinkaufszettelInput'))
		return;
	
	if(event.keyCode < 48 && event.keyCode != 13)
		return;
	
	if(event.keyCode > 57)
		return;
	
	if($j('#EinkaufszettelInput') && event.keyCode != 13)
		$j('#EinkaufszettelInput').append(String.fromCharCode(event.keyCode));
	
	if(event.keyCode == 13 && $j('#EinkaufszettelInput').html() != "")
		contentManager.rmePCR("mEinkaufszettel", -1, "addEAN", $j('#EinkaufszettelInput').html(), function(transport){
			$j('#EinkaufszettelInput').html("");
			
			$j('#EinkaufszettelLastAdded .emptyElement').remove();
			
			var kids = $j('#EinkaufszettelLastAdded').children();
			for(var i = 2; i < kids.length; i++)
				$j(kids[i]).remove();
			
			$j('#EinkaufszettelLastAdded').prepend(transport.responseText);
		}
	);
});*/