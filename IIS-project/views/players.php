<div class='right'>
	<button><a href='/index.php/page'>Back to main page</a></button>
</div>
<?php
    $pdo = createDB();
    $q = $pdo->query("SELECT Name from Member");
    $users = $q->fetchAll();
?>
<table>
    <td>Username</td>
    <td>Stats - TODO</td>
    <?php foreach ($users as $user) {
        echo "<tr>
                <td>". $user["Name"] ."</td>
                <td>0</td>
              </tr>";
    } ?>
</table>