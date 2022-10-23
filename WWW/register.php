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

<form method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username"><br><br>
    <label for="login">Login:</label>
    <input type="text" id="login" name="login"><br><br>
    <label for="pass">Password:</label>
    <input type="text" id="pass" name="pass"><br><br>
    <input type="submit" value="Submit">
</form>
