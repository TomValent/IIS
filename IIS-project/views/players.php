<div class='right'>
	<button><a href='page'>Back to main page</a></button>
</div>
<?php
    $pdo = createDB();
    $q = $pdo->query("SELECT Name, MemberID, isAdmin from Member");
    $users = $q->fetchAll();
?>
<table>
    <td>Username</td>
    <td>Stats</td>
    <?php
	if ($_SESSION["isAdmin"]?? false) {
        echo "<td>Set admin rights</td>";
    }
    foreach ($users as $user) {
        $_GET["id"] = $user["MemberID"];
        echo "<tr>
                <td>". $user["Name"] ."</td>
                <td><button><a href='player?id=".$user["MemberID"]."' >Detail</a></button></td>";
        if ($_SESSION["isAdmin"]?? false) {
            $setAdmin =  $user["isAdmin"] ? 0 : 1;
            echo "<td><form method='post'>
                    <input type='hidden' name='setAdmin' value='". $setAdmin ."'>
                    <input type='hidden' name='id' value='". $user["MemberID"] ."'>";
            if ($user["MemberID"] != $_SESSION["id"]) {
				if ($user["isAdmin"]) {
					echo "<input type='submit' value='User is admin'>";
				} else {
					echo "<input type='submit' value='User is not admin'>";
				}
            }
            echo "</form></td>";
        }
        echo "</tr>";
    }

	if (isset($_POST["setAdmin"]) && isset($_POST["id"])) {
		$stmt = $pdo->prepare("UPDATE Member SET isAdmin = :isAdmin WHERE MemberID = :id");
		$stmt->execute(["isAdmin" => intval($_POST["setAdmin"]), "id" => $_POST["id"]]);
		unset($_POST["setAdmin"]);
	}
	?>
</table>