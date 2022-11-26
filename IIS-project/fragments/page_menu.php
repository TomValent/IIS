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

if (isset($_SESSION["login"]) && isset($_SERVER["PATH_INFO"]) && in_array($_SERVER["PATH_INFO"], PAGES)) {
	 echo "  <div class='button_container right'>
				 <button><a onclick='logout()'>Log out</a></button>
			 </div>
	 ";
} else {
	echo "<div class='right'>
			  <button><a href='index'>Back to welcome page</a></button>
		  </div>";
}

if (isset($_GET["url"])) {
	$url = parseUrl($_GET["url"]);
}