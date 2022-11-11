<?php
	session_start();
	$_SESSION["guest"] = "true";
	header("Location: http://{$_SERVER["SERVER_NAME"]}:{$_SERVER["SERVER_PORT"]}/page.php");
?>