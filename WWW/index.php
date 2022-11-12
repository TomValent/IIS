<?php

require_once "../IIS-project/inc/bootstrap.php";

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
Router::route($_SERVER['REQUEST_URI']);
