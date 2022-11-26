<?php

function getUserID()
{
	if (!isset($_SESSION["id"])) {
		return NULL;
	}
	return $_SESSION["id"];
}

function isAdmin() {
	if (!isset($_SESSION["isAdmin"])) {
		return false;
	}
	return $_SESSION["isAdmin"];
}
