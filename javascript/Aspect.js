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

var Aspect = {
	pointCuts: new Array(),

	joinPoint: function(mode, method, arg, responseText){

		arg2 = Array.prototype.slice.call(arg);

		if(!Aspect.pointCuts[mode]) return null;
		if(!Aspect.pointCuts[mode][method]) return null;

		for(var i = 0; i < Aspect.pointCuts[mode][method].length; i++){
			if(Aspect.pointCuts[mode][method][i] == null)
				continue;
			
			var r = Aspect.pointCuts[mode][method][i][0](arg2, responseText);
			if(Aspect.pointCuts[mode][method][i][1] == true && r)
				Aspect.pointCuts[mode][method][i] = null;
		}
	},

	registerPointCut: function(mode, pointCut, advice, once){
		if(typeof once == "undefined") once = false;
		
		if(!Aspect.pointCuts[mode])
			Aspect.pointCuts[mode] = new Array();

		if(!Aspect.pointCuts[mode][pointCut])
			Aspect.pointCuts[mode][pointCut] = new Array();

		for(var i = 0; i < Aspect.pointCuts[mode][pointCut].length; i++)
			if(Aspect.pointCuts[mode][pointCut][i] == advice) return;

		Aspect.pointCuts[mode][pointCut].push([advice, once]);
	},

	unregisterPointCut: function(mode, pointCut){
		Aspect.pointCuts[mode][pointCut] = new Array();
	},

	registerOnLoadFrame: function(targetFrame, plugin, isNewEntry, advice, once){
		if(typeof isNewEntry == "undefined") isNewEntry = true;
		if(typeof once == "undefined") once = false;

		Aspect.registerPointCut("loaded", "contentManager.loadFrame", function(a, responseText){
			if(a[0] != targetFrame) return false;
			if(a[1] != plugin) return false;

			if(isNewEntry && a[2] != "-1") return false;
			if(!isNewEntry && a[2] == "-1") return false;

			return advice(a, responseText);
		}, once);
	},

	registerOnRmePCR: function(targetClass, targetMethod, advice){
		Aspect.registerPointCut("loaded", "contentManager.rmePCR", function(a, responseText){
			//alert(responseText);//Belegvorlage,2,createAuftragWithBeleg,'A',contentManager.loadFrame('contentLeft', 'Auftrag', transport.responseText);,
			if(a[0] != targetClass) return;
			if(a[2] != targetMethod) return;

			advice(a, responseText);
		});
	}
}
