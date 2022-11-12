<?php
require "../IIS-project/inc/bootstrap.php";
require PRJ_DIR . "/controller/api/tournament-controller.php";
require PRJ_DIR . "/controller/api/user-controller.php";

function getController($method) {
	switch ($method) {
		case 'user':
			return new UserController();
		case 'tournament':
			return new TournamentController();
		default:
			header("HTTP/1.1 404 Not Found");
			exit();
	}
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if (!isset($uri[2]) || !isset($uri[3])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$controller = getController($uri[2]);
$method = $uri[3].'Action';
if (!method_exists($controller, $method)) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$controller->invoke($method);
