<div>
<?php require_once "tournament_participants.php" ?>
</div>
<?php
if (!isset($_GET['id'])) {
    exit();
}

$id = $_GET['id'];

$tournament = new Tournament($id);
if (!$tournament->exists()) {
    exit();
}

echo '<div id="tournament-detail">';
try {
	$pdo = createDB();
    $name = NULL;
	$stmt = $pdo->prepare("SELECT Name FROM Member WHERE MemberID=?;");
	$stmt->execute([$tournament['CreatorID']]);
	$creator = $stmt->fetch();
    if ($creator) {
        $name = $creator['Name'];
    }
    if ($name !== NULL) {
		echo "<a href='player?id=" . $tournament['CreatorID'] . "'>";
	}
	echo '<span>Created by '.displayName($name).'</span>';
	if ($name !== NULL) {
	    echo "</a>";
	}
	$date = date_create($tournament['StartTime']);
	echo '<span>Starting '.date_format($date, 'H:i d.m.y').'</span>';
    echo '<span>Price '.$tournament['Price'].'</span>';
	echo '<span>Required participants: '.$tournament['ParticipantCount'].'</span>';
    if ($tournament['type'] == 'team') {
		echo '<span>Minimum allowed team members: '.$tournament['MinCountTeam'].'</span>';
		echo '<span>Maximum allowed team members: '.$tournament['MaxCountTeam'].'</span>';
    }
    echo '<p>'.$tournament['Description'].'</p>';
}
catch (PDOException) {

}
echo '</div>';

