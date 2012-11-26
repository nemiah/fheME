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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

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
	require "./system/basics.php";

	emoFatalError(
		"I'm sorry, but I'm unable to access some directories",
		"Please make sure that the webserver is able to access these directories and its subdirectories:<br /><br />".implode("<br />", $notExecutable)."<br /><br />Usually a good plan to achieve this, is to run the following<br />commands in the installation directory:<br /><code>chmod -R u=rw,g=r,o=r *<br />chmod -R u=rwX,g=rX,o=rX *</code>",
		"phynx");
}

$texts = array();
$texts["de_DE"] = array();
$texts["de_DE"]["username"] = "Benutzername";
$texts["de_DE"]["password"] = "Passwort";
$texts["de_DE"]["application"] = "Anwendung";
#$texts["de_DE"]["login"] = "anmelden";
$texts["de_DE"]["save"] = "Zugangsdaten speichern";
$texts["de_DE"]["sprache"] = "Sprache";
$texts["de_DE"]["optionsImage"] = "Optionen anzeigen";
$texts["de_DE"]["lostPassword"] = "Passwort vergessen?";

$texts["en_US"] = array();
$texts["en_US"]["username"] = "Username";
$texts["en_US"]["password"] = "Password";
$texts["en_US"]["application"] = "Application";
#$texts["en_US"]["login"] = "login";
$texts["en_US"]["save"] = "save login data";
$texts["en_US"]["sprache"] = "Language";
$texts["en_US"]["optionsImage"] = "show options";
$texts["en_US"]["lostPassword"] = "Lost password?";

$texts["it_IT"] = array();
$texts["it_IT"]["username"] = "Username";
$texts["it_IT"]["password"] = "Password";
$texts["it_IT"]["application"] = "Applicazione";
#$texts["it_IT"]["login"] = "accesso";
$texts["it_IT"]["save"] = "memorizzare i dati";
$texts["it_IT"]["sprache"] = "Lingua";
$texts["it_IT"]["optionsImage"] = "Visualizzare le opzioni";
$texts["it_IT"]["lostPassword"] = "Password persa?";

require "./system/connect.php";
$browserLang = Session::getLanguage();
/*
$E = new Environment();
*/
$cssColorsDir = (isset($_COOKIE["phynx_color"]) ? $_COOKIE["phynx_color"] : "standard");
/*
if(file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php")){
	require_once Util::getRootPath()."plugins/Cloud/Cloud.class.php";
	require_once Util::getRootPath()."plugins/Cloud/mCloud.class.php";

	$E = mCloud::getEnvironment();
}*/

$build = rand(1, 9999999);
if(file_exists(Util::getRootPath()."system/build.xml")){
	$xml = new SimpleXMLElement(file_get_contents(Util::getRootPath()."system/build.xml"));
	
	if(isset($_COOKIE["phynx_lastSeenBuild"]) AND $_COOKIE["phynx_lastSeenBuild"] != $xml->build->prefix."-".$xml->build->number){
		$isCloud = file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php");
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		setcookie("phynx_lastSeenBuild", $xml->build->prefix."-".$xml->build->number, time() + 3600 * 24 * 365);
		//header("location: ".  basename(__FILE__));
		
		$button = "
			<div
				onclick=\"document.location.reload();\"
				onmouseover=\"this.style.backgroundColor = '#d7eac5';\"
				onmouseout=\"this.style.backgroundColor = 'transparent';\"
				style=\"width:120px;padding:10px;border:1px solid green;border-radius:5px;box-shadow:2px 2px 4px grey;margin-top:20px;cursor:pointer;font-weight:bold;\">
				
				<img src=\"./images/navi/navigation.png\" style=\"float:left;margin-top:-8px;margin-right:10px;\" />Weiter
			</div>";
		
		emoFatalError("Diese Anwendung wurde aktualisiert", "Der Administrator dieser Anwendung hat seit Ihrem letzten Besuch eine Aktualisierung eingespielt.</p>
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
			</div>", "Diese Anwendung wurde aktualisiert", false, "ok");
	} elseif(!isset($_COOKIE["phynx_lastSeenBuild"]))
		setcookie("phynx_lastSeenBuild", $xml->build->prefix."-".$xml->build->number, time() + 3600 * 24 * 365);
	
	$build = $xml->build->prefix."-".$xml->build->number;
}

$validUntil = Environment::getS("validUntil", null);

if($_SESSION["S"]->checkIfUserLoggedIn() == false) $_SESSION["CurrentAppPlugins"]->scanPlugins();
#header('Content-type: text/html; charset="utf-8"',true);
/*echo '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="./styles/layout.xsl" ?>
<phynx>
	<HTMLGUI>
		<overlay>
			<options>
				<label for="username">'.$texts[$browserLang]["username"].'</label>
				<label for="password">'.$texts[$browserLang]["password"].'</label>
				<label for="application">'.$texts[$browserLang]["application"].'</label>
				<!--<label for="login"></label>-->
				<label for="save">'.$texts[$browserLang]["save"].'</label>
				<label for="optionsImage">'.$texts[$browserLang]["optionsImage"].'</label>
				<label for="lostPassword">'.$texts[$browserLang]["lostPassword"].'</label>
				
				<label for="isDemo">'.($validUntil != null ? "Bitte beachten Sie: Diese Version läuft noch bis ".date("d.m.Y", $validUntil) : "Für den Demo-Zugang verwenden Sie bitte Max//Max oder Admin//Admin").'</label>
				<label for="extDemo">Dies ist die erweiterte Demoversion. Sie können sich auch als Mitarbeiter//Mitarbeiter einloggen, um eine für Mitarbeiter angepasste Version von <b>open3A</b> zu sehen.</label>

				<isDemo value="'.((strstr($_SERVER["SCRIPT_FILENAME"],"demo") OR $validUntil != null) ? "true" : "false").'" />
				<isExtendedDemo value="'.(strstr($_SERVER["SCRIPT_FILENAME"],"demo_all") ? "true" : "false").'" />
				
				<hasImpressum value="'.Environment::getS("impressum", "").'" />
				<hasDatenschutz value="'.Environment::getS("datenschutz", "").'" />
				<hasRegistrierung value="'.Environment::getS("registrierung", "").'" />
				
				<showApplicationsList value="'.Environment::getS("showApplicationsList", "1").'" defaultApplicationIfFalse="'.Environment::getS("defaultApplication", "").'" />
				<showCertificateLogin value="'.(extension_loaded("openssl") ? "true" : "false").'" />
			</options>
			
			<languages>
				<lang value="default">'.$texts[$browserLang]["sprache"].'</lang>
				<lang value="de_DE">deutsch</lang>
				<lang value="en_US">english</lang>
				<lang value="it_IT">italiano</lang>
			</languages>
		</overlay>
		
		<applications>'.$_SESSION["applications"]->getGDL().'
		</applications>
		
		<options>
			<label for="title">'.Environment::getS("renameFramework", "phynx by Furtmeier Hard- und Software").'</label>
			<isDesktop value="'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop") ? "true" : "false").'" />
			<physion>'.$physion.'</physion>
		</options>
		
		<stylesheets>
			<css>./libraries/jquery/jquery-ui-1.8.17.custom.css</css>
			<css>./libraries/jquery/jquery.qtip.min.css</css>
			<css>./styles/standard/overlayBox.css</css>
			<css>./styles/standard/frames.css</css>
			<css>./styles/standard/general.css</css>
			<css>./styles/standard/navigation.css</css>
			<css>./styles/standard/autoCompletion.css</css>
			<css>./styles/standard/phynxContextMenu.css</css>
			<css>./styles/standard/TextEditor.css</css>
			<css>./styles/standard/calendar.css</css>
			<css>./styles/'.Environment::getS("cssColorsDir", $cssColorsDir).'/colors.css</css>
			'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "vertical") ? '<css>./styles/standard/vertical.css</css>' : "").'
			'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop") ? '<css>./styles/standard/desktop.css</css>' : "").'
			'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "fixed") ? '<css>./styles/standard/fixed.css</css>' : "").'
		</stylesheets>
		
		<javascripts>
			<staticJsAbove>./libraries/jquery/jquery-1.7.1.min.js</staticJsAbove>
			<staticJsAbove>./libraries/jquery/jquery-ui-1.8.17.custom.min.js</staticJsAbove>
			<staticJsAbove>./libraries/jquery/jquery.json-2.3.min.js</staticJsAbove>
			<staticJsAbove>./libraries/jquery/jquery.timers.js</staticJsAbove>
			<staticJsAbove>./libraries/jquery/jquery.qtip.min.js</staticJsAbove>
			<staticJsAbove>./libraries/jquery/jquery.scrollTo-1.4.2-min.js</staticJsAbove>
			<staticJsAbove>./libraries/jstorage.js</staticJsAbove>

			<staticJsAbove>./libraries/webtoolkit.base64.js</staticJsAbove>
			<staticJsAbove>./libraries/webtoolkit.sha1.js</staticJsAbove>
			
			'.(file_exists(Util::getRootPath()."ubiquitous/Wysiwyg/tiny_mce/tiny_mce.js") ? '
			<staticJsBelow>./ubiquitous/Wysiwyg/tiny_mce/tiny_mce.js</staticJsBelow>
			<staticJsBelow>./ubiquitous/Wysiwyg/tiny_mce/jquery.tinymce.js</staticJsBelow>' : '').'
			<staticJsBelow>./javascript/DynamicJS.php?r='.rand().'</staticJsBelow>
				
			
			<js>./javascript/P2J.js</js>
			
			<js>./javascript/Aspect.js</js>
			<js>./javascript/Observer.js</js>
			<js>./javascript/Overlay.js</js>
			<js>./javascript/Menu.js</js>
			<js>./javascript/autoComplete.js</js>
			<js>./javascript/phynxContextMenu.js</js>
			<js>./javascript/userControl.js</js>
			<js>./javascript/Interface.js</js>
			<js>./javascript/Popup.js</js>
			<js>./javascript/contentManager.js</js>
			<js>./javascript/DesktopLink.js</js>
			<js>./javascript/notificationArea.js</js>
			<js>./javascript/handler.js</js>
			<js>./javascript/Util.js</js>
			
			<js>./libraries/TextEditor.js</js>
			<js>./libraries/fileuploader.js</js>
			
			
		</javascripts>
		
		<contentLeft>
			<p>Sie haben JavaScript nicht aktiviert.<br />
			Bitte aktivieren Sie JavaScript, damit diese Anwendung funktioniert.</p>
		</contentLeft>
		
		<footer>
			<options>
				<showHelpButton value="'.Environment::getS("showHelpButton", "1").'" />
				<showLayoutButton value="'.Environment::getS("showLayoutButton", "1").'" />
				<showCopyright value="'.Environment::getS("showCopyright", "1").'" />
				<onLogout>'.Environment::getS("onLogout", "userControl.doLogout();").'</onLogout>
			</options>

			<iconLayout>./images/navi/office.png</iconLayout>
			<iconLogout>./images/i2/logout.png</iconLogout>
			<iconHelp>./images/navi/hilfe.png</iconHelp>
			
			<copyright>
				Copyright (C) 2007 - 2012 by <a href="http://www.Furtmeier.IT">Furtmeier Hard- und Software</a>. This program comes with ABSOLUTELY NO WARRANTY; this is free software, and you are welcome to redistribute it under certain conditions; see <a href="gpl.txt">gpl.txt</a> for details.<br />Thanks to the authors of the libraries and icons used by this program. <a href="javascript:contentManager.loadFrame(\'contentRight\',\'Credits\');">View credits.</a>
			</copyright>
		</footer>
	</HTMLGUI>
</phynx>';*/
?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="revisit-after" content="14 days" />
		<title><?php echo Environment::getS("renameFramework", "phynx by Furtmeier Hard- und Software"); ?></title>

		<link rel="shortcut icon" href="./images/FHSFavicon.ico" /> 

		<script type="text/javascript" src="./libraries/jquery/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery-ui-1.8.17.custom.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.json-2.3.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.timers.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.qtip.min.js"></script>
		<script type="text/javascript" src="./libraries/jquery/jquery.scrollTo-1.4.2-min.js"></script>
		<script type="text/javascript" src="./libraries/jstorage.js"></script>
		<script type="text/javascript" src="./libraries/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="./libraries/webtoolkit.sha1.js"></script>
		<script type="text/javascript" src="./libraries/modernizr.custom.js"></script>


		<script type="text/javascript" src="./javascript/P2J.js?r=<?php echo $build; ?>"></script>

		<script type="text/javascript" src="./javascript/Aspect.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Observer.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Overlay.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Menu.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/autoComplete.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/phynxContextMenu.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/userControl.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Interface.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Popup.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/contentManager.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/DesktopLink.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/notificationArea.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/handler.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./javascript/Util.js?r=<?php echo $build; ?>"></script>

		<script type="text/javascript" src="./libraries/TextEditor.js?r=<?php echo $build; ?>"></script>
		<script type="text/javascript" src="./libraries/fileuploader.js?r=<?php echo $build; ?>"></script>

			
		<?php if(file_exists(Util::getRootPath()."ubiquitous/Wysiwyg/tiny_mce/tiny_mce.js")) echo '
		<script type="text/javascript" src="./ubiquitous/Wysiwyg/tiny_mce/tiny_mce.js?r='.$build.'"></script>
		<script type="text/javascript" src="./ubiquitous/Wysiwyg/tiny_mce/jquery.tinymce.js?r='.$build.'"></script>'; ?> 
		<script type="text/javascript" src="./javascript/DynamicJS.php?r=<?php echo rand(); ?>"></script>

		<script type="text/javascript">
			if(typeof contentManager == "undefined")
				alert("Die JavaScript-Dateien konnten nicht geladen werden.\nDies kann an der Server-Konfiguration liegen.\nBitte versuchen Sie, diese Anwendung in ein Unterverzeichnis zu installieren.");
		</script>
		
		<link rel="stylesheet" type="text/css" href="./libraries/jquery/jquery-ui-1.8.17.custom.css" />
		<link rel="stylesheet" type="text/css" href="./libraries/jquery/jquery-ui-1.8.17.custom.css" />
		<link rel="stylesheet" type="text/css" href="./libraries/jquery/jquery.qtip.min.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/overlayBox.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/frames.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/general.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/navigation.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/autoCompletion.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/phynxContextMenu.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/TextEditor.css" />
		<link rel="stylesheet" type="text/css" href="./styles/standard/calendar.css" />
		<link rel="stylesheet" type="text/css" href="./styles/<?php echo Environment::getS("cssColorsDir", $cssColorsDir); ?>/colors.css" />
		<?php
		if((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "vertical"))
			echo '<link rel="stylesheet" type="text/css" href="./styles/standard/vertical.css" />';
		
		if((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop"))
			echo '<link rel="stylesheet" type="text/css" href="./styles/standard/desktop.css" />';
		
		if((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "fixed"))
			echo '<link rel="stylesheet" type="text/css" href="./styles/standard/fixed.css" />';
		?>

		<!--[if lt IE 8]>
		<script type="text/javascript">
			alert("Sie benötigen mindestens Internet Explorer Version 8!");
		</script>
		<![endif]-->

	</head>
	<body>
		<div id="DynamicJS" style="display: none;"></div>
		<!--<div style="position:fixed;top:0px;left:0px;width:20px;" id="growlContainer"></div>-->
		<div id="boxInOverlay" style="display: none;" class="backgroundColor0 borderColor1">
			<?php if(Environment::getS("showCopyright", "1") == "1") { ?>
			<p style="color:grey;left:10px;position:fixed;bottom:10px;"><a style="color:grey;" target="_blank" href="http://www.furtmeier.it">Unternehmenssoftware</a> von Furtmeier Hard- und Software</p>
			<?php } ?>
			<form id="loginForm" onsubmit="return false;">
				<table class="loginWindow">
					<colgroup>
						<col class="backgroundColor2" style="width:120px;" />
						<col class="backgroundColor3" />
					</colgroup>
					<tr>
						<td class="backgroundColor2"><label><?php echo $texts[$browserLang]["username"]; ?>:</label></td>
						<td><input style="width:285px;" tabindex="1" onfocus="focusMe(this);" onblur="blurMe(this);" type="text" name="loginUsername" id="loginUsername" onkeydown="userControl.abortAutoCertificateLogin(); if(event.keyCode == 13) userControl.doLogin();" /></td>
					</tr>
					<tr>
						<td><label><?php echo $texts[$browserLang]["password"]; ?>:</label></td>
						<td>
							<img
								style="float:right;"
								class="mouseoverFade"
								onclick="if($('loginOptions').style.display=='none') $('loginOptions').style.display=''; else $('loginOptions').style.display='none';"
								src="./images/i2/settings.png"
								title="<?php echo $texts[$browserLang]["optionsImage"]; ?>" />
							<img
								style="float:right;margin-right:5px;"
								class="mouseoverFade"
								onclick="rmeP('Users', -1, 'lostPassword', [$('loginUsername').value], 'checkResponse(transport);');"
								src="./images/i2/hilfe.png"
								title="<?php echo $texts[$browserLang]["lostPassword"]; ?>" />
							<input
								style="width:240px;"
								onfocus="focusMe(this);"
								onblur="blurMe(this);"
								type="password"
								id="loginPassword"
								tabindex="2"
								onkeydown="userControl.abortAutoCertificateLogin(); if(event.keyCode == 13) userControl.doLogin();"
							/>
						</td>
					</tr>
					<tr id="loginOptions" <?php if(Environment::getS("showApplicationsList", "1") == "0" OR count($_SESSION["applications"]->getApplicationsList()) <= 1) echo "style=\"display:none;\"" ?>>
						<td><label><?php echo $texts[$browserLang]["application"]; ?>:</label></td>
						<td>
							<select
								style="width:110px;float:right;"
								id="loginSprache"
								name="loginSprache"
								tabindex="4"
								onkeydown="if(event.keyCode == 13) userControl.doLogin();">
								
								<option value="default"><?php echo $texts[$browserLang]["sprache"]; ?></option>
								<option value="de_DE">deutsch</option>
								<option value="en_US">english</option>
								<option value="it_IT">italiano</option>
							</select>
							<?php
							
							if(Environment::getS("showApplicationsList", "1") == "1"){ ?>
								<select
									style="width:160px;"
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
							</td>
					</tr>
					<tr>
						<td colspan="2">
							<input
								class="LPBigButton backgroundColor3"
								type="button"
								style="float:right;background-image:url(./images/navi/keys.png);"
								onclick="userControl.doLogin();"
								value=" " />
							
							<?php if(extension_loaded("openssl")) { ?>
								<input
									class="LPBigButton backgroundColor3"
									type="button"
									style="float:right;background-image:url(./plugins/Users/certificateLogin.png);margin-right:10px;"
									onclick="userControl.doCertificateLogin();"
									id="buttonCertificateLogin"
									value=" " />
								<div id="countdownCertificateLogin" style="float:right;margin-top:20px;width:20px;text-align:right;margin-right:5px;"></div>
							<?php } ?>
								
							<div style="padding-top:23px;">
								<input
									type="checkbox"
									style="margin-right:5px;float:left"
									name="saveLoginData"
									id="saveLoginData" />
								<label
									style="float:none;display:inline;font-weight:normal;"
									for="saveLoginData">
									<?php echo $texts[$browserLang]["save"]; ?>
								</label>
							</div>
							<input type="hidden" value="" name="loginSHAPassword" id="loginSHAPassword" />
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
			<div id="messenger" style="left:-210px;top:0px;" class="backgroundColor3 borderColor1"></div>
			<div id="navigation"></div>
			<?php if(isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop"){ ?>
				<div id="desktopWrapper">
					<div id="wrapperHandler" class="backgroundColor1 borderColor1"></div>
					<div id="wrapper">
						<div id="contentScreen"></div>
						<table id="wrapperTable">
							<tr>
								<td id="wrapperTableTd1">
									<div id="contentLeft">
										<p>Sie haben JavaScript nicht aktiviert.<br />
										Bitte aktivieren Sie JavaScript, damit diese Anwendung funktioniert.</p>
									</div>
								</td>
								<td id="wrapperTableTd2">
									<div id="contentRight"></div>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<?php } else { ?>
				<div id="wrapper">
					<div id="contentScreen"></div>
					<table id="wrapperTable">
						<tr>
							<td id="wrapperTableTd1">
								<div id="contentLeft">
										<p>Sie haben JavaScript nicht aktiviert.<br />
										Bitte aktivieren Sie JavaScript, damit diese Anwendung funktioniert.</p>
								</div>
							</td>
							<td id="wrapperTableTd2">
								<div id="contentRight"></div>
							</td>
						</tr>
					</table>
				</div>
			<?php } ?>

			<div id="windows"></div>
			<div id="windowsPersistent"></div>
			<div id="footer">
				<p>
					<img
						style="margin-left:15px;float:left;"
						class="mouseoverFade"
						title="Abmelden"
						alt="Abmelden"
						src="./images/i2/logout.png"
						onclick="<?php echo Environment::getS("onLogout", "userControl.doLogout();"); ?>" />

					<?php if(Environment::getS("showLayoutButton", "1") == "1"){ ?>
						<img
							onclick="phynxContextMenu.start(this, 'Colors','1','Einstellungen:','left', 'up');"
							style="float:right;margin-left:8px;margin-right:5px;"
							class="mouseoverFade"
							title="Layout"
							alt="Layout"
							src="./images/navi/office.png" />
					<?php }
				
					if(Environment::getS("showHelpButton", "1") == "1"){ ?>
						<img
							onclick="window.open('http://www.phynx.de/support');"
							style="float:right;margin-left:8px;margin-right:5px;"
							class="mouseoverFade"
							title="Hilfe"
							alt="Hilfe"
							src="./images/navi/hilfe.png" />
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
						echo 'Copyright (C) 2007 - 2012 by <a href="http://www.Furtmeier.IT">Furtmeier Hard- und Software</a>. This program comes with ABSOLUTELY NO WARRANTY; this is free software, and you are welcome to redistribute it under certain conditions; see <a href="gpl.txt">gpl.txt</a> for details.<br />Thanks to the authors of the libraries and icons used by this program. <a href="javascript:contentManager.loadFrame(\'contentRight\',\'Credits\');">View credits.</a>';
					?>
				</p>
			</div>
		</div>
		<script type="text/javascript">
			$j(document).ready(function() {
				Ajax.physion = '<?php echo $physion; ?>'

				contentManager.init();

				setTimeout(function(){
					if($j.jStorage.get('phynxUserCert', null) == null && $j('#buttonCertificateLogin'))
						$j('#buttonCertificateLogin').css('opacity', '0.5');
					else
						userControl.autoCertificateLogin();
					} , 500);  
			});

		</script>
	</body>
</html>