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

var UPnP = {
	currentTargetID: null,
	currentTargetName: "",
	
	currentSourceID: null,
	currentSourceName: "",
	skipNext: false,
	
	start: function(){
		$j('.UPnPDirectory').css('margin-top', ($j('#UPnPSelection').height())+'px');
		$j('.UPnPBackButton').css('top', $j('#UPnPSelection').outerHeight()+'px').css("margin-top", ($j('.UPnPBackButton').outerHeight() * -1)+"px"); 
		$j('.UPnPDirectoryBrowser').css('min-height', ($j(window).height() - $j('#UPnPSelection').height())+'px');
		
		$j('.UPnPItem').hammer().on("hold", function(){
			var item = $j(this);
			Popup.load("Details", "UPnP", UPnP.currentSourceID, "details", [item.data("oid")]);
		});
		
		$j('.UPnPItem').each(function(k, v){
			console.log($j(v).data('oid'));
			var played = $j.jStorage.get('phynxUPnPPlayed'+$j(v).data('oid'), false);
			if(played)
				$j(v).find('.iconic.check').css("display", "inline");
		})
	},
	
	search: function(filename){
		Popup.load("Abspielen auf", "mUPnP", "-1", "search", [filename]);
	},
	
	overlay: '<div class="darkOverlay" id="UPnPOverlay" style="display:none;">\n\
		\n\
			<div id=\"UPnPLoading\" style=\"font-family:Roboto;font-size:30px;padding:10px;height:128px;width:500px;\">\n\
				<img src=\"./images/loading.svg\" style=\"float:left;margin-right:20px;margin-top:-25px;height:128px;width:128px;\" />Die verfügbaren Geräte<br />werden abgefragt...\n\
			</div>\n\
		\n\
		</div>',
	
	show: function(){
		$j('#UPnPOverlay').remove();//Clean up the hard way!
		
		$j('body').append(UPnP.overlay);
		
		
		$j('#UPnPLoading').css('margin-top', ($j(window).height() / 2) - ($j('#UPnPLoading').outerHeight() / 2));
		$j('#UPnPLoading').css('margin-left', ($j(window).width() / 2) - ($j('#UPnPLoading').outerWidth() / 2));
		
		$j('#UPnPOverlay').css("position", "absolute");
		/*$j('#UPnPOverlay').on("click", function(){
			$j('#UPnPOverlay').remove();
		});*/
		
		$j('#UPnPOverlay').fadeIn();
		contentManager.rmePCR("mUPnP", "-1", "discoverNow", [0], function(){
			contentManager.rmePCR("mUPnP", "-1", "remote", "", function(transport){
				//console.log($j('#UPnPOverlay'));
				$j('#UPnPOverlay').html(transport.responseText);

				var source = $j.jStorage.get('phynxUPnPSource', null);
				var target = $j.jStorage.get('phynxUPnPTarget', null);

				if(target != null)
					UPnP.selectTarget(target.ID, target.Name);

				if(source != null)
					UPnP.selectSource(source.ID, source.Name);
			});
		});

	},
	
	showRadio: function(){
		$j('#UPnPOverlay').remove();//Clean up the hard way!
		
		$j('body').append(UPnP.overlay);
		
		$j('#UPnPLoading').css('margin-top', ($j(window).height() / 2) - ($j('#UPnPLoading').outerHeight() / 2));
		$j('#UPnPLoading').css('margin-left', ($j(window).width() / 2) - ($j('#UPnPLoading').outerWidth() / 2));
		
		$j('#UPnPOverlay').css("position", "absolute");
		
		$j('#UPnPOverlay').fadeIn();
		
		contentManager.rmePCR("mUPnP", "-1", "discoverNow", [0], function(){
			contentManager.rmePCR("mUPnP", "-1", "remoteRadio", "", function(transport){
				$j('#UPnPOverlay').html(transport.responseText);
			});
		});
	},
	
	hide: function(){
		$j('#UPnPOverlay').fadeOut("fast", function(){
				
				$j('#UPnPOverlay').remove();
		});
	},
	
	targetSelection: function(){
		$j('#UPnPSourceSelection:visible').hide();
		
		contentManager.rmePCR("mUPnP", "-1", "getTargets", "", function(transport){ 
			$j('#UPnPTargetSelection').css("top", ($j('#UPnPSelection').height() / 2)+'px').html(transport.responseText);
			$j('#UPnPTargetSelection').slideDown(400);
			$j('#UPnPMediaSelection').slideUp(400);
		});
	},
	
	sourceSelection: function(){
		$j('#UPnPTargetSelection:visible').hide();
		
		contentManager.rmePCR("mUPnP", "-1", "getSources", "", function(transport){ 
			$j('#UPnPSourceSelection').css("top", ($j('#UPnPSelection').height() / 2)+'px').html(transport.responseText);
			$j('#UPnPSourceSelection').slideDown(400);
			$j('#UPnPMediaSelection').slideUp(400);
		});
	},
	
	selectSource: function(UPnPID, UPnPName){
		
		UPnP.currentSourceID = UPnPID;
		UPnP.currentSourceName = UPnPName;
		
		$j.jStorage.set('phynxUPnPSource', {ID: UPnPID, Name: UPnPName});
		
		contentManager.rmePCR("UPnP", UPnP.currentSourceID, "directoryTouch", ["*", UPnP.currentTargetID], function(transport){
			$j('#UPnPMediaSelection').slideDown(400, function(){
				$j('#UPnPMediaSelection').html(transport.responseText);
			});
			
			$j('#UPnPSourceSelection').slideUp(400, function(){
				$j('#UPnPSourceName').html(UPnPName);
			}); 
			
			$j('.UPnPItem, .UPnPSeries').css('display', 'inline-block');
		});
	},
	
	currentRadioTargets: [],
	currentRadioSource: null,
	toggleRadioTarget: function(UPnPID, UPnPName){
		if(!UPnP.currentRadioTargets[UPnPID])
			UPnP.currentRadioTargets[UPnPID] = true;
		else
			UPnP.currentRadioTargets[UPnPID] = false;
		
		UPnP.updateRadioTargets();
	},
			
	selectRadioSource: function(UPnPRadioStationID){
		UPnP.currentRadioSource = UPnPRadioStationID;
		UPnP.updateRadioSource();
	},
			
	updateRadioTargets: function(){
		for (key in UPnP.currentRadioTargets) {
			if (UPnP.currentRadioTargets.hasOwnProperty(key) && /^0$|^[1-9]\d*$/.test(key) && key <= 4294967294) {
				if(UPnP.currentRadioTargets[key])
					$j('#radioTarget'+key).removeClass("x").addClass("check");
				else
					$j('#radioTarget'+key).removeClass("check").addClass("x");
			}
		}
	},
			
	updateRadioSource: function(){
		$j('.radioSource').hide();
		
		if(!UPnP.currentRadioSource)
			return;
		
		$j('#radioSource'+UPnP.currentRadioSource).show();
	},
			
	actionRadio: function(action){
		var players = "";
		
		for (key in UPnP.currentRadioTargets) {
			if (UPnP.currentRadioTargets.hasOwnProperty(key) && /^0$|^[1-9]\d*$/.test(key) && key <= 4294967294) {
				if(UPnP.currentRadioTargets[key])
					players += (players != "" ? "," : "")+key;
			}
		}
		
		contentManager.rmePCR("mUPnP", -1, action+"Radio", [players, UPnP.currentRadioSource]);
	},
	
	selectTarget: function(UPnPID, UPnPName){
		UPnP.currentTargetID = UPnPID;
		UPnP.currentTargetName = UPnPName;
		
		$j.jStorage.set('phynxUPnPTarget', {ID: UPnPID, Name: UPnPName});
		
		$j('#UPnPMediaSelection').slideDown(400);
		$j('#UPnPTargetSelection').slideUp(400, function(){
			$j('#UPnPTargetName').html(UPnPName);
			$j('#UPnPSourceName').parent().parent().fadeIn();
		}); 
		
	}
}