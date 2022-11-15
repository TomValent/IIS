<?php

require_once "../IIS-project/inc/bootstrap.php";
require_once PRJ_DIR . '/controller/api/base-controller.php';
require_once PRJ_DIR . "/controller/api/tournament-controller.php";
require_once PRJ_DIR . "/controller/api/user-controller.php";

function getController($method)
{
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

if (count($uri) < 3) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
// remove ""
array_shift($uri);

if (str_starts_with($uri[0], "~")) {
	// remove login if on eva
    array_shift($uri);
}
// remove api.php
array_shift($uri);

if (count($uri) < 2) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$controller = getController($uri[0]);
$method = $uri[1].'Action';
if (!method_exists($controller, $method)) {
	echo "method not found";
    header("HTTP/1.1 404 Not Found");
    exit();
}

$controller->invoke($method);

