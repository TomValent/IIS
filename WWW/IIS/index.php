<?php
require_once "../../IIS-project/inc/bootstrap.php";
require_once "../../IIS-project/src/router.php";

error_log("[router] ".parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
Router::route(parseUrl($_SERVER['REQUEST_URI']));
