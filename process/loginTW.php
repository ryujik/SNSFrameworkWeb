<?php 

session_start();

require_once(dirname(__FILE__) . "/../socialMedia/LibTwitter/TWManager.php");

$twManager = new TWManager();
$result = $twManager->isLogged();

if(!$result["result"]) 
	$twManager->Login();
else 
	header("Location: " . TW_LOGOUT_URL_CALLBACK);

?>