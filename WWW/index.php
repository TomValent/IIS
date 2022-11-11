<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="CSS/styles.css">
    </head>
    <body>
        <div id="welcome">
            <h1>Welcome in our page</h1> <!-- TODO  -->
        </div>
        <div class="authenticate">
            <p>Are you registered?</p>
            <div class="button_container">
                <button><a href="login.php">Log in</a></button>
            </div>
            <p>Are you new here?</p>
            <div class="button_container">
                <button><a href="register.php">Create new account</a></button>
                <button><a href="guest.php">Continue as guest</a></button>
            </div>
        </div>

        <?php
            require '../IIS-project/src/database.php';

            try {
                $pdo = createDB();

                $q = $pdo->query('SELECT * FROM Member');
                $data = $q->fetchAll(PDO::FETCH_ASSOC);
                foreach($data as $row) {
                    echo "u: " . $row['Name'] . "<br>";
                }

            } catch (PDOException $e) {
                echo "Connection error: ".$e->getMessage();
                exit;
            }

        ?>
    </body>
</html>
