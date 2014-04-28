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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
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

$e->useDefaultMySQLData();

$e->useAdminUser();

$CH = Util::getCloudHost();


$I = new mInstallation();
$data = $I->updateAllTables();

$T = new HTMLTable(2);
$T->setTableStyle("font-size:10px;font-family:sans-serif;");

$T->addColStyle(1, "vertical-align:top;");

foreach($data AS $k => $v)
	$T->addRow(array($k, "<pre>".trim($v)."</pre>"));


$mimeMail2 = new PHPMailer(true, "", true);
$mimeMail2->CharSet = "UTF-8";
$mimeMail2->Subject = "Installation Plugin";

$mimeMail2->From = $CH->emailAdmin;
$mimeMail2->Sender = $CH->emailAdmin;
$mimeMail2->FromName = "Cloud Server Cronjob";

$mimeMail2->Body = "<html><body>".$T."</body></html>";
$mimeMail2->IsHTML();

$mimeMail2->AltBody = "Diese Nachricht wird nur als HTML übertragen";

$mimeMail2->AddAddress($CH->emailAdmin);

if(!$mimeMail2->Send())
	throw new Exception ("E-Mail could not be sent!");

$e->cleanUp();

?>