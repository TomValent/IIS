<html>
    <head>
    </head>
    <body>
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
        <?php if (!isset($_SESSION["login"])): ?>
            <p style="text-align: center">Nejsi prihlaseny :)</p></br>
        <?php endif ?>
    </body>
</html>