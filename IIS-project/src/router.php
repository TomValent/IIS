<?php

const PAGES = ["/page", "/tournaments", "/tournament", "/newTournament"];

class Router {

	private static function error(): void
	{
		header("HTTP/1.1 404 Not Found");
		echo "404 Not Found";
		die();
	}

	private static function show($file): void
	{
		// website start
		echo "<!DOCTYPE html>";
		echo "<html>";
		require_once "website_header.php";
		echo "	<body>";
		$user = "(guest)";
		if (isset($_SESSION["login"])) {
			$user = $_SESSION["username"];
		}
		echo "<div class='right'>User: " . $user . "</div>";

		if (isset($_SESSION["login"]) && isset($_SERVER["PATH_INFO"]) && in_array($_SERVER["PATH_INFO"], PAGES)) {
			echo "	<div class='button_container right'>
            			<button><a href='/index.php' onclick='logout()'>Log out</a></button>
        			</div>
			";
		} else {
			echo "<div class='right'>
            		<button><a href='/index.php'>Go back</a></button>
        		</div>";
		}

		// page content
		require VIEWS_DIR."/".$file;
		// website end
		echo "	</body>";
		echo "</html>";

	}

	public static function route($request): void
	{
		if (str_starts_with($request, "/~") && strlen($request) >= 10) {
			$request = substr($request, 10);
		}

		if (strlen($request) == 0 || $request == "/" || $request == "/index.php") {
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