<html>
    <head>
    </head>
    <body>
        <nav>
            <div class="menu-item">
                <a href="../index.php/players">Players</a>
            </div>
            <div class="menu-item">
                <a href="../index.php/teams">Teams</a>
            </div>
            <div class="menu-item">
                <a href="tournaments">Tournaments</a>
            </div>
        </nav></br></br></br></br></br></br></br></br></br>
        <?php if (!isset($_SESSION["login"])): ?>
            <p class="center">You are not logged in :)</br>You will not be able to see everything</p></br>
        <?php else: ?>
            <p class="center">You are logged in</br>Enjoy :)</p>
        <?php endif ?>
    </body>
</html>