<?php
// Iniciar la sessión
session_start();

// Importar la libreria de TWManager para Twitter
require_once(dirname(__FILE__) . "/socialMedia/LibTwitter/TWManager.php");

// Inicializar Twitter
$twManger = new TWManager();

// Verificar si esta logueado
$state = $twManger->isLogged();

if( $state["result"] )
{
	// Obtener información del usuario
	$user = $twManger->getUser();
	$user = $user["user"];
} else
	header("Location: " . TW_LOGOUT_URL_CALLBACK);

// Verificar si realizo la acción de postear
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $state["result"]) {
	$msg = $_POST["txtMessage"];
	$imgFile = $_FILES["txtFile"];
	
	// Twittear
	$result = $twManger->postWall($msg, $imgFile);
	
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
	<form id="formMe" action="twPage.php" method="POST" enctype="multipart/form-data">
		<span id=photo><img src="<?php echo $user["photoUser"]; ?>"></span><br/>
		<button id="btnDisconnectTW" type="button">Disconnect TW</button><br/>
	
		<label id="lblId">ID:</label>
		<span id="spanId"><?php echo $user["id"];  ?></span><br/>
		
		<label id="lblFullName">NAME:</label>
		<span id="spanFullName"><?php echo $user["name"];  ?></span><br/>
	
		<label id="lblUsername">USERNAME:</label>
		<span id="spanUsername"><?php echo $user["username"];  ?></span><br/>
	
		<label id="lblLocation">LOCATION:</label>
		<span id="spanBirthday"><?php echo $user["location"];  ?></span><br/>
		
		<label id="lblDescription">DESCRIPTION:</label>
		<span id="spanDescription"><?php echo $user["description"];  ?></span><br/><br/><br/>
	
		<label id="lblUrl">URL:</label>
		<span id="spanUrl"><?php echo $user["url"];  ?></span><br/><br/><br/>
		
		MESSAGE POST: 	<input type="text" id="txtMessage" name="txtMessage" value="" /><br/>
		PHOTO: 			<input type="file" id="txtFile" name="txtFile" value="" /><br/>
		<input id="btnPost" type="submit" value="PUBLISH"><br/>
	</form>	
</body>
</html>