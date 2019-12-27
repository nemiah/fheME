<?php
/*
 *  This file is part of plugins.

 *  plugins is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  plugins is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnInstallation");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);

$e->addClassPath($absolutePathToPhynx."/plugins/Installation");

#$e->useDefaultMySQLData(); //DOES NOT WORK IN CLOUD!

if($argv[2] == "All"){
	$mandanten = Installation::getMandanten(true);
	$data = array();
	foreach($mandanten AS $httpHost){
		$_SERVER["HTTP_HOST"] = $httpHost;
		
		Session::reloadDBData();
		$e->useAdminUser();
		
		$I = new mInstallation();
		$data[$httpHost] = $I->updateAllTables();
	}
} else {
	Session::reloadDBData();

	$e->useAdminUser();

	$I = new mInstallation();
	$data = array("*" => $I->updateAllTables());
}

$CH = Util::getCloudHost();
if($CH == null OR !isset($CH->emailAdmin)){
	foreach($data AS $host => $sub){
		echo "Checking $host …\n";
		foreach($sub AS $k => $v)
			echo $k.": ".trim($v)."\n";
		echo "Done $host …\n";
	}
	$e->cleanUp();
	exit();
}

$T = new HTMLTable(2);
$T->setTableStyle("font-size:10px;font-family:sans-serif;");

$T->addColStyle(1, "vertical-align:top;");
foreach($data AS $host => $sub){
	foreach($sub AS $k => $v)
		$T->addRow(array($k, "<pre>".trim($v)."</pre>"));
}

$mimeMail2 = new PHPMailer(true, "", true);
$mimeMail2->SMTPOptions = array(
	'ssl' => array(
		'verify_peer' => false,
		'verify_peer_name' => false,
		'allow_self_signed' => true
	)
);
#$mimeMail2->SMTPDebug = 2;
$mimeMail2->Hostname = trim(shell_exec("hostname"));
$mimeMail2->CharSet = "UTF-8";
$mimeMail2->Subject = "Installation Plugin";

$mimeMail2->From = $CH->emailAdmin;
$mimeMail2->Sender = $CH->emailAdmin;
$mimeMail2->FromName = "Cloud Server Cronjob";

$mimeMail2->Body = "<html><body>".$T."</body></html>";
$mimeMail2->IsHTML();

$mimeMail2->AltBody = "Diese Nachricht wird nur als HTML übertragen";

$mimeMail2->AddAddress($CH->emailAdmin);
try {
	$mimeMail2->Send();
} catch (phpmailerException $ex){
	#echo $ex->errorMessage();
	echo nl2br(print_r($mimeMail2->ErrorInfo, true))."\n";
	echo "Host: ".$mimeMail2->Host."\n";
	echo "Username: ".$mimeMail2->Username."\n";
}
$e->cleanUp();

?>