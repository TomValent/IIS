<?php

require_once "../src/database.php";

try {
	$pdo = Database::getInstance()->getPDO();

	$stmt = $pdo->prepare("DELETE FROM Member WHERE Login='admin';");
	$stmt->execute();

	$stmt = $pdo->prepare("INSERT INTO Member VALUES (default, 'admin', 'admin', :password, 1);");
	$stmt->execute(['password' => password_hash("admin", PASSWORD_DEFAULT)]);

	error_log("admin account created.");
}
catch(PDOException|Exception $e) {
	error_log($e->getMessage());
}
