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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
$par = "";

if(isset($_GET["class"]))			$cla = $_GET["class"];
if(isset($_GET["constructor"]))		$con = $_GET["constructor"];
if(isset($_GET["construct"]))		$con = $_GET["construct"];
if(isset($_GET["parameters"]))		$par = $_GET["parameters"];
if(isset($_GET["method"]))			$met = $_GET["method"];
if(isset($_GET["bps"]))				$bps = $_GET["bps"];
	
if(isset($_POST["class"])) 			$cla = $_POST["class"];
if(isset($_POST["constructor"])) 	$con = $_POST["constructor"];
if(isset($_POST["construct"])) 		$con = $_POST["construct"];
if(isset($_POST["parameters"]))		$par = $_POST["parameters"];
if(isset($_POST["method"])) 		$met = $_POST["method"];
if(isset($_POST["bps"]))			$bps = $_POST["bps"];

if(isset($_GET["target"])){
	$e = explode(";", $_GET["target"]);
	$cla = $e[0];
	$met = $e[1];
	$con = "";
}

$par = str_replace(";-;;raute;;-;","#",$par);
$par = str_replace(";-;;und;;-;","&",$par);
$par = str_replace(";-;;prozent;;-;","%",$par);
$par = str_replace(";-;;istgleich;;-;","=",$par);
$par = str_replace(";-;;frage;;-;","?",$par);

if($met == "getHTML") exit;
	
require "../system/connect.php";
if($cla != "Users" AND $met != "doLogin" AND $_SESSION["S"]->checkIfUserLoggedIn() == true) die("-1");
if($cla != "Users" AND $cla != "Util" AND $cla != "mUserdata" AND $cla != "mWebsocket" AND $cla != "HTML" AND $cla != "Support" AND $cla != "nicEdit" AND $met != "doLogin" AND $met != "createMyTable" AND $met != "checkMyTables" AND !$_SESSION["S"]->checkIfUserIsAllowed($cla)) die("You are not allowed to see this Page!");

$build = Phynx::build();
if($build)
	header("X-Build: ".$build);

if(isset($bps))
	$_SESSION["BPS"]->setByString($bps);

/*
$_GET["class"];  		//Class of method $_GET["method"]
$_GET["constructor"];	//Parameters to forward to constructor of $_GET["class"]
$_GET["method"];		//Method to call in $_GET["class"]
$_GET["parameters"]		//Parameters to call method $_GET["method"] with
*/

$c = $cla."GUI";
$d = new $c($con);

$par = (get_magic_quotes_gpc() ? stripslashes($par) : $par);

#$phpversion = str_replace(".","",phpversion())*1;
if(Util::phpVersionGEThen("5.1.0")){
	$pars = explode("','",$par);
	$pars[0] = substr($pars[0],1);
	$pars[count($pars) - 1] = substr($pars[count($pars) - 1],0,strlen($pars[count($pars) - 1])-1);

	if(!method_exists($d, $met)){
		if(!method_exists($d, "__call")) 
			Red::errorD("Die Methode $c::$met existiert nicht");
		else {
			array_unshift($pars, $met);
			$met = "__call";
		}
	}
	$method = new ReflectionMethod($c, $met);
	try {
		$method->invokeArgs($d, $pars);
	} catch (FieldDoesNotExistException $e) {
		Red::errorUpdate($e);
	}
} else
	eval("\$d->".$met."($par);");

?>