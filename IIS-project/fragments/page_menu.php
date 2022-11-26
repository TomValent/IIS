<?php

const PAGES = ["/page", "/tournaments", "/tournament", "/newTournament", "/players", "/teams"];

$user = "(guest)";
if (isset($_SESSION["login"])) {
	$user = $_SESSION["username"];
}
echo "<div class='right'>User: " . $user . "</div>";

if (isset($_SESSION["login"])) {
	echo "<div class='right'>";
	echo "<button onclick='logout()'>Log out</button>";
	echo "</div>";
} else {
	echo "<div class='right'>
            		<a href='../index.php'><button>Back to welcome page</button></a>
        		</div>";
}

if (isset($_GET["url"])) {
	$url = parseUrl($_GET["url"]);

}