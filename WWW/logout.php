<!DOCTYPE html>
<html>
	<head>
        <link rel="stylesheet" href="CSS/styles.css">
	</head>
	<body>
	<div class="button_container right">
		<?php
		    session_start();
            unset($_SESSION["login"]);
            session_destroy();
		    header('Location: /index.php');
		?>
	</div>
	</body>
</html>
