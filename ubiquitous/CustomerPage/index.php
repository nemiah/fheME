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
}
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" /> 
		<title><?php echo $pageTitle; ?></title>
		<style type="text/css">
			* { margin:0px; }
			article, aside, details, figcaption, figure, footer, header, hgroup, nav, section { display: block; }
			audio, canvas, video { display: inline-block; *display: inline; *zoom: 1; }
			audio:not([controls]) { display: none; }
			[hidden] { display: none; }

			html { font-size: 100%; overflow-y: scroll; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
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
			td { vertical-align: top; padding:3px; }
			
			h1 { padding-bottom:20px; }
			
			label { text-align:right; width:120px; display:block; font-weight:bold; }
			.submitFormButton { float:right; padding:4px; width:auto; padding-left:10px; padding-right:10px; }
			
			input, select { border:0px; border-bottom:1px solid grey; padding:2px; border-color: #97a652;background-color:#EFEFEF; width:250px; }
			
			.borderColor1 { border-color: #97a652; }
			.backgroundColor1 { background-color: #F5FFC5; }
			
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
			
			/*.green-button.is-submitting {
				background: url("/media/i/throbber16_green.27bc94e79fb2.gif") no-repeat scroll 10px center #478F81;
				color: #38766C;
				cursor: default;
				padding-left: 37px;
			}*/
		</style>
		
		<script type="text/javascript" src="./lib/jquery-1.6.1.min.js"></script>
		<script type="text/javascript" src="./lib/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="./lib/jquery.validate.min.js"></script>
		<script type="text/javascript" src="./lib/jstorage.min.js"></script>
		
		<script type="text/javascript">
		var CustomerPage = {
			rme: function(method, parameters, onSuccessFunction){
				
				var ps = "";
				if(typeof parameters == "object"){
					for(var i = 0; i < parameters.length; i++)
						ps += "&P"+i+"="+parameters[i];
				}
				
				if(typeof parameters == "string")
					ps = "&"+parameters;
				
				
				$.ajax({url: "./index.php?CC=<?php echo $_GET["CC"]; ?>&M="+method+ps, success: function(transport){

					if(typeof onSuccessFunction == "function" && CustomerPage.checkResponse(transport))
						onSuccessFunction(transport);
					
				}, type: "GET"});
			},
	
			checkResponse: function(transport) {
				if(transport.search(/^error:/) > -1){
					eval("var message = "+transport.replace(/error:/,"")+";");
					alert("Es ist ein Fehler aufgetreten:\n"+message);
					return false;
				}
				if(transport.search(/^alert:/) > -1){
					eval("var message = "+transport.replace(/alert:/,"")+";");
					alert(message);
					return false;
				}
				if(transport.search(/^message:/) > -1){
					eval("var message = "+transport.replace(/message:/,"")+";");
					alert(message);
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
		</script>
		
		
	</head>
		<body>
<?php
if(isset($_GET["CC"]))
	echo $content;

?>
		</body>
</html>