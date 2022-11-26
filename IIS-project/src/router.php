<?php

class Router {

	private static function error(): void
	{
		header("HTTP/1.1 404 Not Found");
		echo "404 Not Found";
		die();
	}

	private static function show($file, $wrap): void
	{
		// website start
		if ($wrap) {
			echo "<!DOCTYPE html>";
			echo "<html>";
			require_once "website_header.php";
			?>
			<body>
            <div class="menu" id="page-menu"></div>
            <div id="inactivityModal" class="modal">
				<div class="modal-content" style="justify-content: center;">
                    <span style="white-space: nowrap;">you will be logged out in 1 minute due to inactivity</span>
                    <span class="close">cancel</span>
				</div>
			</div>
			<?php
		}
		// page content
		require_once $file;
		// website end
		if ($wrap) {
			echo "	</body>";
			echo "</html>";
		}
		exit();
	}

	private static function find($dir, $target, $wrap) {
		foreach (new DirectoryIterator($dir) as $fileInfo) {
			if(!$fileInfo->isDot()) {
				$file = $fileInfo->getFilename();
				if ($target == $file) {
					Router::show($dir ."/". $file, $wrap);
				}
			}
		}
	}

	public static function route($request): void
	{
		if(count($request) == 0) {
			Router::show(VIEWS_DIR."/index.php", true);
		}
		if(count($request) == 1 && ($request[0] == "" || $request[0] == "index.php")) {
			Router::show(VIEWS_DIR."/index.php", true);
		}
		if ($request[0] != "index.php") {
			Router::error();
		}
		if (count($request) > 2 && $request[1] == "frags") {
			$target = $request[2] . ".php";
			Router::find(FRAGS_DIR, $target, false);
		}
		else {
			$target = $request[1] . ".php";
			Router::find(VIEWS_DIR, $target, true);
		}

		Router::error();
	}
}