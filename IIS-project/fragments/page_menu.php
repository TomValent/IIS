<?php

const PAGES = ["/page", "/tournaments", "/tournament", "/newTournament", "/players", "/teams", "/player", "/editAccount"];

$user = "(guest)";
if (isset($_SESSION["login"])) {
	$user = $_SESSION["username"];
}
if (isset($_SESSION["login"])) {
	$user = $_SESSION["username"];
	echo "<div class='right profile'><a href='player?id=". $_SESSION["id"] ."'>User: " . $user . "</a></div>";
} else {
	 echo "<div class='right'>User: " . $user . "</div>";
}

$showLogout = false;
foreach (PAGES as $page) {
	if (strpos($_SERVER["HTTP_REFERER"], $page)) {
		$showLogout = true;
	}
}

if (isset($_SESSION["login"]) && isset($_SERVER["HTTP_REFERER"]) && $showLogout) {
	 echo "  <div class='button_container right'>
				 <a onclick='logout()'><button>Log out</button></a>
			 </div>
	 ";
} else {
	echo "<div class='right'>
			  <a href='index'><button>Back to welcome page</button></a>
		  </div>";
}

if (isset($_GET["url"])) {
	$url = parseUrl($_GET["url"]);
}