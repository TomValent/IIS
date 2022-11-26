<?php

require_once "../IIS-project/inc/bootstrap.php";
require_once PRJ_DIR . '/controller/api/base-controller.php';
require_once PRJ_DIR . "/controller/api/tournament-controller.php";
require_once PRJ_DIR . "/controller/api/team-controller.php";
require_once PRJ_DIR . "/controller/api/user-controller.php";
require_once PRJ_DIR . "/controller/api/match-controller.php";

function getController($method)
{
	switch ($method) {
		case 'user':
			return new UserController();
		case 'tournament':
			return new TournamentController();
        case 'team':
            return new TeamController();
		case 'match':
			return new MatchController();
		default:
			header("HTTP/1.1 404 Not Found");
			exit();
	}
}

$uri = parseUrl($_SERVER['REQUEST_URI']);
if (count($uri) < 3) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$controller = getController($uri[1]);
$method = $uri[2].'Action';
if (!method_exists($controller, $method)) {
	echo "method not found";
    header("HTTP/1.1 404 Not Found");
    exit();
}

$controller->invoke($method);

