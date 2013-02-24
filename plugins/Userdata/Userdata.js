/**
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
function addUserRestriction(){
	var cEs = $('cant'+$('uRestAction').value).value.split(":");
	rme("mUserdata","-1","setUserdata",new Array("cant"+$('uRestAction').value+cEs[0],"cant"+$('uRestAction').value+cEs[1],"uRest",lastLoadedLeft),"contentManager.reloadFrameLeft()");
}

function saveFieldRelabeling(){
	rme("mUserdata","-1","setUserdata",new Array("relabel"+$('relabelPlugin').value+":"+$('relabelField').value, $('relabelTo').value, "relab", lastLoadedLeft),"contentManager.reloadFrameLeft()");
}

function saveFieldHiding(){
	rme("mUserdata","-1","setUserdata",new Array("hideField"+$('hidePlugin').value+":"+$('hideField').value, "", "hideF", lastLoadedLeft),"contentManager.reloadFrameLeft()");
}

function savePluginSpecificRestriction(){
	rme("mUserdata","-1","setUserdata",new Array($('pSSelect').value.split(":")[0],$('pSSelect').value.split(":")[1], "pSpec", lastLoadedLeft),"contentManager.reloadFrameLeft()");
}

function addHidePlugin(){
	rme("mUserdata","-1","setUserdata",new Array("hidePlugin"+$('relabelPlugin').value.split(":")[0],$('relabelPlugin').value.split(":")[0], "pHide", lastLoadedLeft),"contentManager.reloadFrameLeft()");
}

function copyFromOtherUser(fromUserId){
	rme("User",lastLoadedLeft,"copyUserRestrictions",new Array(fromUserId),"contentManager.reloadFrameLeft()");
}