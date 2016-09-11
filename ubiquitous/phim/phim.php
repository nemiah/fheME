<?php
#session_name("phim_".sha1(__FILE__));
define("PHYNX_NO_SESSION_RELOCATION", true);

require "../../system/connect.php";

addClassPath(Util::getRootPath()."ubiquitous/phim");
$username = filter_input(INPUT_GET, "username");
$password = sha1(filter_input(INPUT_GET, "password"));

if(filter_input(INPUT_GET, "token")){
	$AC = anyC::get("phimUser", "phimUserToken", filter_input(INPUT_GET, "token"));
	$AC->addAssocV3("phimUserActive", "=", "1");
	$U = $AC->n();
	if($U != null){
		$AnyUser = anyC::getFirst("User");
		
		$Us = new Users();
		$login = $Us->doLogin(array(
			"loginUsername" => $AnyUser->A("username"),
			"loginSHAPassword" => $AnyUser->A("SHApassword"),
			"loginPWEncrypted" => 1
		));
		
		$user = new User($U->A("phimUserUserID"));
		$user->loadMe(false);
		
		$username = $user->A("username");
		$password = $user->A("SHApassword");
	}
}

$U = new Users();
$login = $U->doLogin(array(
	"loginUsername" => $username,
	"loginSHAPassword" => $password,
	"loginPWEncrypted" => 1,
	"anwendung" => "lightCRM"
));

if(!$login)
	emoFatalError ("Sorry, ich kenne dich nicht!", "Login fehlgeschlagen! Vermutlich sind die Zugangsdaten falsch.", "Login fehlgeschlagen", false);

$p = new mphimGUI();
$p->chatPopup("../");

?>