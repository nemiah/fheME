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
	startAt: null,
	interval: null,
	data: null,
	
	loadThemAll: function(){
		if($j.jStorage.get('phynxDeviceID', null) == null)
			return;
		
		contentManager.rmePCR("mWecker", "-1", "loadThemAll", $j.jStorage.get('phynxDeviceID', null), function(transport){
			Wecker.data = $j.parseJSON(transport.responseText);
		});
	},
	
	show: function(){
		$j('body').append('<div class="darkOverlay" id="ClockOverlay" style="display:none;"></div>');
		
		$j("#ClockOverlay").hammer().on("touch dragup release", function(ev){
			switch(ev.type){
				case "touch":
					Wecker.startAt = ev.gesture.center.pageY;
				break;
				
				case "dragup":
					$j("#ClockOverlay").css("margin-top", ((Wecker.startAt - ev.gesture.center.pageY) * -1)+"px");
				break;
				
				case "release":
					if(Math.abs(Wecker.startAt - ev.gesture.center.pageY) > 100){
						$j('#Clock').fadeOut();
						$j('#ClockOverlay').animate({"margin-top": "-100%"}, 400, "swing", function(){
							$j('#ClockOverlay').remove();
							window.clearInterval(Wecker.interval);
							Wecker.interval = null;
						});
					} else
						$j("#ClockOverlay").animate({"margin-top" : "0px"});
					
				break;
			}
			ev.gesture.preventDefault();
			ev.stopPropagation();
		});
		
		
		$j('#ClockOverlay').slideDown("fast", function(){
			Wecker.clock();
			/*$j("#WetterOverlay").hammer().on("tap", function(){
				alert("tap!");
			});*/
		});
	},
	
	update: function(){
		var jetzt = new Date();
		$j('#Clock').html("<span style=\"font-size:200px;\">"+(jetzt.getHours() < 10 ? '0' : '')+jetzt.getHours()+':'+(jetzt.getMinutes() < 10 ? '0' : '')+jetzt.getMinutes()+"</span>");
		$j('#ClockDay').html("<span>"+fheOverview.days[jetzt.getDay()]+', '+jetzt.getDate()+'. '+fheOverview.months[jetzt.getMonth()]+' '+jetzt.getFullYear()+'</span>');
		var wecker = "";
		for(var i = 0; i < Wecker.data.length; i++){
			wecker += "<span class=\"iconic clock\"></span> "+Wecker.data[i].WeckerTime;
		}
		$j("#ClockWecker").html(wecker);
	},
	
	clock: function(){
		$j("#ClockOverlay").append("\
		<div id=\"ClockDay\" style=\"font-size:30px;color:#888;font-family:Roboto;font-weight:300;display:none;padding:20px;padding-bottom:0px;float:left;\"></div>\n\
		<div id=\"ClockWecker\" style=\"font-size:20px;color:#888;font-family:Roboto;font-weight:300;display:none;padding:20px;float:left;clear:both;\"></div>\n\
		<div id=\"Clock\" style=\"color:#888;font-family:Roboto;font-weight:300;display:none;text-align:right;padding:30px;padding-bottom:10px;\"></div>");
		
		Wecker.update();
		$j('#Clock').css("margin-top", ($j(window).height() - $j('#Clock').outerHeight())+"px").fadeIn("slow", function(){
			$j('#ClockDay').fadeIn("fast", function(){
				$j('#ClockWecker').fadeIn();
			});
			
		});
		Wecker.interval = window.setInterval(function(){
			Wecker.update();
		}, 1000);
	}
}

$j(function(){
	Wecker.loadThemAll();
});