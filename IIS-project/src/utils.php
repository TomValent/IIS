<?php

function getUserID(): mixed
{
	if (!isset($_SESSION["id"])) {
		return NULL;
	}
	return $_SESSION["id"];
}

function isAdmin(): bool
{
	if (!isset($_SESSION["isAdmin"])) {
		return false;
	}
	return $_SESSION["isAdmin"];
}

function displayName($name): string
{
	if ($name == NULL) {
		return "deleted";
	}
	return $name;
}
