<?php
const PRJ_DIR = __DIR__ . "/../";
const VIEWS_DIR =  PRJ_DIR . "/views";
const FRAGS_DIR =  PRJ_DIR . "/fragments";

require_once PRJ_DIR . '/src/database.php';
require_once PRJ_DIR . '/src/tournament_entity.php';
require_once PRJ_DIR . '/src/utils.php';

function url($url): string
{
	return "\"" . $GLOBALS["url_prefix"] . $url . "\"";
}

function parseUrl($uri)
{
	$uri = parse_url($uri, PHP_URL_PATH);
	$uri = explode('/', $uri);

	if (count($uri) < 2) {
		header("HTTP/1.1 404 Not Found");
		exit();
	}
	// remove root /
	array_shift($uri);
    if ("/".$uri[0] == $GLOBALS["url_prefix"]) {
		// remove login if on eva
		array_shift($uri);
	}
    return $uri;
}

if (!isset($GLOBALS["url_prefix"]))
{
	loadDotenv();
	if (!isset($_ENV['URL_PREFIX'])) {
		$GLOBALS["url_prefix"] = "";
	}
	else {
		$GLOBALS["url_prefix"] = $_ENV['URL_PREFIX'];
	}
}

if (session_status() === PHP_SESSION_NONE)
{
	error_log("START SESSION");
	session_start();
}
