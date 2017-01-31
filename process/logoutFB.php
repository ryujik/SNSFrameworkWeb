<?php 

session_start();

require_once(dirname(__FILE__) . "/../socialMedia/LibFacebook/FBManager.php");

$fbManager = new FBManager();
$result = $fbManager->isLogged();

if($result["result"])
	$fbManager->Logout();
else 
	header("Location: " . FB_LOGOUT_URL_CALLBACK);
?>