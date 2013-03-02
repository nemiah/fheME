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
	active: null,
	snoozeTimer: null,
	currentAudio: null,
	
	loadThemAll: function(onSuccessFunction){
		if($j.jStorage.get('phynxDeviceID', null) == null)
			return;
		
		contentManager.rmePCR("mWecker", "-1", "loadThemAll", $j.jStorage.get('phynxDeviceID', null), function(transport){
			Wecker.data = $j.parseJSON(transport.responseText);
			if(typeof onSuccessFunction == "function")
				onSuccessFunction();
			
			$j('.ClockAudioPreload').remove();
			for(var i = 0; i < Wecker.data.length; i++){
				$j("body").append("<audio class=\"ClockAudioPreload\" id=\"ClockAudioFallback_"+Wecker.data[i].WeckerID+"\" src=\"./specifics/"+Wecker.data[i].WeckerFallback+"\" loop preload=\"auto\"></audio>");
			}
		});
	},
	
	show: function(){
		$j('body').append('<div class="darkOverlay" id="ClockOverlay" style="display:none;"></div>');
		
		$j("#ClockOverlay, #ClockTouch").hammer().on("touch dragup dragdown release", function(ev){
			switch(ev.type){
				case "touch":
					Wecker.startAt = ev.gesture.center.pageY;
				break;
				
				case "dragup":
				case "dragdown":
					var newY = (Wecker.startAt - ev.gesture.center.pageY) * -1;
					if(newY > 0)
						newY = 0;
					$j("#ClockOverlay").css("margin-top", newY+"px");
				break;
				
				case "release":
					if(Wecker.startAt - ev.gesture.center.pageY > 200){
						$j('#Clock').fadeOut();
						$j('#ClockOverlay').animate({"margin-top": "-100%"}, 400, "swing", function(){
							Wecker.stop();
		
							$j('#ClockOverlay').remove();
							$j('.ClockAudioPreload').remove();
							window.clearInterval(Wecker.interval);
							Wecker.interval = null;
							Wecker.active = null;
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
		});
	},
	
	fadeIn: function(element){
		if(element.get(0).volume + 0.01 > 1)
			return;
		
		if(element.get(0).volume >= Wecker.active.WeckerVolume / 100)
			return;
		
		element.get(0).volume += 0.01;
		window.setTimeout(function(){
			Wecker.fadeIn(element);
		}, 200);
	},
	
	stop: function(){
		if(Wecker.currentAudio == null)
			return;
		
		Wecker.currentAudio.get(0).pause();
		if(Wecker.currentAudio.prop("class") != "ClockAudioPreload")
			Wecker.currentAudio.prop("src", "")
		else
			Wecker.currentAudio.get(0).currentTime = 0;
	},
	
	play: function(){
		Wecker.currentAudio = $j('#ClockAudio');
		
		Wecker.currentAudio.prop("src", Wecker.active.WeckerSource).get(0).volume = 0;
		Wecker.currentAudio.bind("canplay", function(){
			Wecker.fadeIn(Wecker.currentAudio);
		});
		Wecker.currentAudio.get(0).play();
		
		window.setTimeout(function(){
			Wecker.fallback();
		}, 15 * 1000);
	},
	
	fallback: function(){
		if(Wecker.active != null && Wecker.snoozeTimer != null && Wecker.snoozeTimer > 0)
			return;
		
		if(Wecker.currentAudio.get(0).networkState == HTMLMediaElement.NETWORK_NO_SOURCE){
			Wecker.stop();
			Wecker.currentAudio = $j('#ClockAudioFallback_'+Wecker.active.WeckerID);
			//console.log($j('#ClockAudioFallback_'+Wecker.active.WeckerID).prop("src"));
			$j('#ClockAudioFallback_'+Wecker.active.WeckerID).get(0).volume = 0;
			
			Wecker.fadeIn($j('#ClockAudioFallback_'+Wecker.active.WeckerID));
			
			$j('#ClockAudioFallback_'+Wecker.active.WeckerID).get(0).play();
		}
	},
	
	update: function(){
		var jetzt = new Date();
		var tage = ["Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"];
		var tageJS = ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"];
		var wecker = "";
		
		$j('#Clock').html("<span style=\"font-size:200px;\">"+(jetzt.getHours() < 10 ? '0' : '')+jetzt.getHours()+':'+(jetzt.getMinutes() < 10 ? '0' : '')+jetzt.getMinutes()+"</span>");
		$j('#ClockDay').html("<span>"+fheOverview.days[jetzt.getDay()]+', '+jetzt.getDate()+'. '+fheOverview.months[jetzt.getMonth()]+' '+jetzt.getFullYear()+'</span>');
		
		if(Wecker.active != null && Wecker.snoozeTimer != null && Wecker.snoozeTimer > 0){
			Wecker.snoozeTimer--;
			var SMinuten = Math.floor(Wecker.snoozeTimer / 60);
			var SSekunden = Wecker.snoozeTimer - SMinuten * 60;
			
			$j('#ClockSnoozeTimer').html(SMinuten+":"+(SSekunden < 10 ? "0" : "")+SSekunden);
		}
		
		if(Wecker.active != null && Wecker.snoozeTimer != null && Wecker.snoozeTimer == 0){
			Wecker.snoozeTimer = null;
			Wecker.play();
			$j('#ClockButtonSnoozing').fadeOut(function(){
				$j('#ClockButtonSnooze').fadeIn();
			});
			
			
		}
		
		for(var i = 0; i < Wecker.data.length; i++){
			var stunden = Math.floor(Wecker.data[i].WeckerTime / 3600);
			var minuten = (Wecker.data[i].WeckerTime - stunden * 3600) / 60;
			
			if(Wecker.data[i]["Wecker"+tageJS[jetzt.getDay()]] == "1" && Wecker.active == null && stunden == jetzt.getHours() && minuten == jetzt.getMinutes()){
				Wecker.active = Wecker.data[i];
				Wecker.play();
				$j('#ClockButtonSnooze').fadeIn();
				//console.log("play!");
			}
			
			var days = "";
			for(var j = 0; j < tage.length; j++){
				if(Wecker.data[i]["Wecker"+tage[j]] == "1")
					days += (days != "" ? ", " : "")+tage[j];
			}
			
			wecker += "<span class=\"iconic clock\"></span> <div style=\"display:inline-block;text-align:right;width:55px;\">"+stunden+":"+(minuten < 10 ? '0' : '')+minuten+"</div> <small>"+days+"</small><br />";
		}
		$j("#ClockWecker").html(wecker);
		
		
	},
	
	snooze: function(){
		Wecker.stop();
		
		Wecker.snoozeTimer = Wecker.active.WeckerRepeatAfter;
		
		$j('#ClockButtonSnooze').fadeOut("fast", function(){
			$j('#ClockButtonSnoozing').fadeIn();
		});
	},
	
	clock: function(){
		$j("#ClockOverlay").append("\
		<div id=\"ClockDay\" style=\"font-size:30px;color:#777;font-family:Roboto;font-weight:300;display:none;padding:20px;padding-bottom:0px;float:left;\"></div>\n\
		<div id=\"ClockWecker\" style=\"font-size:20px;color:#777;font-family:Roboto;font-weight:300;display:none;padding:20px;float:left;clear:both;\"></div>\n\
		<div id=\"Clock\" style=\"color:#777;font-family:Roboto;font-weight:300;display:none;text-align:right;padding:30px;padding-bottom:10px;\"></div>\n\
		<div id=\"ClockTouch\" style=\"position:absolute;z-index:5000;width:100%;\">\n\
			<div style=\"padding:30px;\">\n\
				<span style=\"font-size:200px;display:none;vertical-align:bottom;\" class=\"iconic curved_arrow\" id=\"ClockButtonSnooze\"></span>\n\
				<div id=\"ClockButtonSnoozing\" style=\"display:none;vertical-align:bottom;\">\n\
					<span style=\"font-size:200px;\" class=\"iconic moon_stroke\"></span><br />\n\
					<span id=\"ClockSnoozeTimer\" style=\"color:#777;font-family:Roboto;font-weight:300;\"></span>\n\
				</div>\n\
			</div>\n\
		</div>\n\
		<audio id=\"ClockAudio\"></audio>");
		
		$j('#ClockButtonSnooze').hammer().on("tap", function(){
			Wecker.snooze();
		});
		
		Wecker.update();
		$j('#Clock').css("margin-top", ($j(window).height() - $j('#Clock').outerHeight())+"px").fadeIn("slow", function(){
			$j('#ClockDay').fadeIn("fast", function(){
				$j('#ClockWecker').fadeIn();
			});
			
		});
		
		$j('#ClockTouch').css("height", $j('#Clock').outerHeight()).css("top", ($j('#Clock').offset().top)+"px");
		Wecker.interval = window.setInterval(function(){
			Wecker.update();
		}, 1000);
	}
}

$j(function(){
	Wecker.loadThemAll();
});