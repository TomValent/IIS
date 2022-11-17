<?php
require_once "../IIS-project/inc/bootstrap.php";
require_once PRJ_DIR . '/src/router.php';

Router::route($_SERVER['REQUEST_URI']);
