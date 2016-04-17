<?php 
/**
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

if(!function_exists("hex2bin")){
	require_once "./system/basics.php";
	
	emoFatalError("I'm sorry, but your PHP version is too old.", "You need at least PHP version 5.4.0 to run this program.<br />You are using ".phpversion().". Please talk to your provider about this.", "phynx");
}

$dir = new DirectoryIterator(dirname(__FILE__));
$notExecutable = array();
foreach ($dir as $file) {
	if($file->isDot()) continue;
	if(!$file->isDir()) continue;

	if($file->getFilename() == "logs")
		continue;
	
	if($file->isExecutable()) continue;
	$notExecutable[] = $file->getFilename();
}

#if(count($notExecutable) > 0 AND !is_executable("./system") AND stripos(getenv("OS"), "Windows") === false)
#	die("The directory <i>system</i> is not marked executable.<br />Please resolve this issue by running the following command inside the installation directory:<br /><code>chmod u=rwX,g=rX,o=rX system</code>");

if(count($notExecutable) > 0 AND is_executable("./system")){
	require_once "./system/basics.php";

	emoFatalError(
		"I'm sorry, but I'm unable to access some directories",
		"Please make sure that the webserver is able to access these directories and its subdirectories:<br /><br />".implode("<br />", $notExecutable)."<br /><br />Usually a good plan to achieve this, is to run the following<br />commands in the installation directory:<br /><code>chmod -R u=rw,g=r,o=r *<br />chmod -R u=rwX,g=rX,o=rX *</code>",
		"phynx");
}

/*$texts = array();
$texts["it_IT"] = array();
$texts["it_IT"]["username"] = "Username";
$texts["it_IT"]["password"] = "Password";
$texts["it_IT"]["application"] = "Applicazione";
$texts["it_IT"]["login"] = "accesso";
$texts["it_IT"]["autologin"] = "accesso automatico";
$texts["it_IT"]["save"] = "memorizzare i dati";
$texts["it_IT"]["sprache"] = "Lingua";
$texts["it_IT"]["optionsImage"] = "Visualizzare le opzioni";
$texts["it_IT"]["lostPassword"] = "Password persa?";*/

require "./system/connect.php";
#$browserLang = Session::getLanguage();
T::load(Util::getRootPath()."libraries");
/*
$E = new Environment();
*/
$cssColorsDir = Environment::getS("cssColorsDir", (isset($_COOKIE["phynx_color"]) ? $_COOKIE["phynx_color"] : "standard"));
$cssCustomFiles = Environment::getS("cssCustomFiles", null);
/*
if(file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php")){
	require_once Util::getRootPath()."plugins/Cloud/Cloud.class.php";
	require_once Util::getRootPath()."plugins/Cloud/mCloud.class.php";

	$E = mCloud::getEnvironment();
}*/

$build = rand(1, 9999999);
if(Phynx::build()){
	#$xml = new SimpleXMLElement(file_get_contents(Util::getRootPath()."system/build.xml"));
	
	if(isset($_COOKIE["phynx_lastSeenBuild"]) AND $_COOKIE["phynx_lastSeenBuild"] != Phynx::build()){
		$isCloud = file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php");
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		setcookie("phynx_lastSeenBuild", Phynx::build(), time() + 3600 * 24 * 365);
		//header("location: ".  basename(__FILE__));
		
		$button = "
			<div
				onclick=\"document.location.reload(true);\"
				onmouseover=\"this.style.backgroundColor = '#d7eac5';\"
				onmouseout=\"this.style.backgroundColor = 'transparent';\"
				style=\"width:120px;padding:10px;border:1px solid green;border-radius:5px;box-shadow:2px 2px 4px grey;margin-top:20px;cursor:pointer;font-weight:bold;\">
				
				<img src=\"./images/navi/navigation.png\" style=\"float:left;margin-top:-8px;margin-right:10px;\" />Weiter
			</div>";
		
		emoFatalError(T::_("Diese Anwendung wurde aktualisiert"), "Der Administrator dieser Anwendung hat seit Ihrem letzten Besuch eine Aktualisierung eingespielt.</p>
			".(!$isCloud ? "<p style=\"margin-left:80px;\">Bitte entscheiden Sie sich nun für eine der beiden Möglichkeiten,<br />abhängig davon, ob Sie diese Anwendung eingerichtet haben, oder eine andere Person:</p>" : "")."
			<div style=\"width:800px;\">
				".(!$isCloud ? "<div style=\"width:350px;float:right;\">
					<h2>Administrator</h2>
					<p>Wenn Sie diese Anwendung eingerichtet haben und das Admin-Passwort kennen, gehen Sie wie folgt vor, um die Aktualisierung abzuschließen:</p><ol><li>Melden Sie sich mit dem <strong>Admin-Benutzer</strong> am System an.</li><li>Aktualisieren Sie im <strong>Installation-Plugin</strong> die Tabellen mit dem Knopf <strong>\"alle Tabellen aktualisieren\"</strong>.</li><ol>
					$button
				</div>" : "")."
				<div style=\"width:350px;\">
					<h2 style=\"clear:none;\">Benutzer</h2>
					<p>Wenn Sie ein Benutzer dieser Anwendung sind und sie nicht selbst eingerichtet haben, initialisiert sich das System nach einem Klick auf den nachfolgenden Knopf neu und Sie können normal weiterarbeiten.</p>
					$button
				</div>
			<div style=\"clear:both;\"></div>
			</div>", T::_("Diese Anwendung wurde aktualisiert"), false, "ok");
	} elseif(!isset($_COOKIE["phynx_lastSeenBuild"]))
		setcookie("phynx_lastSeenBuild", Phynx::build(), time() + 3600 * 24 * 365);
	
	$build = Phynx::build();
}

$validUntil = Environment::getS("validUntil", null);

if($_SESSION["S"]->checkIfUserLoggedIn() == false) $_SESSION["CurrentAppPlugins"]->scanPlugins();

$updateTitle = true;
$title = Environment::getS("renameFramework", "phynx by Furtmeier Hard- und Software");
if(isset($_GET["title"]) AND preg_match("/[a-zA-Z0-9 _-]*/", $_GET["title"])){
	$title = $_GET["title"];
	$updateTitle = false;
}
$favico = "./images/FHSFavicon.ico";
$sephy = Session::physion();
if($sephy AND isset($sephy[3]) AND $sephy[3])
		$favico = $sephy[3];

?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<title><?php echo $title ?></title>

		<link rel="shortcut icon" href="<?php echo $favico ?>" /> 
		
		<script type="text/javascript">
			window.paceOptions = {
				ajax: {
					trackMethods: ['POST'],
					trackWebSockets: false,
					ignoreURLs: []
				},
				elements: false//,
				//document: false,
				//eventLag: false,
				
				//restartOnRequestAfter: 500,
				//restartOnPushState: true
			};
		</script>
		<script type="text/javascript" src="./libraries/pace/pace.min.js"></script>
		<link href="./libraries/pace/pace-theme-minimal.css" rel="stylesheet" />
		
		<script type="text/javascript" src="./libraries/jquery/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery-ui-1.10.1.custom.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.json-2.3.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.timers.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.qtip.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.scrollTo-1.4.2-min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.hammer.js"></script>
		<script type="text/javascript" src="./libraries/jstorage.js"></script>
		<script type="text/javascript" src="./libraries/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="./libraries/webtoolkit.sha1.js"></script>
		<script type="text/javascript" src="./libraries/flot/jquery.flot.js"></script>
		<script type="text/javascript" src="./libraries/flot/jquery.flot.time.js" async></script>
		<script type="text/javascript" src="./libraries/flot/jquery.flot.threshold.js" async></script>
		<script type="text/javascript" src="./libraries/flot/jquery.flot.pie.js" async></script>
		<script type="text/javascript" src="./libraries/flot/jquery.flot.selection.js" async></script>
		<script type="text/javascript" src="./libraries/flot/jquery.flot.orderBars.js" async></script>
		<!--<script type="text/javascript" src="./libraries/nicEdit/nicEdit.js"></script>-->
		<script type="text/javascript" src="./libraries/modernizr.custom.js"></script>
		<script type="text/javascript" src="./libraries/snap.svg/snap.svg-min.js"></script>
		<script type="text/javascript" src="./libraries/touchy/Touchy.js"></script>
		<script type="text/javascript" src="./libraries/iconic/iconic.min.js"></script>
		<script type="text/javascript" src="./libraries/tinymce/tinymce.min.js?r=<?php echo $build; ?>" async></script>
		<script type="text/javascript" src="./libraries/tinymce/jquery.tinymce.min.js?r=<?php echo $build; ?>" async></script>
		
		
		<script type="text/javascript" src="./javascript/P2J.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Registry.js?r=<?php echo $build; ?>"></script>

		<script type="text/javascript" src="./javascript/Aspect.js?r=<?php echo $build; ?>"></script>
		<!--<script type="text/javascript" src="./javascript/Observer.js?r=<?php echo $build; ?>"></script>-->
		<script type="text/javascript" src="./javascript/Overlay.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Menu.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/autoComplete.js?r=<?php echo $build; ?>" async></script>
		<script type="text/javascript" src="./javascript/phynxContextMenu.js?r=<?php echo $build; ?>" async></script>
		<script type="text/javascript" src="./javascript/userControl.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Interface.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Popup.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/contentManager.js?r=<?php echo $build; ?>"></script>
		<!--<script type="text/javascript" src="./javascript/DesktopLink.js?r=<?php echo $build; ?>"></script>-->
		<script type="text/javascript" src="./javascript/notificationArea.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/handler.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Util.js?r=<?php echo $build; ?>"></script>

		<script type="text/javascript" src="./libraries/TextEditor.js?r=<?php echo $build; ?>" async></script>
		<script type="text/javascript" src="./libraries/fileuploader.js?r=<?php echo $build; ?>" async></script>

		<script type="text/javascript">
			if(typeof contentManager == "undefined")
				alert("Die JavaScript-Dateien konnten nicht geladen werden.\nDies kann an der Server-Konfiguration liegen.\nBitte versuchen Sie, diese Anwendung in ein Unterverzeichnis zu installieren.");
		</script>
		<script>
			/*window.define = function(factory) {
				try{ delete window.define; } catch(e){ window.define = void 0; } // IE
				window.when = factory();
			};
			window.define.amd = {};*/
			<?php
			if(!Environment::getS("usePWEncryption", true))
				echo "\$j(function(){
					userControl.usePWEncryption = false;
					\$j('#loginPWEncrypted').val('0');
				});";
			?>
		</script>
		<?php if(!file_exists(dirname(__FILE__)."/styles/standard/merge.css")){ ?>
		
		<link rel="stylesheet" type="text/css" href="./libraries/jquery/jquery-ui-1.10.1.custom.css" />
		<link rel="stylesheet" type="text/css" href="./libraries/jquery/jquery.qtip.min.css" />
		<link rel="stylesheet" type="text/css" href="./libraries/touchy/Touchy.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/overlayBox.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/frames.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/general.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/navigation.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/autoCompletion.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/phynxContextMenu.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/TextEditor.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/colors.css" />
		<?php
		} else
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"./styles/standard/merge.css\" />";
		
		if($cssColorsDir != "standard") 
			echo '
		<link rel="stylesheet" type="text/css" href="./styles/'.$cssColorsDir.'/colors.css" />';
		
		if($cssCustomFiles != null){
			foreach (explode("\n", $cssCustomFiles) AS $cssFile)
				echo '
		<link rel="stylesheet" type="text/css" href="'.$cssFile.'" />';
		}
		
		if((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "vertical"))
			echo '
		<link rel="stylesheet" type="text/css" href="./styles/standard/vertical.css" />';
		
		if((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop"))
			echo '
		<link rel="stylesheet" type="text/css" href="./styles/standard/desktop.css" />';
		
		if((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "fixed"))
			echo '
		<link rel="stylesheet" type="text/css" href="./styles/standard/fixed.css" />';
		?>

		<!--[if lt IE 9]>
		<script type="text/javascript">
			alert("Sie benötigen mindestens Internet Explorer Version 9!");
		</script>
		<![endif]-->

	</head>
	<body>
		<div id="DynamicJS" style="display: none;"></div>
		<!--<div style="position:fixed;top:0px;left:0px;width:20px;" id="growlContainer"></div>-->
		<div id="boxInOverlay" style="display: none;" class="backgroundColor0 borderColor1">

			<?php
			echo Environment::getS("contentLoginTop", "");
			
			if(Environment::getS("showCopyright", "1") == "1") { ?>
			<p style="color:grey;left:10px;position:fixed;bottom:10px;"><a style="color:grey;" target="_blank" href="http://www.furtmeier.it"><?php echo T::_("Unternehmenssoftware"); ?></a> <?php echo T::_("von Furtmeier Hard- und Software"); ?></p>
			<?php } ?>
			<form id="loginForm" onsubmit="return false;">
				<table class="loginWindow" style="border-spacing: 0 0px;">
					<colgroup>
						<col class="" style="width:120px;" />
						<col class="" />
					</colgroup>
					<tr>
						<td><label><?php echo T::_("Benutzername"); ?>:</label></td>
						<td><input style="width:285px;" tabindex="1" onfocus="focusMe(this);" onblur="blurMe(this);" type="text" name="loginUsername" id="loginUsername" onkeydown="userControl.abortAutoCertificateLogin(); userControl.abortAutoLogin(); if(event.keyCode == 13) userControl.doLogin();" /></td>
					</tr>
					<tr>
						<td><label><?php echo T::_("Passwort"); ?>:</label></td>
						<td>
							<img
								style="float:right;"
								class="mouseoverFade"
								onclick="$j('#loginOptions, #altLogins').toggle();"
								src="./images/i2/settings.png"
								title="<?php echo T::_("Optionen anzeigen"); ?>"
								alt="<?php echo T::_("Optionen anzeigen"); ?>"/>
							
							<img
								style="float:right;margin-right:5px;"
								class="mouseoverFade"
								onclick="rmeP('Users', -1, 'lostPassword', [$('loginUsername').value], 'checkResponse(transport);');"
								src="./images/i2/hilfe.png"
								title="<?php echo T::_("Passwort vergessen?"); ?>"
								alt="<?php echo T::_("Passwort vergessen?"); ?>"/>
							<input
								style="width:240px;"
								onfocus="focusMe(this);"
								onblur="blurMe(this);"
								type="password"
								id="loginPassword"
								tabindex="2"
								onkeydown="userControl.abortAutoCertificateLogin(); userControl.abortAutoLogin(); if(event.keyCode == 13) userControl.doLogin();"
							/>
						</td>
					</tr>
					<tr id="loginOptions">
						<td><label><?php echo T::_("Anwendung"); ?>:</label></td>
						<td>
							<?php
							
							if(Environment::getS("showApplicationsList", "1") == "1"){ ?>
								<select
									style=""
									id="anwendung"
									name="anwendung"
									tabindex="3"
									onkeydown="if(event.keyCode == 13) userControl.doLogin();">
									<?php echo $_SESSION["applications"]->getHTMLOptions(isset($_GET["application"]) ? $_GET["application"] : null); ?>
								</select>
							<?php }
							
							if(Environment::getS("showApplicationsList", "1") == "0"){ ?>
								<input
									type="hidden"
									id="anwendung"
									name="anwendung"
									value="<?php echo Environment::getS("defaultApplication", ""); ?>"/>
							<?php } ?>
								
							<input
								type="hidden"
								id="loginSprache"
								name="loginSprache"
								value="default"/>
								
							<!--<select
								style="width:160px;margin-top:5px;"
								id="loginSprache"
								name="loginSprache"
								tabindex="4"
								onkeydown="if(event.keyCode == 13) userControl.doLogin();">
								
								<option value="default"><?php echo T::_("Sprache"); ?></option>
								<option value="de_DE">deutsch</option>
								<option value="en_US">english</option>
								<option value="it_IT">italiano</option>
							</select>-->
								
							</td>
					</tr>
					<tr>
						<td colspan="2" style="background-color:#EEE;">
							<input
								class="bigButton"
								type="button"
								style="float:right;background-image:url(./images/navi/keys.png);background-color:#CCC;width: 150px;"
								onclick="userControl.doLogin();"
								value="<?php echo T::_("Anmelden"); ?>" />
							
							<div id="countdownCertificateLogin" style="float:right;margin-top:20px;width:20px;text-align:right;margin-right:5px;"></div>
								
							<div style="padding-top:3px;" id="saveLoginDataContainer">
								<input
									type="checkbox"
									style="margin:0px;margin-right:5px;float:left;"
									onclick="if($j(this).prop('checked')) $j('#doAutoLoginContainer').fadeIn(); else { $j('#doAutoLogin').prop('checked', false); $j('#doAutoLoginContainer').fadeOut(); }"
									name="saveLoginData"
									id="saveLoginData" />
								<label
									style="float:none;display:inline;font-weight:normal;color:grey;"
									for="saveLoginData">
									<?php echo T::_("Zugangsdaten speichern"); ?>
								</label>
							</div>
							<div style="padding-top:5px;display:none;" id="doAutoLoginContainer">
								<input
									type="checkbox"
									style="margin:0px;margin-right:5px;float:left"
									name="doAutoLogin"
									id="doAutoLogin"
									onclick="userControl.abortAutoLogin();"/>
								<label
									style="float:none;display:inline;font-weight:normal;color:grey;"
									for="doAutoLogin">
									<?php echo T::_("Automatisch anmelden"); ?>
								</label>
							</div>
							<input type="hidden" value="" name="loginSHAPassword" id="loginSHAPassword" />
							<input type="hidden" value="1" name="loginPWEncrypted" id="loginPWEncrypted" />
						</td>
					</tr>
					<?php if(strstr($_SERVER["SCRIPT_FILENAME"],"demo") OR $validUntil != null) { ?>
						<tr>
							<td colspan="2">
								<?php echo ($validUntil != null ? "Bitte beachten Sie: Diese Version läuft noch bis ".date("d.m.Y", $validUntil) : "Für den Demo-Zugang verwenden Sie bitte Max//Max oder Admin//Admin"); ?>
							</td>
						</tr>
					<?php } ?>
					
					<tr id="loginCertOptions" style="display:none;" class="backgroundColor3">
						<td colspan="2">In diesem Browser wurde noch kein Zertifikat für die Ein-Klick-Anmeldung hinterlegt. Bitte bestellen Sie ein Zertifikat für Ihren Benutzer bei <a href="http://www.furtmeier.it/page-Kontakt" target="_blank">Furtmeier Hard- und Software</a> und kopieren Sie es anschließend in das Textfeld:
							<br />
							<br />
							<textarea id="loginNewCertificate" class="backgroundColor2" style="width:99%;font-size:10px;height:100px;"></textarea>
							<br />
							<br />
							<input type="button" class="bigButton backgroundColor2" style="background-image:url(./images/navi/bestaetigung.png);float:right;" onclick="userControl.saveCertificate();" value="speichern"/>
						</td>
					</tr>
					
					<?php if(Environment::getS("impressum", "") != "") { ?>
						<tr>
							<td colspan="2" style="text-align:right;font-size:10px;">
								<a target="_blank" href="<?php echo Environment::getS("impressum", ""); ?>" onclick="window.open('<?php echo Environment::getS("impressum", ""); ?>','Impressum','height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes'); return false;">Impressum</a>
								<?php
								
								if(Environment::getS("datenschutz", "") != "")
									echo '
									| <a target="_blank" href="'.Environment::getS("datenschutz", "").'" onclick="window.open(\''.Environment::getS("datenschutz", "").'\',\'Datenschutz\',\'height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes\'); return false;">Datenschutz</a>';
								
								if(Environment::getS("registrierung", "") != "")
								echo '
									| <a target="_blank" href="'.Environment::getS("registrierung", "").'" onclick="window.open(\''.Environment::getS("registrierung", "").'\',\'Datenschutz\',\'height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes\'); return false;">Registrieren</a>';
								?>
							</td>
						</tr>
					<?php }
					
					if(strstr($_SERVER["SCRIPT_FILENAME"],"demo_all")){ ?>
						<tr>
							<td colspan="2">
								Dies ist die erweiterte Demoversion. Sie können sich auch als Mitarbeiter//Mitarbeiter einloggen, um eine für Mitarbeiter angepasste Version zu sehen.
							</td>
						</tr>
					<?php } ?>
				</table>
			</form>
			
			<div style="float:right;margin-bottom:-70px;opacity:0.3;" id="altLogins">
				<?php if(extension_loaded("openssl") AND Environment::getS("showCertificateLoginButton", "1") == "1") { ?>
				<img
					class="mouseoverFade"
					src="./plugins/Users/certificateLogin.png"
					style="margin-top:15px;margin-right:10px;"
					onclick="userControl.doCertificateLogin();"
					id="buttonCertificateLogin"
					title="<?php echo T::_("Mit Zertifikat anmelden");?>"
					alt="<?php echo T::_("Mit Zertifikat anmelden");?>"/>
				<?php } ?>
			</div>
		</div>
		
		<div id="lightOverlay" style="display: none;" class="backgroundColor0">
			<img src="./images/html5.png" style="position:fixed;bottom:20px;right:20px;" alt="HTML5" title="HTML5" />
			<svg
				style="width:5000px;height:4000px;"
				xmlns="http://www.w3.org/2000/svg"
				xmlns:xlink="http://www.w3.org/1999/xlink">
				<defs
					id="defs3012">
				<linearGradient
					x1="-42.290161"
					y1="143.31378"
					x2="403.77344"
					y2="1179.3137"
					id="linearGradient3282"
					xlink:href="#linearGradient3276"
					gradientUnits="userSpaceOnUse" />
				<linearGradient
					id="linearGradient3276">
					<stop
						id="stop3278"
						style="stop-color:#fdd99b;stop-opacity:1"
						offset="0" />
					<stop
						id="stop3280"
						style="stop-color:#fdd99b;stop-opacity:0"
						offset="1" />
				</linearGradient>
				<linearGradient
					x1="753.72754"
					y1="157.27899"
					x2="1185.8259"
					y2="1161.8571"
					id="linearGradient3025"
					xlink:href="#linearGradient3276"
					gradientUnits="userSpaceOnUse"
					gradientTransform="translate(-652.92988,-535.04004)" />
				<linearGradient
					x1="-0.46899444"
					y1="-57.742737"
					x2="88.643677"
					y2="306.25726"
					id="linearGradient3290"
					xlink:href="#linearGradient3284"
					gradientUnits="userSpaceOnUse" />
				<linearGradient
					id="linearGradient3284">
					<stop
						id="stop3286"
						style="stop-color:#cfbda8;stop-opacity:1"
						offset="0" />
					<stop
						id="stop3288"
						style="stop-color:#ffffff;stop-opacity:0"
						offset="1" />
				</linearGradient>
				<linearGradient
					x1="-0.46899444"
					y1="-57.742737"
					x2="88.643677"
					y2="306.25726"
					id="linearGradient3046"
					xlink:href="#linearGradient3284"
					gradientUnits="userSpaceOnUse"
					gradientTransform="translate(-185.37404,592.87887)" />
				<linearGradient
					x1="753.72754"
					y1="157.27899"
					x2="1519.8259"
					y2="2141.8569"
					id="linearGradient3083"
					xlink:href="#linearGradient3284"
					gradientUnits="userSpaceOnUse"
					gradientTransform="translate(-925.25174,-953.99673)" />
				</defs>
				<g
					transform="translate(0,147.63782)"
					id="layer1">
				<path
					d="M 1971.8271,1649.7169 C 1585.0954,1961.859 -818.24172,1014.1453 -905.09085,431.51644 720.11678,-1427.3773 9232.2538,-799.22686 1971.8271,1649.7169 z"
					id="path3081"
					style="opacity:0.48318092;fill:url(#linearGradient3083);fill-opacity:1;stroke:none" />
				<path
					d="M 2244.1489,2068.6736 C 1857.5335,2380.6652 -409.38459,1008.6272 -806.76899,710.47313 725.51083,-911.45479 9384.8842,-75.05722 2244.1489,2068.6736 z"
					id="rect3263"
					style="opacity:0.48318092;fill:url(#linearGradient3025);fill-opacity:1;stroke:#a05a2c" />
				</g>
			</svg>
		</div>
		<div id="darkOverlay" style="display: none; background-color:black;"></div>
		<div id="phim" style="display:none;"></div>

		<div id="container" style="display:none;">
			<div id="messenger" style="display:none;" class="backgroundColor3 borderColor1"></div>
			<div id="navigation"></div>
			<?php if(isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop"){ ?>
				<div id="desktopWrapper">
					<div id="wrapperHandler" class=""></div>
					<div id="wrapper">
						<div id="contentScreen"></div>
						<div id="wrapperTable" style="display:none;"></div><!-- Remove some time -->
						<div id="wrapperTableTd2">
							<div id="contentRight"></div>
						</div>
						<div id="wrapperTableTd3">
							<div id="contentCenter"></div>
						</div>
						<div id="wrapperTableTd1">
							<div id="contentLeft">
								<p><?php echo T::_("Sie haben JavaScript nicht aktiviert."); ?><br />
								<?php echo T::_("Bitte aktivieren Sie JavaScript, damit diese Anwendung funktioniert."); ?></p>
							</div>
						</div>
						<div style="clear:both;"></div>
						
						<div id="contentBelow" style="display:none;"><div id="contentBelowContent"></div></div>
					</div>
				</div>
			<?php } else { ?>
				<div id="wrapper">
					<div id="contentScreen"></div>
						<div id="wrapperTable" style="display:none;"></div><!-- Remove some time -->
						<div id="wrapperTableTd2">
							<div id="contentRight"></div>
						</div>
						<div id="wrapperTableTd3">
							<div id="contentCenter"></div>
						</div>
						<div id="wrapperTableTd1">
							<div id="contentLeft">
								<p><?php echo T::_("Sie haben JavaScript nicht aktiviert."); ?><br />
								<?php echo T::_("Bitte aktivieren Sie JavaScript, damit diese Anwendung funktioniert."); ?></p>
							</div>
						</div>
						<div style="clear:both;"></div>
						
					<div id="contentBelow" style="display:none;"><div id="contentBelowContent"></div></div>
				</div>
			<?php } ?>
			<div id="windows"></div>
			<div id="windowsPersistent"></div>
			<div id="stash"></div>
			<div id="footer">
				<p>
					<span
						style="cursor:pointer;margin-left:5px;float:left;"
						class="iconic iconicL x"
						title="Abmelden"
						onclick="<?php echo Environment::getS("onLogout", "userControl.doLogout();"); ?>"></span>
					
					<span
						style="cursor:pointer;margin-left:40px;margin-right:15px;float:left;"
						class="iconic iconicL layers_alt"
						title="Navigation"
						id="buttonHideNavigation"></span>
					<!--<img
						style="margin-left:15px;float:left;"
						class="mouseoverFade"
						title="Abmelden"
						alt="Abmelden"
						src="./images/i2/logout.png"
						onclick="<?php echo Environment::getS("onLogout", "userControl.doLogout();"); ?>" />-->

					<?php
					
					if(Environment::getS("showLayoutButton", "1") == "1"){ ?>
						<span
							style="cursor:pointer;float:right;margin-left:15px;margin-right:5px;"
							class="iconic iconicL wrench"
							title="Layout"
							onclick="phynxContextMenu.start(this, 'Colors','1','Einstellungen:','left', 'up');"></span>
					
						<!--<img
							onclick="phynxContextMenu.start(this, 'Colors','1','Einstellungen:','left', 'up');"
							style="float:right;margin-left:8px;margin-right:5px;"
							class="mouseoverFade"
							title="Layout"
							alt="Layout"
							src="./images/navi/office.png" />-->
					<?php }
				
					if(Environment::getS("showHelpButton", "1") == "1"){ ?>
						<span
							style="cursor:pointer;float:right;margin-left:15px;margin-right:5px;"
							class="iconic iconicL comment_alt2_stroke"
							title="Hilfe"
							onclick="window.open('http://www.phynx.de/support');"></span>
						<!--<img
							onclick="window.open('http://www.phynx.de/support');"
							style="float:right;margin-left:8px;margin-right:5px;"
							class="mouseoverFade"
							title="Hilfe"
							alt="Hilfe"
							src="./images/navi/hilfe.png" />-->
					<?php }
						
					if(Environment::getS("showDashboardButton", "1") == "1"){ ?>
						<span
							style="cursor:pointer;float:right;margin-left:15px;margin-right:5px;"
							class="iconic iconicL home"
							title="Dashboard"
							onclick="contentManager.loadDesktop()"></span>
						<!--<img
							onclick="contentManager.loadDesktop()"
							style="float:right;margin-left:8px;margin-right:5px;"
							class="mouseoverFade"
							title="Dashboard"
							alt="Dashboard"
							src="./images/navi/dashboard.png" />-->
					<?php }
				
					if(Environment::getS("showTouchButton", "1") == "1"){ ?>
						<span
							style="cursor:pointer;float:right;margin-left:15px;margin-right:5px;"
							class="iconic iconicL cursor"
							id="buttonTouchReset"
							title="Touch"></span>
					<?php }
					
					if(Environment::getS("showMenuButton", "1") == "1"){ ?>
						<span
							style="cursor:pointer;float:right;margin-left:15px;margin-right:5px;"
							class="iconic iconicL iphone"
							id="buttonMenu"
							title="Menü"></span>
					<?php } ?>
					<!--<xsl:if test="options/showDesktopButton/@value='true'">
						<img
							onclick="DesktopLink.toggle();"
							style="float:right;margin-left:8px;margin-right:5px;"
							class="mouseoverFade"
							title="Desktop"
							alt="Desktop"><xsl:attribute name="src"><xsl:value-of select="iconDesktop" /></xsl:attribute></img>
					</xsl:if>-->
					<?php if(Environment::getS("showCopyright", "1") == "1")
						echo Environment::getS("contentCopyright", 'Copyright (C) 2007 - 2014 by <a href="http://www.Furtmeier.IT">Furtmeier Hard- und Software</a>. This program comes with ABSOLUTELY NO WARRANTY; this is free software, and you are welcome to redistribute it under certain conditions; see <a href="gpl.txt">gpl.txt</a> for details.<!--<br />Thanks to the authors of the libraries and icons used by this program. <a href="javascript:contentManager.loadFrame(\'contentRight\',\'Credits\');">View credits.</a>-->');
					?>
				</p>
			</div>
		</div>
		<script type="text/javascript">
			$j(document).ready(function() {
				Ajax.physion = '<?php echo $physion; ?>';
				<?php $build = Phynx::build(); if($build) echo "Ajax.build = '$build';\n"; ?>
				$j(document).keydown(function(evt){
					if(!(evt.keyCode == 83 && evt.ctrlKey))
						return;
					evt.preventDefault();
					
					var element = $j(evt.target).prop("tagName").toLowerCase();
					
					if(element != "input" && element != "select" && element != "textarea")
						return;
					
					
					var button = $j(evt.target).parent().parent().parent().find("input[name=currentSaveButton]");
					if(button.length != 1)
						return;
					
					button.trigger("click");
				});

				<?php
				if(!$updateTitle)
					echo "
				contentManager.updateTitle = false;";
				

				echo "contentManager.init('".(isset($_COOKIE["phynx_layout"]) ? $_COOKIE["phynx_layout"] : "horizontal")."');";
				
				?>
				
				$j('#altLogins').hover(function(){
					$j(this).fadeTo('fast', 1);
				}, function(){
					$j(this).fadeTo('slow', 0.3);
				});

				<?php 
					if(Environment::getS("showApplicationsList", "1") == "0" OR count($_SESSION["applications"]->getApplicationsList()) <= 1)
						echo "\$j('#loginOptions, #altLogins').hide();"
				?>
				/*setTimeout(function(){
					if($j.jStorage.get('phynxUserCert', null) == null && $j('#buttonCertificateLogin').length > 0)
						$j('#buttonCertificateLogin').css('opacity', '0.2');
					else
						userControl.autoCertificateLogin();
					} , 500);  */
			});

		</script>
		
		<div style="display:none;" id="messageSetup" title="Ersteinrichtung">
			<?php echo T::_("Bitte verwenden Sie '<b>Admin</b>' als Benutzername und als Passwort, um mit der Ersteinrichtung dieser Anwendung fortzufahren."); ?>
		</div>
		
		<div style="display:none;" id="messageTouch" title="Touch-Eingabe">
			<?php echo T::_("Ihr Gerät unterstützt Touch-Eingaben. Möchten Sie die Touch-Optimierungen aktivieren? Maus-Eingaben werden dann nicht mehr funktionieren.<br /><br />Wenn Sie 'Ja' auswählen, wird die Anwendung neu geladen. Sie können diese Auswahl mit dem %1-Knopf rechts unten rückgängig machen.", "<span class=\"iconic iconicG cursor\"></span>"); ?>
		</div>
		
		<div style="display:none;" id="messageTouchReset" title="Touch-Eingabe">
			<?php echo T::_("Möchten Sie die Eingabemethode zurücksetzen? Sie werden dann erneut gefragt, ob Sie die Touch-Optimierungen nutzen möchten.<br /><br />Wenn Sie 'Ja' auswählen, wird die Anwendung neu geladen."); ?>
		</div>
	</body>
</html>
