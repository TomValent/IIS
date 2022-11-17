<?php
const PRJ_DIR = __DIR__ . "/../";
const VIEWS_DIR =  PRJ_DIR . "/views";

require_once PRJ_DIR . '/src/database.php';

function url($url)
{
	return "\"" . $GLOBALS["url_prefix"] . $url . "\"";
}

if (!isset($GLOBALS["url_prefix"]))
{
	$GLOBALS["url_prefix"] = "";
	loadDotenv();
	if (isset($_ENV['URL_PREFIX'])) {
		$GLOBALS["url_prefix"] = $_ENV['URL_PREFIX'];
	}
}

if (session_status() === PHP_SESSION_NONE)
{
	error_log("START SESSION");
	session_start();
}
