<?php

class Router {

	private static function error(): void
	{
		header("HTTP/1.1 404 Not Found");
		echo "404 Not Found";
		die();
	}

	private static function show($file): void
	{
		// begin
		echo
		'<!DOCTYPE html>
<html>
	<head>
		<link rel="icon" type="image/png" href="/assets/favicon.ico">
		<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
		<link rel="stylesheet" href="/CSS/styles.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
		<script>
			function logout() {
                 $.get("/api.php/user/logout").done(function() {
                     if (typeof onLogout !== "undefined") onLogout();                     
                 }).fail(function(data, textStatus, xhr) {
                     console.log("request failed");
                 });
			}
            
            function get(url, done) {
                $.ajax({
                	url: url
                })
                .done(done)
                .fail(function(data, textStatus, xhr) {
                     console.log("request failed");
                 });
            }            
            
		</script>
	</head>
	<body>';
		$user = "(guest)";
		if (isset($_SESSION["login"])) {
			$user = $_SESSION["login"];
		}
		echo "[DBG] User: " . $user;
		if (isset($_SESSION["login"])) {
			echo "<button onclick='logout()'>Logout</button>";
		}
		echo "<br><br>";

		// page content
		require VIEWS_DIR."/".$file;
		// end
		echo
		'	</body>
</html>';

	}

	public static function route($request): void
	{
		if ($request == "/" || $request == "/index.php") {
			Router::show("index.php");
		}
		else {

			if (!str_starts_with($request, "/index.php/")) {
				Router::error();
			}
			$request = substr($request, strlen("/index.php/"));

			if (strpos($request, ".")) {
				Router::error();
			}

			$target = $request;
			$pos = strpos($request , "?");
			if (!$pos) {
				$pos = strpos($request , "/");
			}
			if ($pos) {
				$target = substr($request, 0, $pos);
			}
			$target .= ".php";

			foreach (new DirectoryIterator(VIEWS_DIR) as $fileInfo) {
				if(!$fileInfo->isDot()) {
					$file = $fileInfo->getFilename();
					if (str_contains($target, $file)) {
						Router::show($file);
					}
				}
			}
		}
	}

}