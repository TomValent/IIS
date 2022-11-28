<?php

$pdo = createDB();

$q = $pdo->query('SELECT TournamentID, Name, type, ApprovalState FROM Tournament ORDER BY StartTime');
$data = $q->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION["login"])) {
    echo "<a href='newTournament'><button>Create Tournament</button></a>";
}
echo '<h4>Tournaments:</h4>';

function list_tournaments($data, $state) {
	foreach ($data as &$row) {
		if ($row['ApprovalState'] == $state) {
			$id = $row['TournamentID'];
			echo '<div class="tournament_list_row">';
			echo '<a href="tournament?id=' . $id . '">'.$row['Name'].'</a>';
			if ($state == 'created') {
				echo '<span class="label">PENDING APPROVAL</span>';
			}
			echo '</div><br>';
		}
	}
}

if (isAdmin()) {
	list_tournaments($data, 'created');
}

list_tournaments($data, 'approved');

$user_id = getUserID();

if (!isAdmin() && $user_id !== NULL) {
	list_tournaments($data, 'created');
}