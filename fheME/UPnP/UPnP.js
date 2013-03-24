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
	
	show: function(){
		$j('#UPnPOverlay').remove();//Clean up the hard way!
		
		$j('body').append('<div class="darkOverlay" id="UPnPOverlay" style="display:none;"></div>');
		/*$j('#UPnPOverlay').on("click", function(){
			$j('#UPnPOverlay').remove();
		});*/
		
		contentManager.rmePCR("mUPnP", "-1", "remote", "", function(transport){
			//console.log($j('#UPnPOverlay'));
			$j('#UPnPOverlay').html(transport.responseText);
			$j('#UPnPOverlay').fadeIn("fast", function(){
				
				//UPnP.clock();
			});
		});

	},
	
	hide: function(){
		$j('#UPnPOverlay').fadeOut("fast", function(){
				
				$j('#UPnPOverlay').remove();
		});
	},
	
	targetSelection: function(){
		contentManager.rmePCR("mUPnP", "-1", "getTargets", "", function(transport){ 
			$j('#UPnPTargetSelection').html(transport.responseText);
			$j('#UPnPTargetSelection').slideDown(400);
			$j('#UPnPMediaSelection').slideUp(400);
		});
	},
	
	sourceSelection: function(){
		contentManager.rmePCR("mUPnP", "-1", "getSources", "", function(transport){ 
			$j('#UPnPSourceSelection').html(transport.responseText);
			$j('#UPnPSourceSelection').slideDown(400);
			$j('#UPnPMediaSelection').slideUp(400);
		});
	},
	
	selectSource: function(UPnPID, UPnPName){
		UPnP.currentSourceID = UPnPID;
		UPnP.currentSourceName = UPnPName;
		
		contentManager.rmePCR("UPnP", UPnP.currentSourceID, "directoryTouch", ["0", UPnP.currentTargetID], function(transport){
			$j('#UPnPMediaSelection').html(transport.responseText);
			
			$j('#UPnPMediaSelection').slideDown(400);
			$j('#UPnPSourceSelection').slideUp(400, function(){
				$j('#UPnPSourceName').html(UPnPName);
			}); 
		});
	},
	
	selectTarget: function(UPnPID, UPnPName){
		UPnP.currentTargetID = UPnPID;
		UPnP.currentTargetName = UPnPName;
		
		
		$j('#UPnPMediaSelection').slideDown(400);
		$j('#UPnPTargetSelection').slideUp(400, function(){
			$j('#UPnPTargetName').html(UPnPName);
			$j('#UPnPSourceName').parent().parent().fadeIn();
		}); 
		
	}
}