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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

if(count($_GET) == 0){
	header("location: info.php?p=Adressen&bps=AdressenGUI;selectionMode:singleSelection,Auftrag,1,getAdresseCopy,Auftraege,contentLeft,Auftrag,1");
	exit;
}
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);

require "connect.php";

/**
 * WENN SIE DIESE ZEILE IN IHREM BROWSER SEHEN, WURDE PHP NICHT KORREKT EINGERICHTET!
 */


ini_set("zend.ze1_compatibility_mode","Off");

setcookie("TestCookie","inhalt", time() + 60);

$tempDir = str_replace("/info","",dirname(Util::getTempFilename()));
$tempDirSubdirs = array();

$tempStat = stat($tempDir);
$tempUser = array("name" => "windows");
if(function_exists('posix_getpwuid')) $tempUser = posix_getpwuid($tempStat[4]);
$tempGroup = array("name" => "windows");
if(function_exists('posix_getgrgid')) $tempGroup = posix_getgrgid($tempStat[5]);

$infoStat = stat(__FILE__);
$infoUser = array("name" => "windows");
if(function_exists('posix_getpwuid')) $infoUser = posix_getpwuid($infoStat[4]);
$infoGroup = array("name" => "windows");
if(function_exists('posix_getgrgid')) $infoGroup = posix_getgrgid($infoStat[5]);

function generic($e, $v){
	return ($e ? "<span style=\"color:green;\">$v</span>" : "<span style=\"color:red;\">nicht $v</span>");
}

function genericReverse($e, $v){
	return ($e ? "<span style=\"color:red;\">$v</span>" : "<span style=\"color:green;\">nicht $v</span>");
}

function noColorReverse($e, $v){
	return ($e ? "$v" : "nicht $v");
}

echo "<script></script>";
echo "<pre>";

$apps = "../applications/";
$fp = opendir($apps);
while (($file = readdir($fp)) !== false) {
	if(is_dir($apps.$file)) continue;
	if($file[0] == "-") continue;
	
	require_once $apps.$file;

	$c = str_replace(".class.php","",$file);
	$c = new $c();
	
	echo str_pad($c->registerName()."-Version:",30)."		".(method_exists($c, "registerVersion") ? "<span style=\"color:green;\">".$c->registerVersion()."</span>" : "<span style=\"color:red;\">unbekannt</span>")."\n";
}
closedir($fp);

$fp = opendir($tempDir);
while (($file = readdir($fp)) !== false) {

	if(!is_dir($tempDir."/".$file)) continue;
	if($file[0] == ".") continue;
	$stat = stat($tempDir."/".$file);
	
	$user = array("name" => "windows");
	if(function_exists('posix_getpwuid')) $user = posix_getpwuid($stat[4]);
	$group = array("name" => "windows");
	if(function_exists('posix_getgrgid')) $group = posix_getgrgid($stat[5]);
	$tempDirSubdirs[] = "	".str_pad($file.":",32).str_pad("<span style=\"color:".((($stat[4] == $infoStat[4] AND $stat[5] == $infoStat[5]) OR !ini_get("safe_mode")) ? "green;" : "red;  ")."\">".$user["name"]."($stat[4]):".$group["name"]."($stat[5])</span>", 70).generic(is_writable($tempDir."/".$file), "beschreibbar");
}
closedir($fp);

echo "\n";
if(isset($_GET["mailto"])){
	require "../libraries/mailer/htmlMimeMail5.class.php";
	
	$mail = new htmlMimeMail5();
	$mail->setFrom("phynx Mailtest <".$_GET["mailfrom"].">");
	if(!ini_get('safe_mode')) $mail->setReturnPath($_GET["mailfrom"]);
	$mail->setSubject("phynx Mailtest");
	
	$mail->setText(wordwrap("Diese Nachricht wurde vom phynx Mailtester erzeugt. Ihre E-Mail-Einstellungen sind korrekt.",80));
	$adressen = array();
	$adressen[] = $_GET["mailto"];
	if($_GET["mailfrom"] != "")
		if($mail->send($adressen))
			echo "<span style=\"color:green;\">E-Mail erfolgreich übergeben.</span>\n\n";
		else
			echo "<span style=\"color:red;\">Fehler beim Übergeben der E-Mail. Bitte überprüfen Sie Ihre Server-Einstellungen.\nFehler: ".print_r($mail->errors, true)."</span>\n\n";
	else
		echo "<span style=\"color:red;\">Bitte geben Sie eine gültige Absender-Adresse ein.</span>\n\n";
			
}
#if(!extension_loaded("mysqli")) die("<span style=\"color:red;\">Die php-Erweiterung mysqli ist nicht installiert!</span>");

$mod_security = (isset($_GET["p"]) AND $_GET["p"] == "Adressen" AND isset($_GET["bps"]) AND $_GET["bps"] == "AdressenGUI;selectionMode:singleSelection,Auftrag,1,getAdresseCopy,Auftraege,contentLeft,Auftrag,1");

$pf = new PhpFileDB();
$pf->setFolder("../system/DBData/");
$pf->pfdbQuery("SELECT * FROM Installation");

@mysql_connect('localhost','','');
$e1 = mysql_error();
@mysql_connect('127.0.0.1','','');
$e2 = mysql_error();
$mysql = false;

if(preg_match("/^Access denied for user/", $e1)) $mysql = true;
if(preg_match("/^Access denied for user/", $e2)) $mysql = true;
echo "PHP-Version:				<span style=\"color:".(version_compare(phpversion(), "5", ">=") ? "green" : "red").";\">".phpversion()."</span>\n";
echo "zend.ze1_compatibility_mode:		".generic(ini_get("zend.ze1_compatibility_mode") == "Off","aus")."\n";
echo "Apache mod_security:			Test ".generic($mod_security, "bestanden")."\n";

echo "Safe Mode:				".noColorReverse(ini_get("safe_mode"), "aktiviert")."\n";
if(ini_get("safe_mode"))
echo "  <span style=\"color:red;\">Achtung: bei aktiviertem Safe Mode werden die Besitzer der Dateien
  und Verzeichnisse mit dem Besitzer des ausgeführten Scripts verglichen.
  Wenn Sie also Dateien über einen FTP-Benutzer hochlanden, kann die Anwendung nicht
  auf temporäre Verzeichnisse im IECache-Verzeichnis zugreifen, die der Webserver anlegt.
  Löschen Sie also mit Ihrem FTP-Programm die Unterverzeichnisse (1, 2, 3, ...)
  und legen Sie sie von Hand mit dem FTP-Programm neu an.</span>\n\n";

echo "IECache-Verzeichnis:			".noColorReverse(is_writable("../system/IECache/"),"beschreibbar")."\n";

echo "Besitzer der info.php-Datei:		".$infoUser["name"]."(".$infoStat[4]."):".$infoGroup["name"]."(".$infoStat[5].")\n";
echo "Temporäres Verzeichnis:			".$tempDir.": ".$tempUser["name"]."($tempStat[4]):".$tempGroup["name"]."($tempStat[5]) ".generic(is_writable($tempDir)," beschreibbar")."\n";
echo implode("\n",$tempDirSubdirs)."\n";
#echo "Apache-User:				".`id`;
echo "\n";

echo "MySQLi-Modul:				".generic(extension_loaded("mysqli"),"geladen")."\n";
echo "MySQL-Modul:				".generic(extension_loaded("mysql"),"geladen")."\n";
echo "Reflection-Modul:			".generic(extension_loaded("reflection"),"geladen")."\n";
echo "GD-Modul:				".generic(extension_loaded("gd"),"geladen")." (bisher nur für multiCMS)\n";
echo "Curl-Modul:				".generic(extension_loaded("curl"),"geladen")." (pixelLetter-Plugin)\n";
echo "Session-Modul:				".generic(extension_loaded("session"),"geladen")."\n";
echo "Pear Imagick-Klasse:			".generic(class_exists("Imagick"),"geladen")." (Exifer-Erweiterung für multiCMS)\n\n";
echo "alle geladenen Module:\n";
echo wordwrap(implode(", ", get_loaded_extensions()), 100)."\n\n";
echo "Cookies:				".generic(isset($_COOKIE["TestCookie"]),"akzeptiert")."\n";
echo "JavaScript:				<span style=\"color:red;\" id=\"jstest\">nicht aktiviert</span>\n\n";
echo "MySQL-Server:				".generic($mysql,"erreichbar")." (es werden nur localhost und 127.0.0.1 getestet)\n";

while($t = $pf->pfdbFetchAssoc()){
	echo "	<b>".str_pad($t["host"],20)."".str_pad($t["datab"],20)."</b>\n";
	
	$r = mysql_connect($t["host"], $t["user"], $t["password"]);
	
	echo "		MySQL server: ".mysql_get_server_info()."\n";
	echo "		MySQL client: ".mysql_get_client_info()."\n";
	
	if(extension_loaded("mysqli")){
		$ri = new mysqli($t["host"], $t["user"], $t["password"], $t["datab"]);
		#print_r($ri);
		echo "		MySQLi client: ".$ri->client_info."\n";
	}
	$ts = mysql_fetch_assoc(mysql_query("SELECT @@sql_mode"));
	echo "		Mode: ".str_replace(array("STRICT_TRANS_TABLES", "STRICT_ALL_TABLES"), array("<span style=\"color:red;\">STRICT_TRANS_TABLES</span>", "<span style=\"color:red;\">STRICT_ALL_TABLES</span>"), $ts["@@sql_mode"]);
	echo "\n";
	
	if($r){
		#$qG = mysql_query("SHOW GRANTS");
		#$tG = mysql_fetch_array($qG);
		#echo "		".$tG[0]."\n\n";
		
		$q = mysql_query("SHOW TABLES FROM `".$t["datab"]."`");
		while ($row = mysql_fetch_row($q)){
			
			$s = mysql_query("SHOW TABLE STATUS FROM `$t[datab]` LIKE '$row[0]'");
			$o = mysql_fetch_array($s);
			echo "		".str_pad($row[0],20)."   ".$o["Comment"]."\n";
		}


		mysql_close($r);
	}
	echo "\n";
}
echo "Browser:				".$_SERVER["HTTP_USER_AGENT"]."\n";
echo "Server:					".$_SERVER["SERVER_SOFTWARE"]."\n\n";
echo "E-Mail-Versand:				<form style=\"border:0px;margin:0px;display:inline;\" method=\"GET\" action=\"info.php\">Absender E-Mail: <input type=\"text\" name=\"mailfrom\" /> Empfänger E-Mail: <input type=\"text\" name=\"mailto\" /> <input type=\"submit\" value=\"testen\" /></form>";
echo "</pre>";
echo "<script>var e = document.getElementById('jstest'); e.innerHTML='aktiviert'; e.style.color = 'green';</script>";

?>