<div class='right'>
    <button><a href='players'>Back to list of players</a></button>
</div>
<?php
    if ($_GET["id"] == NULL) {
		echo "You have no profile";
		exit;
    }

    $pdo = createDB();
    $q = $pdo->prepare("SELECT * FROM Member WHERE MemberID = :id");
    $q->execute(["id" => $_GET["id"]]);
    $user = $q->fetch(PDO::FETCH_NAMED);

    $stmt = $pdo->prepare("SELECT * FROM Matches m left join Tournament t on m.TournamentID = t.TournamentID left join TournamentParticipant tp on t.TournamentID = tp.TournamentID where t.type = 'member' and tp.MemberID = :id");
    $stmt->execute(["id" => $_GET["id"]]);
    $stats = $stmt->fetchAll(PDO::FETCH_NAMED);
?>

<span>Profile of <?php echo $user["Name"]; ?></span></br>
<div class="center margin" style="margin-top:100px;">Matches stats</div>
<table style="margin-top:5px;">
    <td>
        Tournament
    </td>
    <td>
        Player 1
    </td>
    <td>
        Player 2
    </td>
    <td>
        Score 1
    </td>
    <td>
        Score 2
    </td>
    <?php foreach ($stats as $row) {
        $getPlayer1 = $pdo->prepare("SELECT Name FROM Member WHERE MemberID = :id");
        $getPlayer1->execute(["id" => $row["Member1ID"]]);
        $player1 = $getPlayer1->fetch(PDO::FETCH_NAMED);

        $getPlayer2 = $pdo->prepare("SELECT Name FROM Member WHERE MemberID = :id");
        $getPlayer2->execute(["id" => $row["Member2ID"]]);
        $player2 = $getPlayer2->fetch(PDO::FETCH_NAMED);
    ?>
    <tr>
        <td>
            <?php echo $row["Name"] ?>
        </td>
        <td>
			<?php echo $player1["Name"] ?>
        </td>
        <td>
			<?php echo $player2["Name"] ?>
        </td>
        <td>
			<?php echo $row["Points1"] ?>
        </td>
        <td>
			<?php echo $row["Points2"] ?>
        </td>
    </tr>
    <?php } ?>
</table>
<script>
    function deleteMember(id) {
        let data = {
            id: id
        }
        api.post({
            url: <?php echo url("/api.php/user/delete") ?>,
            data: data,
        })
    }
</script>
<?php
    if (!empty($_GET["id"]) && $_SESSION["id"] === intval($_GET["id"])) {
		$_GET["edit"] = "true";
        echo "<div class='right'><button><a href='editAccount?id=". $_GET["id"] ."&edit=true'>Edit account</a></button></div>";
        echo "<div class='right'><button onclick='deleteMember(" . $_GET["id"] . ")'>Delete account</button></div>";
    }
?>