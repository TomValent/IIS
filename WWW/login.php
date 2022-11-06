<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="CSS/styles.css">
    </head>
    <body>
        <div class="return">
            <button><a href="index.php">Go back</a></button>
        </div>
        <?php
            require '../IIS-project/src/database.php';

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                error_log("got post");
                try {
                    $pdo = createDB();

                    $sql = "SELECT login, password FROM Member";
                    $data = $pdo->query($sql)->fetchAll();

                    session_start();
                    foreach ($data as $user) {
                        if (isset($_POST["login"]) && isset($_POST["pass"])) {
							if ($user["login"] === $_POST["login"]) {
                                if ($user["password"] === $_POST["pass"]) {
									$_SESSION["login"] = $_POST["login"];
									header("Location: http://{$_SERVER["SERVER_NAME"]}:{$_SERVER["SERVER_PORT"]}/page.php");
                                } else {
                                    echo "Wrong password";
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
            <button type="submit">Submit</button>
        </form>
        <div class="alternative">
            <p>Do you want to create new account?</p>
            <button><a href="register.php">Register</a></button>
        </div>
    </body>