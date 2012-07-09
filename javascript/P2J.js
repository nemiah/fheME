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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

var $j = jQuery.noConflict();

jQuery(function($j){
        $j.datepicker.regional['de_DE'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
                closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
                prevText: '&#x3c;zurück', prevStatus: 'letzten Monat zeigen',
                nextText: 'vor&#x3e;', nextStatus: 'nächsten Monat zeigen',
                currentText: 'heute', currentStatus: '',
                monthNames: ['Januar','Februar','März','April','Mai','Juni',
                'Juli','August','September','Oktober','November','Dezember'],
                monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
                'Jul','Aug','Sep','Okt','Nov','Dez'],
                monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
                weekHeader: 'Wo', weekStatus: 'Woche des Monats',
                dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
                dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
                dateFormat: 'dd.mm.yy', firstDay: 1, 
                initStatus: 'Wähle ein Datum', isRTL: false};
});

var P2J = {
	make$: function(element){
		if(typeof element != "object" && element.charAt(0) != "#")
			element = "#"+element;
		
		return element;
	}
}

function $(something){
	var E = document.getElementById(something);
	
	//if(document.getElementById(something) && document.getElementById(something).style)
	//	E.style = document.getElementById(something).style;
	
	if(E)
		E.update = function(content){
			//alert(something);
			$j(document.getElementById(something)).html(content);
		}
	
	return E;
}

function $$(something){
	return $j(something);
}

function PeriodicalExecuter(callback, delayInSeconds) {
	window.setInterval(function(){
		callback();
	}, delayInSeconds * 1000);
}

var Ajax = {
	physion: "default",
	
	Request: function(anurl, options){
		$j.ajax({url: anurl+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""), success: function(transport){

			var t = {
				responseText: transport
			}
			options.onSuccess(t); 
		}, type: options.method ? options.method : "GET", data: options.parameters ? options.parameters : null});
	},
	
	Responders: {
		register: function(options){
			$j(document).ajaxStart(options.onCreate)
			$j(document).ajaxSuccess(options.onComplete)
			$j(document).ajaxError(options.onFailure)
		}
	},
	
	Updater: function(){
		alert("Ajax.Updater is no longer supported!");
	}
}

function Draggable(element, options) {
	if(typeof options == "undefined") options = {};

	$j(P2J.make$(element)).draggable({
		handle: options.handle ? $j(P2J.make$(options.handle)) : false
	});
}

var Effect = {
	Appear: function(element, options){
		if(options.to)
			$j(P2J.make$(element)).delay(options.delay ? options.delay * 1000 : 0).fadeTo(options.duration ? options.duration * 1000 : 400, options.to);
		else
			$j(P2J.make$(element)).fadeIn();
	},
	
	Fade: function(element, options){
		if(options.to){
			$j(P2J.make$(element)).delay(options.delay ? options.delay * 1000 : 0).fadeTo(options.duration ? options.duration * 1000 : 400, options.to);
		} else
			$j(P2J.make$(element)).fadeOut(options.duration ? options.duration * 1000 : 400);
	},
	
	BlindUp: function(element, options){
		if(typeof options == "undefined") options = {};
	
		$j(P2J.make$(element)).hide("blind", {direction: "vertical"}, options.duration ? options.duration * 1000 : 1000);
	},
	
	BlindDown: function(element, options){
		if(typeof options == "undefined") options = {};
	
		$j(P2J.make$(element)).show("blind", {direction: "vertical"}, options.duration ? options.duration * 1000 : 1000);
	},
	
	BlindToggle: function(element, options){
		if($(element).style.display == "none")
			new Effect.BlindDown(element, options);
		else
			new Effect.BlindUp(element, options);
			
	},
	
	Move: function(element, options){
		if(options.delay)
			$j(P2J.make$(element)).delay(options.delay * 1000).animate({"left": options.x}, {"duration" : options.duration * 1000});
		else
			$j(P2J.make$(element)).animate({"left": options.x}, {"duration" : options.duration * 1000});
	},

	SlideDown: function(element, options){
		$j(P2J.make$(element)).slideDown();
	},

	SlideUp: function(element, options){
		$j(P2J.make$(element)).slideUp();
	},
	
	Highlight: function (element, options){
		$j(P2J.make$(element)).effect("highlight", {}, 1000);
	}
}

var Sortable = {
	create: function(element, options){
		var cw = false;
		if(options && options.containment && typeof options.containment == 'string'){
			cw = options.containment;
		}
		
		if(options && options.containment && typeof options.containment != 'string'){
			for(i = 0; i < options.containment.length; i++){
				if(options.containment[i] == element)
					continue;
				
				cw = $j(P2J.make$(options.containment[i]))
			}
		}

		$j(P2J.make$(element)).sortable({
			axis: typeof options.constraint != "undefined" ? (options.constraint == "vertical" ? "y" : "x") : false, 
			placeholder: typeof options.placeholder != "undefined" ? options.placeholder : false, 
			change: options.onChange, 
			update: options.onUpdate,
			connectWith: cw,
			dropOnEmpty: true,
			handle: typeof options.handle != "undefined"  ? $j('.'+options.handle) : false});
	},
	
	serialize: function(element, options){
		if(typeof options == "undefined")
			options = {};
		
		var serial = $j(P2J.make$(element)).sortable('serialize', options);
		if(typeof serial != "string")
			serial = "";
		
		return serial.replace(/&/g,";").replace(/\[\]\=/g,"");
	}
}

var Event = {
	observe: function(element, action, call){
		if(action == "load")
			$j(P2J.make$(element)).ready(call);
		
		if(action == "mousemove")
			$j(P2J.make$(element)).mousemove(call);
		
		if(action == "click")
			$j(P2J.make$(element)).click(call);
		
		if(action == "mouseout")
			$j(P2J.make$(element)).mouseout(call);
		
		if(action == "resize")
			$j(P2J.make$(element)).resize(call);
		
	}
}

var Builder = {
	node: function(elementName, attributes, children){
		var E = document.createElement(elementName);

		$j(E).attr(attributes);
		
		if(typeof children != "undefined")
			for(i = 0; i < children.length; i++)
				$j(E).append(children[i]);
		
		return E;
	}
}

var qTipSharedRed = {
	position: {
		my: 'top right',
		at: 'bottom left',
		viewport: true,
		adjust: {
			method: 'flip'
		}
	},
	show: {
		event: false,
		ready: true,
		solo: true
	},
	hide: false,
	style: {
		classes: 'ui-tooltip-shadow ui-tooltip-red ui-tooltip-rounded ui-tooltip-text'
	}
}

$j('img[title]').live('mouseover', function(event) {
	$j(this).qtip({
		overwrite: false,
		show: {
			event: event.type,
			ready: true,
			delay: 1000
		},
		
		position: {
			viewport: true,
			adjust: {
				method: 'flip'
			}
		},
		
		style : {
			tip: true,
			classes: 'ui-tooltip-rounded ui-tooltip-shadow'
		}
		
	}, event);
}).each(function(i) {
   $j.attr(this, 'oldtitle', $j.attr(this, 'title'));
   this.removeAttribute('title');
   this.removeAttribute('alt');
});

$j(document).ready(function()
{
   /*$('button').click(function() {
      // Check if it should be persistent (can set to a normal bool if you like!)
      createGrowl( $(this).hasClass('persistent') );
   });*/
   
   window.createGrowl = function(message, persistent) {
	   persistent = false;
      // Use the last visible jGrowl qtip as our positioning target
      var target = $j('.qtip.jgrowl:visible:last');
 
      // Create your jGrowl qTip...
      $j(document.body).qtip({
         // Any content config you want here really.... go wild!
         content: {
            text: message/*,
            title: {
               text: 'Attention!',
               button: true
            }*/
         },
         position: {
			//container: $j('#growlContainer'),
            my: 'top left', // Not really important...
            at: (target.length ? 'bottom' : 'top') + ' left', // If target is window use 'top right' instead of 'bottom right'
            target: target.length ? target : $j(document.body), // Use our target declared above
            adjust: { y: 5 } // Add some vertical spacing
         },
         show: {
            event: false, // Don't show it on a regular event
            ready: true, // Show it when ready (rendered)
            effect: function() { $j(this).stop(0,1).fadeIn(400); }, // Matches the hide effect
            delay: 0, // Needed to prevent positioning issues
            
            // Custom option for use with the .get()/.set() API, awesome!
            persistent: persistent
         },
         hide: {
            event: false, // Don't hide it on a regular event
            effect: function(api) { 
               // Do a regular fadeOut, but add some spice!
               $j(this).stop(0,1).fadeOut(400).queue(function() {
                  // Destroy this tooltip after fading out
                  api.destroy();
 
                  // Update positions
                  updateGrowls();
               })
            }
         },
         style: {
            classes: 'jgrowl ui-tooltip-light ui-tooltip-rounded', // Some nice visual classes
            tip: false // No tips for this one (optional ofcourse)
         },
         events: {
            render: function(event, api) {
               // Trigger the timer (below) on render
               timer.call(api.elements.tooltip, event);
            }
         }
      })
      .removeData('qtip');
   };
 
   // Make it a window property see we can call it outside via updateGrowls() at any point
   window.updateGrowls = function() {
      // Loop over each jGrowl qTip
      var each = $j('.qtip.jgrowl:not(:animated)');
      each.each(function(i) {
         var api = $j(this).data('qtip');
 
         // Set the target option directly to prevent reposition() from being called twice.
         api.options.position.target = !i ? $j(document.body) : each.eq(i - 1);
         api.set('position.at', (!i ? 'top' : 'bottom') + ' left');
      });
   };
 
   // Setup our timer function
   function timer(event) {
      var api = $j(this).data('qtip'),
         lifespan = 3000; // 3 second lifespan
      
      // If persistent is set to true, don't do anything.
      if(api.get('show.persistent') === true) { return; }
 
      // Otherwise, start/clear the timer depending on event type
      clearTimeout(api.timer);
      if(event.type !== 'mouseover') {
         api.timer = setTimeout(api.hide, lifespan);
      }
   }
 
   // Utilise delegate so we don't have to rebind for every qTip!
   $j(document).delegate('.qtip.jgrowl', 'mouseover mouseout', timer);
});
