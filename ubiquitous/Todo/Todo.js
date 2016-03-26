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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

var ToDoMessages = {
	M001: "Änderung gespeichert"
}

var ToDo = {
	shownDetails: null,

	showDetails: function(todoID){
		rme('Todo',todoID,'loadDetails','',"if(checkResponse(transport)) ToDo.update(transport,"+todoID+");");
	},
	
	/*reloadDetails: function(todoID){
		if(EventP.shownDetails == todoID)
			EventP.showDetails(todoID);
	},*/
	
	update: function(transport, todoID){
		Popup.create(todoID, "ToDo", "Aktivität:");
		Popup.update(transport, todoID, "ToDo");
	},
	
	/*createWindow: function(todoID){
		EventP.createWindowG(todoID, "Todo");
	},
	
	closeWindow: function(todoID){
		EventP.closeWindowG(todoID, "Todo");
	},*/
	
	typeChange: function(input){
		$('TodoFromTime').parentNode.parentNode.style.display = "none";
		$('TodoTillTime').parentNode.parentNode.style.display = "none";
		$('TodoTillDay').parentNode.parentNode.style.display = "";
		$('TodoPercent').parentNode.parentNode.style.display = "";
		$('TodoPriority').parentNode.parentNode.style.display = "";
		$('TodoEstHours').parentNode.parentNode.style.display = "none";
		$('TodoLocation').parentNode.parentNode.style.display = "none";
		$('TodoCallDirection').parentNode.parentNode.style.display = "none";
		
		if(input.value == 1){
			$('TodoFromTime').parentNode.parentNode.style.display = "";
			$('TodoTillDay').parentNode.parentNode.style.display = "none";
			$('TodoPercent').parentNode.parentNode.style.display = "none";
			$('TodoPriority').parentNode.parentNode.style.display = "none";
			$('TodoEstHours').parentNode.parentNode.style.display = "";
			$('TodoLocation').parentNode.parentNode.style.display = "";
		}
		
		if(input.value == 2){
			$('TodoFromTime').parentNode.parentNode.style.display = "";
			$('TodoTillDay').parentNode.parentNode.style.display = "none";
			$('TodoPercent').parentNode.parentNode.style.display = "none";
			$('TodoPriority').parentNode.parentNode.style.display = "none";
			$('TodoEstHours').parentNode.parentNode.style.display = "";
			$('TodoCallDirection').parentNode.parentNode.style.display = "";
		}
	},

	onChange: function(){
		var r = $('TodoPercentID'+$('TodoID').value).value;;
		
		if(r == 0) $('TodoStatusID'+$('TodoID').value).selectedIndex = 0;
		if(r > 0) $('TodoStatusID'+$('TodoID').value).selectedIndex = 1;
		if(r == 100) $('TodoStatusID'+$('TodoID').value).selectedIndex = 2;
		
		saveMultiEditInput('Todo',$('TodoID').value,'TodoPercent');
		saveMultiEditInput('Todo',$('TodoID').value,'TodoStatus');
		if($('isDesktop')) contentManager.reloadFrameRight();
	}/*,
	
	onChange: function(value){
		var r = Math.round(value);
		if($('TodoPercentID'+$('TodoID').value) != r) ToDo.onSlide(value);
		saveMultiEditInput('Todo',$('TodoID').value,'TodoPercent');
		saveMultiEditInput('Todo',$('TodoID').value,'TodoStatus');
		if($('isDesktop')) contentManager.reloadFrameRight();
	}*/
}