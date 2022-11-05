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

                    $data = [
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
            <label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"><br><br>
            <label for="pass">Password</label></br>
            <input class="input" type="text" id="pass" name="pass"><br><br>
            <input class="button" type="submit" value="Submit">
        </form>
    </body>