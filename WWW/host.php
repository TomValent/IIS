<?php
	session_start();
	$_SESSION["host"] = "true";
	error_log($_SESSION["host"]);
	header("Location: http://{$_SERVER["SERVER_NAME"]}:{$_SERVER["SERVER_PORT"]}/page.php");
?>