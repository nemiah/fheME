<?php
/*
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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
require_once '../libraries/Timer.class.php';

require "../system/connect.php";
#$output = new Output('auto', true, false);
#$T = new Timer();

foreach($_POST AS $k => $v)
	$_GET[$k] = $v;

$build = Phynx::build();
if($build)
	header("X-Build: ".$build);

if($_SESSION["S"]->checkIfUserLoggedIn() == true) {
	#setcookie(session_name(),"",time()-1000);
	#echo session_id();
	die("NO USER SESSION");
}
if(!$_SESSION["S"]->checkIfUserIsAllowed($_GET["p"])) Red::errorD("Sie haben keine Berechtigung, diese Seite zu betrachten!");

if(!$_SESSION["S"]->isUserAdmin()) $userHiddenPlugins = mUserdata::getHiddenPlugins();
if(isset($userHiddenPlugins[$_GET["p"]])) Red::errorD("Sie haben keine Berechtigung, diese Seite zu betrachten!");

if(isset($_GET["bps"]))
	$_SESSION["BPS"]->setByString($_GET["bps"].(isset($_GET["bpsPar"]) ? ";".implode(";", $_GET["bpsPar"]) : ""));

$_GET["p"] = str_replace("GUI","",$_GET["p"]);
$n = $_GET["p"]."GUI";
try {
	$b = new $n((isset($_GET["id"]) ? $_GET["id"] : "-1"));
} catch (ClassNotFoundException $e){
	Red::errorClass($n);
}

if(!PMReflector::implementsInterface($n,"iGUIHTMLMP2")
	AND !PMReflector::implementsInterface($n,"iGUIHTML2"))
		Red::errorD ("Class $_GET[p]GUI needs to implement the interface iGUIHTML2 or iGUIHTMLMP2!");
		
try {
	ob_start();
	Timer::now("init", __FILE__, __LINE__);
	
	if(method_exists($b, "appendable"))
		$b->appendable(isset($_GET["appendable"]) ? $_GET["appendable"] : false);
	
	if(method_exists($b, "targetFrame"))
		$b->targetFrame(isset($_GET["frame"]) ? $_GET["frame"] : null);
	
	echo $b->getHTML((isset($_GET["id"]) ? $_GET["id"] : "-1"), isset($_GET["page"]) ? $_GET["page"] : 0, isset($_GET["frame"]) ? $_GET["frame"] : null);
	Timer::now("done", __FILE__, __LINE__);
	
	$timers = Timer::getLogged();
	if(count($timers) > 0)
		header("X-Timers: ".json_encode($timers));
	
	if(isset($_SESSION["phynx_Achievements"]) AND is_array($_SESSION["phynx_Achievements"]) AND count($_SESSION["phynx_Achievements"]) > 0){
		header("X-Achievements: ".json_encode($_SESSION["phynx_Achievements"]));
		$_SESSION["phynx_Achievements"] = array();
	}
	
	ob_end_flush();
} catch (TableDoesNotExistException $e) {
	if(Util::getCloudHost() != null)
		throw $e;
	
	Red::errorD("Die Datenbank-Tabelle (".$e->getTable().") dieses Plugins wurde noch nicht angelegt. Bitte verwenden Sie das Installations-Plugin im Administrationsbereich.");
} catch (DatabaseNotSelectedException $e) {
	Red::errorD("Keine Datenbank ausgewählt. Bitte verwenden Sie das Installations-Plugin im Administrationsbereich.");
} catch (NoDBUserDataException $e) {
	Red::errorD("Die Datenbank-Zugangsdaten sind falsch. Bitte verwenden Sie das Installations-Plugin im Administrationsbereich.");
} catch (FieldDoesNotExistException $e) {
	if(Util::getCloudHost() != null)
		throw $e;
	
	Red::errorUpdate($e);
} catch (DatabaseNotFoundException  $e) {
	Red::errorD("Keine Datenbank ausgewählt. Bitte verwenden Sie das Installations-Plugin im Administrationsbereich.");
}

?>