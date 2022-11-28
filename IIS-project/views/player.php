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

    $stmt = $pdo->prepare("SELECT * FROM Matches m left join Tournament t on m.TournamentID = t.TournamentID ".
                                    "left join TournamentParticipant tp on t.TournamentID = tp.TournamentID ".
                                    "where t.type = 'member' and tp.MemberID = :id");
    $stmt->execute(["id" => $_GET["id"]]);
    $stats = $stmt->fetchAll(PDO::FETCH_NAMED);

    $q2 = $pdo->prepare("SELECT m.MatchID, m.Points1, m.Points2, t.Name, te.Name, te.TeamID FROM Matches m left join Tournament t on m.TournamentID = t.TournamentID ".
                                    "left join TournamentParticipant tp on t.TournamentID = tp.TournamentID ".
                                    "left join Team te on te.TeamID = tp.TeamID ".
                                    "left join MemberTeam mt on mt.TeamID = te.TeamID ".
                                    "where t.type = 'team' and mt.MemberID = :id");
    $q2->execute(["id" => $_GET["id"]]);
    $statsT = $q2->fetchAll(PDO::FETCH_NAMED);
?>

<span>Profile of <?php echo $user["Name"] ?? ""; ?></span></br><hr>
<?php
if (!empty($_GET["id"]) && !empty($_SESSION["id"]) && $_SESSION["id"] == intval($_GET["id"])) {
	$_GET["edit"] = "true";
	echo "<div class='right'><button><a href='editAccount?id=". $_GET["id"] ."&edit=true'>Edit account</a></button></div>";
	echo "<div class='right'><button onclick='deleteMember(" . $_GET["id"] . ")'>Delete account</button></div>";
} else if (!empty($_GET["id"]) && !empty($_SESSION["isAdmin"])) {
	echo "<div class='right'><button onclick='deleteMember(" . $_GET["id"] . ")'>Delete account</button></div>";
}
?>
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
</br></br>
<table style="margin-top:5px;">
    <td>
        Tournament
    </td>
    <td>
        Team 1
    </td>
    <td>
        Team 2
    </td>
    <td>
        Score 1
    </td>
    <td>
        Score 2
    </td>
    <?php foreach ($statsT as $row) {
        $getID = $pdo->prepare("SELECT Team1ID, Team2ID FROM Matches WHERE MatchID = :id");
		$getID->execute(["id" => $row["MatchID"]]);
        $teamID = $getID->fetch(PDO::FETCH_NAMED);
        $id2 = 0;

        if ($teamID["Team1ID"] == $row["TeamID"]) {
           $id2 =  $teamID["Team2ID"];
        } else {
            $id2 = $teamID["Team1ID"];
        }

		$getTeam2 = $pdo->prepare("SELECT Name FROM Team WHERE TeamID = :id");
		$getTeam2->execute(["id" => $id2]);
		$team2 = $getTeam2->fetch(PDO::FETCH_NAMED);
    ?>
        <tr>
            <td>
				<?php echo $row["Name"][0] ?>
            </td>
            <td>
				<?php echo $row["Name"][1] ?>
            </td>
            <td>
				<?php echo $team2["Name"] ?>
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