<?php
session_name("CP_".sha1(__FILE__));
define("PHYNX_NO_SESSION_RELOCATION", true);

require "../../system/connect.php";
$content = "";

#$_POET = $_GET;#array();
foreach($_GET AS $k => $v)
	$_POET[$k] = $v;

foreach($_POST AS $k => $v)
	if(!isset($_POET[$k]))
		$_POET[$k] = $v;

$pageTitle = "Customer Page";
if(isset($_POET["CC"])){
	$C = "CC".$_POET["CC"];
	registerClassPath($C, Util::getRootPath()."ubiquitous/CustomerPage/pages/$C.class.php");

	$I = new $C();
	
	if(isset($_POET["M"])){
		$M = $_POET["M"];
		
		unset($_POET["M"]);
		unset($_POET["CC"]);
		
		die($I->$M($_POET));
	}
	
	if(method_exists($I, "getTitle"))
		$pageTitle = $I->getTitle();
	
	$content = $I->getCMSHTML();
}

if(isset($_POET["D"])){
	$ex = explode("/", $_POET["D"]);
	
	$C = "CC".$ex[1];
	
	registerClassPath($C, Util::getRootPath()."$_POET[D]/$C.class.php");

	$I = new $C();
	
	if(isset($_POET["M"])){
		$M = $_POET["M"];
		
		unset($_POET["M"]);
		unset($_POET["CC"]);
		
		die($I->$M($_POET));
	}
	
	if(method_exists($I, "getTitle"))
		$pageTitle = $I->getTitle();
	
	$content = $I->getCMSHTML();
}

$styles = "";
$script = "";
$styleFiles = "";
$scriptFiles = "";
if(isset($I)){
	if(method_exists($I, "getStyle"))
		$styles = $I->getStyle();
	
	if(method_exists($I, "getScript"))
		$script = $I->getScript();
	
	if(method_exists($I, "getStyleFiles")){
		$styleFilesData = $I->getStyleFiles();
		foreach($styleFilesData AS $v)
			$styleFiles .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$v\" />";
	}
	
	if(method_exists($I, "getScriptFiles")){
		$scriptFilesData = $I->getScriptFiles();
		foreach($scriptFilesData AS $v)
			$scriptFiles .= "<script type=\"text/javascript\" src=\"$v\"></script>";
	}
}

?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" /> 
		<title><?php echo $pageTitle; ?></title>
		
		<link rel="stylesheet" type="text/css" href="./lib/jquery-ui-1.8.24.custom.css" />
		<link rel="stylesheet" type="text/css" href="./lib/default.css" />
		<style type="text/css">
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
			rme: function(method, parameters, onSuccessFunction, onFailureFunction, type){
				if(typeof type == "undefined")
					type = "GET";
				
				var ps = "";
				if(typeof parameters == "object"){
					for(var i = 0; i < parameters.length; i++)
						ps += "&P"+i+"="+parameters[i];
				}
				
				if(typeof parameters == "string")
					ps = "&"+parameters;
				
				
				$.ajax({url: "./index.php", success: function(transport){

					if(typeof onSuccessFunction == "function" && CustomerPage.checkResponse(transport))
						onSuccessFunction(transport);
					
				}, 
				data: "<?php if(isset($_POET["CC"])) echo "CC=".$_POET["CC"]; if(isset($_POET["D"])) echo "D=".$_POET["D"]; ?>&M="+method+ps,
				error: function(){
					if(typeof onFailureFunction == "function")
						onFailureFunction();
				}, type: type});
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
		
		<?php
			echo $styleFiles;
			echo $scriptFiles;
		?>
		
	</head>
		<body>
<?php
if(isset($_POET["CC"]) OR isset($_POET["D"]))
	echo $content;

?>
			<div id="popups"></div>
		</body>
</html>