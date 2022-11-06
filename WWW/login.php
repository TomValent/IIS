<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="CSS/styles.css">
    </head>
    <body>
        <?php
            require '../IIS-project/src/database.php';

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                error_log("got post");
                try {
                    $pdo = createDB();

                    $sql = "SELECT login, password FROM Member";
                    $data = $pdo->query($sql)->fetchAll();

                    foreach ($data as $user) {
                        if (isset($_POST["login"]) && isset($_POST["pass"])) {
							if ($user["login"] === $_POST["login"]) {
                                if ($user["password"] === $_POST["pass"]) {
                                    var_dump("Logged in");
                                } else {
                                    var_dump("Wrong password");
                                    break;
                                }
							}
						}
                    }

                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
            }
        ?>

        <form class="form" method="post">
            <label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"><br><br>
            <label for="pass">Password</label></br>
            <input class="input" type="password" id="pass" name="pass"><br><br>
            <input class="button" href="login.php" type="submit" value="Submit">
            <button><a href="register.php">Register</a></button>
        </form>
    </body>