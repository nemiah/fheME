<?php

define("PHYNX_NO_SESSION_RELOCATION", true);

require "../../system/connect.php";

addClassPath(Util::getRootPath()."ubiquitous/phim");

$T = mUserdata::getGlobalSettingValue("phimServerToken");
if(!$T)
	die("<phynx><phim><token>unknown</token></phim></phynx>");

if($T !== filter_input(INPUT_GET, "token"))
	die("<phynx><phim><token>unknown</token></phim></phynx>");

$U = anyC::getFirst("phimUser", "phimUserSystemName", str_replace("\\", "/", filter_input(INPUT_GET, "user")));
if($U == null){
	$F = new Factory("phimUser");
	$F->sA("phimUserSystemName", str_replace("\\", "/", filter_input(INPUT_GET, "user")));
	$F->store();
	
	die("<phynx><phim><token>unknown</token></phim></phynx>");
}

echo "<phynx><phim><token>".$U->A("phimUserToken")."</token></phim></phynx>";


?>