	    <?php

		if (!isset($_SESSION["login"]) && !isset($_SESSION["guest"])) {
				header("Location: login");
			}

//			if (isset($_SESSION['previous'])) {		//TODO zistit na FE (asi javascript) ci doslo k presmerovaniu a ukoncit session a toto zmazat xd
//				if (basename($_SERVER['PHP_SELF']) !== "page.php" && $_SESSION["previous"] !== "login.php") {
//					unset($_SESSION["login"]);
//					session_destroy();
//				}
//			}
		?>

        <div class="button_container right">
            <button onclick="logout()">Log out</a></button>
        </div>
        <nav>
            <div class="menu-item">
                <a href="player-list.php">Players</a>
            </div>
            <div class="menu-item">
                <a href="team-list.php">Teams</a>
            </div>
            <div class="menu-item">
                <a href="tournaments">Tournaments</a>
            </div>
        </nav></br></br></br></br></br></br></br></br></br>
		<?php if (isset($_SESSION["guest"])): ?>
            <p style="text-align: center">Nejsi prihlaseny :)</p></br>
		<?php endif ?>
	</body>