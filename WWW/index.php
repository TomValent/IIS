<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="button_container">
    <a href="register.php">Register</a>
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
    die();
}

?>
</body>
</html>
