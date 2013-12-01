<?php
session_name("CP_".sha1(__FILE__));
define("PHYNX_NO_SESSION_RELOCATION", true);

require "../../system/connect.php";
$content = "";

$pageTitle = "Customer Page";
if(isset($_GET["CC"])){
	$C = "CC".$_GET["CC"];
	registerClassPath($C, Util::getRootPath()."ubiquitous/CustomerPage/pages/$C.class.php");

	$I = new $C();
	
	if(isset($_GET["M"])){
		$M = $_GET["M"];
		
		unset($_GET["M"]);
		unset($_GET["CC"]);
		
		die($I->$M($_GET));
	}
	
	if(method_exists($I, "getTitle"))
		$pageTitle = $I->getTitle();
	
	$content = $I->getCMSHTML();
	
	$styles = "";
	if(method_exists($I, "getStyle"))
		$styles = $I->getStyle();
	
	$script = "";
	if(method_exists($I, "getScript"))
		$script = $I->getScript();
	
	
}

if(isset($_GET["D"])){
	$ex = explode("/", $_GET["D"]);
	
	$C = "CC".$ex[1];
	
	registerClassPath($C, Util::getRootPath()."$_GET[D]/$C.class.php");

	$I = new $C();
	
	if(isset($_GET["M"])){
		$M = $_GET["M"];
		
		unset($_GET["M"]);
		unset($_GET["CC"]);
		
		die($I->$M($_GET));
	}
	
	if(method_exists($I, "getTitle"))
		$pageTitle = $I->getTitle();
	
	$content = $I->getCMSHTML();
	
	$styles = "";
	if(method_exists($I, "getStyle"))
		$styles = $I->getStyle();
	
	$script = "";
	if(method_exists($I, "getScript"))
		$script = $I->getScript();
}
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" /> 
		<title><?php echo $pageTitle; ?></title>
		
		<link rel="stylesheet" type="text/css" href="./lib/jquery-ui-1.8.24.custom.css" />
		<style type="text/css">
			* { margin:0px; }
			article, aside, details, figcaption, figure, footer, header, hgroup, nav, section { display: block; }
			audio, canvas, video { display: inline-block; *display: inline; *zoom: 1; }
			audio:not([controls]) { display: none; }
			[hidden] { display: none; }

			html { font-size: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
			body { margin: 10px; font-size: 13px; line-height: 1.231; }
			body, button, input, select, textarea { font-family: sans-serif; color: #222; }

			::-moz-selection { background: #fe57a1; color: #fff; text-shadow: none; }
			::selection { background: #fe57a1; color: #fff; text-shadow: none; }

			a { color: black; }
			a:visited { color: black; }
			a:hover { color: grey; }
			a:focus { outline: thin dotted; }
			a:hover, a:active { outline: 0; }

			abbr[title] { border-bottom: 1px dotted; }
			b, strong { font-weight: bold; }
			blockquote { margin: 1em 40px; }
			dfn { font-style: italic; }
			hr { display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0; }
			ins { background: #ff9; color: #000; text-decoration: none; }
			mark { background: #ff0; color: #000; font-style: italic; font-weight: bold; }
			pre, code, kbd, samp { font-family: monospace, monospace; _font-family: 'courier new', monospace; font-size: 1em; }
			pre { white-space: pre; white-space: pre-wrap; word-wrap: break-word; }
			q { quotes: none; }
			q:before, q:after { content: ""; content: none; }
			small { font-size: 85%; }
			sub, sup { font-size: 75%; line-height: 0; position: relative; vertical-align: baseline; }
			sup { top: -0.5em; }
			sub { bottom: -0.25em; }
			ul, ol { margin: 1em 0; padding: 0 0 0 40px; }
			dd { margin: 0 0 0 40px; }
			nav ul, nav ol { list-style: none; list-style-image: none; margin: 0; padding: 0; }
			img { border: 0; -ms-interpolation-mode: bicubic; vertical-align: middle; }
			svg:not(:root) { overflow: hidden; }
			figure { margin: 0; }

			form { margin: 0; }
			fieldset { border: 0; margin: 0; padding: 0; }
			label { cursor: pointer; }
			legend { border: 0; *margin-left: -7px; padding: 0; }
			button, input, select, textarea { font-size: 100%; margin: 0; vertical-align: baseline; *vertical-align: middle; }
			button, input { line-height: normal; *overflow: visible; }
			table button, table input { *overflow: auto; }
			button, input[type="button"], input[type="reset"], input[type="submit"] { cursor: pointer; -webkit-appearance: button; }
			input[type="checkbox"], input[type="radio"] { box-sizing: border-box; }
			input[type="search"] { -webkit-appearance: textfield; -moz-box-sizing: content-box; -webkit-box-sizing: content-box; box-sizing: content-box; }
			input[type="search"]::-webkit-search-decoration { -webkit-appearance: none; }
			button::-moz-focus-inner, input::-moz-focus-inner { border: 0; padding: 0; }
			textarea { overflow: auto; vertical-align: top; resize: vertical; }
			input:valid, textarea:valid {  }
			input:invalid, textarea:invalid { background-color: #f0dddd; }

			table { border-collapse: collapse; border-spacing: 0; }
			td { /*vertical-align: top; */padding:3px; }
			
			h1 { font-family:Roboto; padding-top:30px;padding-bottom:15px; }
			h2 { font-family:Roboto; padding-top:20px;padding-bottom:10px; }
			
			label { text-align:right; width:120px; display:block; font-weight:bold; }
			.submitFormButton { float:right; padding:4px; width:auto; padding-left:10px; padding-right:10px; }
			
			input, select, textarea { border:0px; border-bottom:1px solid grey; padding:2px; border-color: #97a652;background-color:#EFEFEF; width:250px; }
			
			.borderColor1 { border-color: #97a652; }
			.backgroundColor1 { background-color: #F5FFC5; }
			
			.tableForSelection tr {
				cursor:pointer;
			}
			
			.tableForSelection tr:hover {
				background-color: #F5FFC5;
			}
			
			form table { width:370px; }
			form table td { width:120px; }
			form table td+td { width:250px; }
			form { border:1px dashed #BBB; padding:10px; width:390px; }
			p { padding:10px; }
			
			input[disabled="disabled"]{
				color:grey;
			}
			
			input.error {
				border-left:1px dashed red;
			}
			
			label.error {
				font-weight:normal;
				text-align:left;
				width:100%;
				font-size:10px;
				color:red;
				margin-top:3px;
			}
			
			.submitFormButton {
				border: medium none;
				cursor: pointer;
				display: inline-block;
				font-size: 18px;
				height: 31px;
				line-height: 32px;
				padding: 0 20px;
				margin-top:10px;
				text-transform: uppercase;
			}
			.submitFormButton:active {
				box-shadow: 0 0 0 transparent, 0 0 0 black;
			}
			
			.submitFormButton {
				background-color: #cbe749;
				box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 0 0 0 black;
				color: #566708;
				text-shadow: 0 1px 0 rgba(255, 255, 255, 0.25);
			}
			.submitFormButton:focus, .submitFormButton:hover {
				background-color: #aacc00;
				color: #374301;
			}
			
			.Tab {
				margin-top:10px;
				margin-bottom:5px;
			}
			
			th {
				color:grey;
			}
			
			tr.selectable {
				cursor:pointer;
			}
			
			tr.selectable:hover {
				background-color: #F5FFC5;/*backgroundColor1*/
			}
			
			/*.green-button.is-submitting {
				background: url("/media/i/throbber16_green.27bc94e79fb2.gif") no-repeat scroll 10px center #478F81;
				color: #38766C;
				cursor: default;
				padding-left: 37px;
			}*/
			
			@font-face {
			  font-family: 'Roboto';
			  font-style: normal;
			  font-weight: 300;
			  src: local('Roboto Light'), local('Roboto-Light'), url('../../../libraries/roboto/Roboto-Light.woff') format('woff'), url('../../../libraries/roboto/Roboto-Light.ttf') format('truetype');
			}
			
			@font-face { 
				font-family: 'IconicStroke'; 
				src: url('./lib/iconic_stroke.eot'); 
				src: url('./lib/iconic_stroke.eot?#iefix') format('embedded-opentype'), url('./lib/iconic_stroke.ttf') format('truetype');
				font-weight: normal;
				font-style: normal; }

			.iconic {
				display:inline-block;
				font-family: 'IconicStroke';
				font-size:20px;
				text-align: center;
				margin:auto;
				vertical-align:middle;
				border-color:white;
				border-style:solid;
				border-width:0px;
				width:20px;


				-moz-transition: color .3s, border-color .3s;
				-webkit-transition: color .3s, border-color .3s;
				-o-transition: color .3s, border-color .3s;
				transition: color .3s, border-color .3s;
			}

			.iconic.inactive {
				cursor:default;
			}

			.iconic[onclick] {
				cursor:pointer;
			}

			.iconic:hover {	
				opacity:0.5;
			}
			
			.iconic.reverse {
				color:white;
			}

			tr:hover .iconic.reverse {
				color:inherit;
			}
			
			.lightbulb:before {content:'\e063';}
			.equalizer:before {content:'\e052';}
			.map_pin_stroke:before {content:'\e068';}
			.brush_alt:before {content:'\e01c';}
			.move:before {content:'\e03e';}
			.paperclip:before {content:'\e08a';}
			.pen_alt_stroke:before {content:'\e005';}
			.move_vertical:before {content:'\e03b';}
			.book_alt2:before {content:'\e06a';}
			.layers:before {content:'\e01f';}
			.pause:before {content:'\e049';}
			.layers_alt:before {content:'\e020';}
			.cloud_upload:before {content:'\e045';}
			.chart_alt:before {content:'\e029';}
			.fullscreen_exit_alt:before {content:'\e051';}
			.cloud_download:before {content:'\e044';}
			.comment_alt2_stroke:before {content:'\e004';}
			.mail:before {content:'\2709';}
			.check_alt:before {content:'\2714';}
			.document_stroke:before {content:'\e066';}
			.battery_charging:before {content:'\e05d';}
			.stop:before {content:'\e04a';}
			.arrow_up:before {content:'\2191';}
			.move_horizontal:before {content:'\e038';}
			.compass:before {content:'\e021';}
			.minus_alt:before {content:'\e009';}
			.battery_empty:before {content:'\e05c';}
			.map_pin_alt:before {content:'\e002';}
			.unlock_stroke:before {content:'\e076';}
			.lock_stroke:before {content:'\e075';}
			.question_mark:before {content:'\003f';}
			.list:before {content:'\e055';}
			.upload:before {content:'\e043';}
			.reload:before {content:'\e030';}
			.loop_alt4:before {content:'\e035';}
			.loop_alt3:before {content:'\e034';}
			.loop_alt2:before {content:'\e033';}
			.loop_alt1:before {content:'\e032';}
			.left_quote:before {content:'\275d';}
			.x:before {content:'\2717';}
			.last:before {content:'\e04d';}
			.document_alt_stroke:before {content:'\e000';}
			.bars:before {content:'\e06f';}
			.arrow_left:before {content:'\2190';}
			.arrow_down:before {content:'\2193';}
			.download:before {content:'\e042';}
			.home:before {content:'\2302';}
			.calendar:before {content:'\e001';}
			.right_quote_alt:before {content:'\e012';}
			.fullscreen:before {content:'\e04e';}
			.dial:before {content:'\e058';}
			.plus_alt:before {content:'\e008';}
			.clock:before {content:'\e079';}
			.movie:before {content:'\e060';}
			.steering_wheel:before {content:'\e024';}
			.pen:before {content:'\270e';}
			.tag_stroke:before {content:'\e02b';}
			.pin:before {content:'\e067';}
			.denied:before {content:'\26d4';}
			.left_quote_alt:before {content:'\e011';}
			.volume_mute:before {content:'\e071';}
			.arrow_up_alt2:before {content:'\e018';}
			.list_nested:before {content:'\e056';}
			.arrow_up_alt1:before {content:'\e014';}
			.comment_stroke:before {content:'\e06d';}
			.undo:before {content:'\e02f';}
			.umbrella:before {content:'\2602';}
			.bolt:before {content:'\26a1';}
			.article:before {content:'\e053';}
			.read_more:before {content:'\e054';}
			.beaker:before {content:'\e023';}
			.beaker_alt:before {content:'\e010';}
			.battery_full:before {content:'\e073';}
			.arrow_right:before {content:'\2192';}
			.new_window:before {content:'\e059';}
			.plus:before {content:'\2795';}
			.cog:before {content:'\2699';}
			.key_stroke:before {content:'\26bf';}
			.first:before {content:'\e04c';}
			.comment_alt1_stroke:before {content:'\e003';}
			.trash_stroke:before {content:'\e05a';}
			.image:before {content:'\e027';}
			.chat_alt_stroke:before {content:'\e007';}
			.cd:before {content:'\e064';}
			.right_quote:before {content:'\275e';}
			.brush:before {content:'\e01b';}
			.cloud:before {content:'\2601';}
			.eye:before {content:'\e025';}
			.play_alt:before {content:'\e048';}
			.transfer:before {content:'\e041';}
			.pen_alt2:before {content:'\e006';}
			.camera:before {content:'\e070';}
			.move_horizontal_alt2:before {content:'\e03a';}
			.curved_arrow:before {content:'\2935';}
			.move_horizontal_alt1:before {content:'\e039';}
			.aperture:before {content:'\e026';}
			.reload_alt:before {content:'\e031';}
			.magnifying_glass:before {content:'\e074';}
			.iphone:before {content:'\e06e';}
			.fork:before {content:'\e046';}
			.box:before {content:'\e06b';}
			.bars_alt:before {content:'\e00a';}
			.heart_stroke:before {content:'\2764';}
			.volume:before {content:'\e072';}
			.x_alt:before {content:'\2718';}
			.link:before {content:'\e077';}
			.moon_stroke:before {content:'\263e';}
			.eyedropper:before {content:'\e01e';}
			.spin:before {content:'\e036';}
			.rss:before {content:'\e02c';}
			.info:before {content:'\2139';}
			.target:before {content:'\e02a';}
			.cursor:before {content:'\e057';}
			.minus:before {content:'\2796';}
			.book_alt:before {content:'\e00b';}
			.headphones:before {content:'\e061';}
			.hash:before {content:'\0023';}
			.arrow_left_alt1:before {content:'\e013';}
			.arrow_left_alt2:before {content:'\e017';}
			.fullscreen_exit:before {content:'\e050';}
			.share:before {content:'\e02e';}
			.fullscreen_alt:before {content:'\e04f';}
			.at:before {content:'\0040';}
			.chat:before {content:'\e05e';}
			.move_vertical_alt2:before {content:'\e03d';}
			.move_vertical_alt1:before {content:'\e03c';}
			.check:before {content:'\2713';}
			.mic:before {content:'\e05f';}
			.calendar_alt_stroke:before {content:'\e06c';}
			.book:before {content:'\e069';}
			.move_alt1:before {content:'\e03f';}
			.move_alt2:before {content:'\e040';}
			.award_stroke:before {content:'\e022';}
			.wrench:before {content:'\e078';}
			.play:before {content:'\e047';}
			.star:before {content:'\2605';}
			.chart:before {content:'\e028';}
			.rain:before {content:'\26c6';}
			.folder_stroke:before {content:'\e065';}
			.sun_stroke:before {content:'\2600';}
			.user:before {content:'\e062';}
			.battery_half:before {content:'\e05b';}
			.aperture_alt:before {content:'\e00c';}
			.eject:before {content:'\e04b';}
			.arrow_down_alt1:before {content:'\e016';}
			.pilcrow:before {content:'\00b6';}
			.arrow_down_alt2:before {content:'\e01a';}
			.arrow_right_alt1:before {content:'\e015';}
			.arrow_right_alt2:before {content:'\e019';}
			.rss_alt:before {content:'\e02d';}
			.spin_alt:before {content:'\e037';}

			::-webkit-input-placeholder {
				color:#999;
			}
			:-moz-placeholder {
				color:#999;
			}
			:-ms-input-placeholder {
				color:#999;
			}
			
			.ui-autocomplete.ui-autocomplete,
			.ui-autocomplete .ui-corner-all,
			.ui-datepicker.ui-corner-all,
			.ui-dialog.ui-corner-all,
			.ui-datepicker-inline.ui-corner-all,
			.ui-datepicker-header.ui-corner-all,
			.ui-dialog-titlebar.ui-corner-all
			{
				border-radius:0px;
			}

			.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
				background:none;
			}

			.ui-widget-header {
				background:none;
				background-color:#EEE;
			}

			.ui-datepicker-header, .ui-dialog-titlebar {
				border:0px;
			}

			.ui-widget-overlay {
				opacity:0.5;
			}
			
			.ui-dialog form {
				width:95%;
			}
			
			.ui-dialog form table {
				width:100%;
			}
			
			.ui-dialog form table td {
				text-align:right;
			}
			
			.ui-dialog form table td + td {
				width:100%;
				text-align:left;
			}
			
			.highlight {
				background-color:rgba(255,204,0,0.3);
			}
			
			.overlay {
				position:absolute;
				top:0;
				left:0;
				right:0;
				bottom:0;
				background-color:white;
				z-index:9999;
			}
			<?php
			echo $styles;
			?>
		</style>
		
		<script type="text/javascript" src="./lib/jquery-1.8.2.min.js"></script>
		<script type="text/javascript" src="./lib/jquery-ui-1.8.24.custom.min.js"></script>
		<script type="text/javascript" src="./lib/jquery.validate.min.js"></script>
		<script type="text/javascript" src="./lib/jstorage.min.js"></script>
		<script type="text/javascript" src="./lib/noty/jquery.noty.js"></script>
		<script type="text/javascript" src="./lib/noty/topLeft.js"></script>
		<script type="text/javascript" src="./lib/noty/default.js"></script>
		<script type="text/javascript" src="./lib/jquery.hammer.min.js"></script>
		
		<script type="text/javascript">
		$.noty.defaults = {
			layout: 'topLeft',
			theme: 'defaultTheme',
			type: 'alert',
			text: '',
			dismissQueue: true, // If you want to use queue feature set this true
			template: '<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',
			animation: {
				open: {height: 'toggle'},
				close: {height: 'toggle'},
				easing: 'swing',
				speed: 200 // opening & closing animation speed
			},
			timeout: 2000, // delay for closing event. Set false for sticky notifications
			force: false, // adds notification to the beginning of queue when set to true
			modal: false,
			closeWith: ['click'], // ['click', 'button', 'hover']
			callback: {
				onShow: function() {
				},
				afterShow: function() {
				},
				onClose: function() {
				},
				afterClose: function() {
				}
			},
			buttons: false // an array of buttons
		};
		
		var CustomerPage = {
			rme: function(method, parameters, onSuccessFunction, onFailureFunction){
				
				var ps = "";
				if(typeof parameters == "object"){
					for(var i = 0; i < parameters.length; i++)
						ps += "&P"+i+"="+parameters[i];
				}
				
				if(typeof parameters == "string")
					ps = "&"+parameters;
				
				
				$.ajax({url: "./index.php?<?php if(isset($_GET["CC"])) echo "CC=".$_GET["CC"]; if(isset($_GET["D"])) echo "D=".$_GET["D"]; ?>&M="+method+ps, success: function(transport){

					if(typeof onSuccessFunction == "function" && CustomerPage.checkResponse(transport))
						onSuccessFunction(transport);
					
				}, error: function(){
					if(typeof onFailureFunction == "function")
						onFailureFunction();
				}, type: "GET"});
			},
	
			popupCounter: 1,
			popup: function(title, method, parameters, options, reuseName){
				
				if(typeof options == "undefined")
					options = {};
				
				if(typeof options.close == "undefined" && typeof reuseName == "undefined")
					options.close = function(){
						$(this).dialog('destroy').remove();
					}
				
				CustomerPage.rme(method, parameters, function(t){
					if(typeof reuseName == "undefined"){
						$('#popups').append("<div title=\""+title+"\" style=\"display:none;\" id=\"popup"+CustomerPage.popupCounter+"\">"+t+"</div>");
						$("#popup"+CustomerPage.popupCounter).dialog(options);
						CustomerPage.popupCounter++;
					} else {
						if($("#popup"+reuseName).length == 0)
							$('#popups').append("<div title=\""+title+"\" style=\"display:none;\" id=\"popup"+reuseName+"\"></div>");
						
						$("#popup"+reuseName).html(t);
						$("#popup"+reuseName).dialog(options);
					}
					
				});
			},
			
			closePopup: function(){
				//console.log($("#popup"+CustomerPage.popupCounter - 1));
				$("#popup"+(CustomerPage.popupCounter - 1)).dialog("close");
			},
	
			checkResponse: function(transport) {
				if(transport.search(/^error:/) > -1){
					eval("var message = "+transport.replace(/error:/,"")+";");
					//alert("Es ist ein Fehler aufgetreten:\n"+message);
					noty({text: message, type: 'error'});
					return false;
				}
				if(transport.search(/^alert:/) > -1){
					eval("var message = "+transport.replace(/alert:/,"")+";");
					noty({text: message, type: 'warning'});
					//alert(message);
					return false;
				}
				if(transport.search(/^message:/) > -1){
					eval("var message = "+transport.replace(/message:/,"")+";");
					noty({text: message, type: 'success'});
					//alert(message);
					return true;
				}
				if(transport.search(/^reload/) > -1){
					document.location.reload();
				}
				if(transport.search(/Fatal error/) > -1){
					alert(transport.replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, ''));
					return false;
				}
				return true;
			},
			
			toggleFormFields: function(mode, fields, formID){
				if(typeof formID == "undefined")
					formID = "";
				else
					formID = "#"+formID+" ";
				
				if(mode == "hide")
					for (var f = 0; f < fields.length; f++) 
						$j(formID+'select[name='+fields[f]+'],'+formID+'input[name='+fields[f]+'],'+formID+'textarea[name='+fields[f]+']').parent().parent().css("display", "none");
					
				if(mode == "show")
					for (var f = 0; f < fields.length; f++) 
						$j(formID+'select[name='+fields[f]+'],'+formID+'input[name='+fields[f]+'],'+formID+'textarea[name='+fields[f]+']').parent().parent().css("display", "");

			}
		}
		
		contentManager = CustomerPage;
		
		$j = $;
		
			<?php echo $script; ?>
		</script>
		
		
	</head>
		<body>
<?php
if(isset($_GET["CC"]) OR isset($_GET["D"]))
	echo $content;

?>
			<div id="popups"></div>
		</body>
</html>