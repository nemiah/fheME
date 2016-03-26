/**
 *
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

var phim = {
	window: null,
	
	init: function(){
		/*phim.window = windowWithRme("mphim", -1, "chatPopup", [], "", "window", {height: 300, width:550, left: $j.jStorage.get("phimX", 20), top: $j.jStorage.get("phimY", 20), name: 'phim', scroll: false});
		
		window.setTimeout(function(){
			if(phim.window.closed)
				return;
			
			$j.jStorage.set('phimX', phim.window.screenX);
			$j.jStorage.set('phimY', phim.window.screenY);
		}, 60 * 2 * 1000);*/
			
		
		
		/*window.setInterval(function(){
			if(phim.window.closed)
				return;
			
			$j.jStorage.set('phimX', phim.window.screenX);
			$j.jStorage.set('phimY', phim.window.screenY);
		}, 60 * 10 * 1000);*/
		
		return;
	}
	
	/*formatMessage: function(data){
		var d = new Date(data.timeSent * 1000);
		return "<p style=\"padding:3px;line-height:1.5;\"><span style=\"color:grey;\">("+(d.getDate() < 10 ? "0" : "")+d.getDate()+"."+(d.getMonth() + 1 < 10 ? "0" : "")+(d.getMonth() + 1)+"."+d.getFullYear()+" "+(d.getHours() < 10 ? "0" : "")+d.getHours()+":"+(d.getMinutes() < 10 ? "0" : "")+d.getMinutes()+")</span> <b>"+phim.id2user(data.from)+":</b> <span"+(data.to == phim.currentUser ? " class=\"phimUnreadMessage"+data.from+"\" style=\"color:orange;\"" : "")+">"+data.message+"</span></p>";//("+data.timeSent+") 
	},*/
};

$j(function(){
	//phim.init();
});
