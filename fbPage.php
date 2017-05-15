<?php
// Iniciar la sesión
session_start();

// Importar la libreria FBManager para Facebook
require_once(dirname(__FILE__) . "/socialMedia/LibFacebook/FBManager.php");

// Inicializar Facebook
$fbManager = new FBManager();

// Verificar si esta logueado
$state = $fbManager->isLogged();

if ($state["result"]) 
{
	// Obtener Información del Usuario
	$result = $fbManager->getUser();
	$user = $result["user"];
	
	// Obtener la lista de amigos e imprimir
	$response = $fbManager->getFriends();
	if($response["result"])
	{
		foreach($response["list"] as $friend)	
			echo $friend["name"] . "<br/>";
	}
} else
	header("Location: " . FB_LOGOUT_URL_CALLBACK);
	

// Verificar, si realizo la acción de postear
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $state["result"]) {
	$msg = $_POST["txtMessage"];
	$link = $_POST["txtLink"];
	$imgFile = $_FILES["txtFile"];
	
	// Postear en su propio muro
	$result = $fbManager->postWall($msg, $link, $imgFile);
	
	if ($result["result"])
		echo "SUCCESS POSTED!!";
	else
		echo "FAIL POST... " . $result["description"];
}

?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="js/jquery-1.11.2.min.js"></script>
	<script src="js/SocialMedia.js"></script>
	<title>Social Media</title>
</head>

<body>
		<form id="formMe" action="fbPage.php" method="POST" enctype="multipart/form-data">
			<span id=photo><img src="<?php echo $user["photoUser"]; ?>"></span><br/>
			<button id="btnDisconnectFB" type="button">Disconnect FB</button><br/>
			
			<label id="lblId">ID:</label>
			<span id="spanId"><?php echo $user["id"];  ?></span><br/>
		
			<label id="lblFullName">NAME:</label>
			<span id="spanFullName"><?php echo $user["name"];  ?></span><br/>
		
			<label id="lblEmail">EMAIL:</label>
			<span id="spanEmail"><?php echo $user["email"];  ?></span><br/>
		
			<label id="lblBirthday">BIRTHDAY:</label>
			<span id="spanBirthday"><?php echo $user["birthday"];  ?></span><br/>
			
			<label id="lblGender">GENDER:</label>
			<span id="spanGender"><?php echo $user["gender"];  ?></span><br/><br/><br/>
			
			MESSAGE POST: 	<input type="text" id="txtMessage" name="txtMessage" value="" /><br/>
			LINK: 			<input type="text" id="txtLink" name="txtLink" value="" /><br/>
			PHOTO: 			<input type="file" id="txtFile" name="txtFile" value="" /><br/>
			<input id="btnPost" type="submit" value="PUBLISH"><br/>
		</form>
</body>
</html>