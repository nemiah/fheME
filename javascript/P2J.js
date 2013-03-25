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

/*
* jQuery appear plugin
*
* Copyright (c) 2012 Andrey Sidorov
* licensed under MIT license.
*
* https://github.com/morr/jquery.appear/
*
* Version: 0.3.1
*/
(function($) {
	var selectors = [];

	var check_binded = false;
	var check_lock = false;
	var defaults = {
		interval: 250,
		force_process: false
	}
	var $window = $(window);

	var $prior_appeared;

	function process() {
		check_lock = false;
		for (var index in selectors) {
			var $appeared = $(selectors[index]).filter(function() {
				return $(this).is(':appeared');
			});

			$appeared.trigger('appear', [$appeared]);

			if ($prior_appeared) {
				var $disappeared = $prior_appeared.not($appeared);
				$disappeared.trigger('disappear', [$disappeared]);
			}
			$prior_appeared = $appeared;
		}
	}

// "appeared" custom filter
	$.expr[':']['appeared'] = function(element) {
		var $element = $(element);
		if (!$element.is(':visible')) {
			return false;
		}

		var window_left = $window.scrollLeft();
		var window_top = $window.scrollTop();
		var offset = $element.offset();
		var left = offset.left;
		var top = offset.top;

		if (top + $element.height() >= window_top &&
				top - ($element.data('appear-top-offset') || 0) <= window_top + $window.height() &&
				left + $element.width() >= window_left &&
				left - ($element.data('appear-left-offset') || 0) <= window_left + $window.width()) {
			return true;
		} else {
			return false;
		}
	}

	$.fn.extend({
		// watching for element's appearance in browser viewport
		appear: function(options) {
			var opts = $.extend({}, defaults, options || {});
			var selector = this.selector || this;
			if (!check_binded) {
				var on_check = function() {
					if (check_lock) {
						return;
					}
					check_lock = true;

					setTimeout(process, opts.interval);
				};

				$(window).scroll(on_check).resize(on_check);
				check_binded = true;
			}

			if (opts.force_process) {
				setTimeout(process, opts.interval);
			}
			selectors.push(selector);
			return $(selector);
		}
	});

	$.extend({
		// force elements's appearance check
		force_appear: function() {
			if (check_binded) {
				process();
				return true;
			}
			;
			return false;
		}
	});
})(jQuery);

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

String.prototype.makeHTML = function() {
	if(this.substr(0, 3) == '<p ' || this.substr(0, 3) == '<p>')
		return this;
	
	if(this == "")
		return "<p><br /></p>";
	
    return "<p>"+this.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2')+"</p>";
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
		$j.ajax({
			url: anurl+(Ajax.physion != "default" ? "&physion="+Ajax.physion : ""), success: function(transport){

				var t = {
					responseText: transport
				}
				options.onSuccess(t); 
			},
			type: options.method ? options.method : "GET",
			data: options.parameters ? options.parameters : null,
			cache : false
		});
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

var qTipSharedYellow = $j.extend({}, qTipSharedRed, {
	style: {
		classes: 'ui-tooltip-rounded ui-tooltip-shadow'
	}
})

	
/*$j("#container").hammer().on("touch dragdown release", function(){
	alert("touch dragdown release!");
});*/
	
var useTouch = $j.jStorage.get('phynxUseTouch', null);

if(Modernizr.touch && useTouch == null){
	$j(function(){
		$j("#messageTouch").dialog({
			modal: true,
			buttons: {
				"Ja": function() {
					$j.jStorage.set('phynxUseTouch', true);
					$j(this).dialog("close");
					document.location.reload(true);
				},
				"Nein": function() {
					$j.jStorage.set('phynxUseTouch', false);
					$j(this).dialog("close");
				}
			},
			resizable: false
		});
	});
}


var Touch = {
	hook: function(){
		var currentHTMLMethod = jQuery.fn.html;
		jQuery.fn.html = function(){
			currentHTMLMethod.apply(this, arguments);
			Touch.make();
		}
		
		var currentPrependMethod = jQuery.fn.prepend;
		jQuery.fn.prepend = function(){
			currentPrependMethod.apply(this, arguments);
			Touch.make();
		}
		
		var currentAppendMethod = jQuery.fn.append;
		jQuery.fn.append = function(){
			currentAppendMethod.apply(this, arguments);
			Touch.make();
		}
	},
			
	make: function(){
		$j("[onclick]").hammer().on("touch", function(){
			$j(this).addClass("highlight");
		}).on("release", function(){
			$j(this).removeClass("highlight");
		}).each(function(i, e){
			$j(this).attr("ontouchend", $j(this).attr("onclick")).removeAttr("onclick");//prop("ontouchend", $j(e).prop("onclick"))
		});
	}
}

if(useTouch){
	Touch.hook();
	
	$j(document).on("touchend", ".contentBrowser td", function(ev){
		$j(this).parent().removeClass("highlight");
		
		if(ev.target != this)
			return;

		if($j(ev.target).hasClass("editButton"))
			return;

		$j(this).parent().find("td").first().find(".editButton").triggerHandler("touchend");
	});

	$j(document).on("touchstart", ".contentBrowser td", function(ev){
		$j(this).parent().addClass("highlight");
	});
}

$j(function(){
	if(Modernizr.touch || useTouch != null){
		$j('#buttonTouchReset').click(function(){
			$j.jStorage.deleteKey('phynxUseTouch');
			//$j(this).dialog("close");
			document.location.reload(true);
			/*$j("#messageTouchReset").dialog({
				modal: true,
				buttons: {
					"Ja": function() {
						$j.jStorage.deleteKey('phynxUseTouch');
						$j(this).dialog("close");
						document.location.reload(true);
					},
					"Abbruch": function() {
						$j(this).dialog("close");
					}
				},
				resizable: false
			});*/

		});
	} else {
		$j('#buttonTouchReset').hide();
	}
});

if(!useTouch){
	$j(document).on('mouseover', 'img[title], span.iconic[title]', function(event) {
		if($j(this).attr('title') == "")
			return;

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
	})/*.each(function(i) {
	   $j.prop(this, 'oldtitle', $j.prop(this, 'title'));
	   this.removeAttribute('title');
	   this.removeAttribute('alt');
	})*/;


	$j(document).on("click", ".contentBrowser td", function(ev){
		if(ev.target != this)
			return;

		if($j(ev.target).hasClass("editButton"))
			return;

		$j(this).parent().find("td").first().find(".editButton").triggerHandler("click");
	});
}

$j(document).on('mouseover', '.bigButton', function(event) {
	if($j(document).width() > 1200)
		return;
		
	$j(this).qtip({
		overwrite: false,
		show: {
			event: event.type,
			ready: true,
			delay: 500
		},
		content: {
			text: function() {
				return $j(this).prop('value');
			}
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
})
