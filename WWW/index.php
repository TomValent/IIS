<?php
if ($_SERVER['REQUEST_URI'] == "/" || $_SERVER['REQUEST_URI'] == "/index.php") {
	header("Location: IIS/index.php");
	die();
}

