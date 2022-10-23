<?php

use Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

static $dotenv;

if ($dotenv === null) {
    try {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/..");
        $dotenv->load();
    } catch (Exception $e) {
        echo "Server error: " . $e->getMessage();
        die();
    }
}

function createDB() {
    return new PDO($_ENV['MYSQL_DSN'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS']);
}