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

                    $data = [
                        'username' => $_POST['username'],
                        'login' => $_POST['login'],
                        'password' => $_POST['pass']
                    ];

                    $sql = "INSERT INTO Member VALUES (default, :username, :login, :password, 0)";
                    $stmt= $pdo->prepare($sql);
                    $stmt->execute($data);
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
            }
        ?>

        <form class="form" method="post">
            <label for="username">Username</label></br>
            <input class="input" type="text" id="username" name="username"><br><br>
            <label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"><br><br>
            <label for="pass">Password</label></br>
            <input class="input" type="password" id="pass" name="pass"><br><br>
            <button type="submit"><a href="login.php">Submit</a></button>
        </form>
        <div class="alternative">
            <p>Do you want to log in?</p>
            <button><a href="login.php">Log in</a></button>
        </div>
    </body>