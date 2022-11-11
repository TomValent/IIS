<!DOCTYPE html>
<html>
	<head>
        <link rel="stylesheet" href="CSS/styles.css">
	</head>
	<body>
	<div class="button_container right">
		<?php
		    session_start();
            if (isset($_SESSION["login"])) {
				unset($_SESSION["login"]);
			}
            if (isset($_SESSION["host"])) {
				unset($_SESSION["host"]);
			}
            session_destroy();
		    header('Location: /index.php');
		?>
	</div>
	</body>
</html>
