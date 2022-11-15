<?php
require_once __DIR__.'/../vendor/autoload.php';
use Dotenv\Dotenv;

function loadDotenv(): ?Dotenv
{
	static $dotenv = null;
	if (!$dotenv) {
		$dotenv = Dotenv::createImmutable(__DIR__.'/..');
		$dotenv->load();
	}
	return $dotenv;
}