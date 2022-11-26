<?php

if (!isset($_GET['id'])) {
	exit();
}

$id = $_GET['id'];

$tournament = new Tournament($id);
if (!$tournament->exists()) {
	exit();
}

$user_id = getUserID();
$tournament_owner = $user_id === $tournament["CreatorID"];

echo '<div class="tournament-teams">';
if ($user_id !== NULL) {
	if ($tournament['type'] == 'team') {
		$teams = Database::getInstance()->getTeamParticipantsWithLeader($id, NULL, $user_id);
		if (count($teams) > 0) {
			echo '<div>';
			echo 'My participating teams:<br>';
			foreach ($teams as $t) {
				echo $t['Name'];
				if ($t['AcceptanceState'] == 'pending') {
					echo '<span class="label pending">PENDING</span>';
				}
				if ($tournament['ProgressState'] == 'unstarted') {
					echo '<button onclick="leaveTournament(' . $t['TeamID'] . ')">Leave</button>';
				}
				echo '<br>';
			}
			echo '</div>';
		}
	}
}
if ($tournament->isTeam()) {
	$pending = Database::getInstance()->getTeamParticipants($id, 'pending');
	$accepted = Database::getInstance()->getTeamParticipants($id,'approved');
}
else {
	$pending = Database::getInstance()->getMemberParticipants($id,'pending');
	$accepted = Database::getInstance()->getMemberParticipants($id,'approved');
}

$total = count($accepted);
if ($tournament_owner) {
	$total += count($pending);
}

if ($total == 0) {
	echo '<br>No participants<br>';
}
else {

	echo '<br>Participants<br>';

	if (($tournament_owner) && $tournament['ProgressState'] == 'unstarted') {
		foreach ($pending as $p) {
			echo $p['Name'];
			echo '<span class="label pending">PENDING</span>';
			echo '<button onclick="acceptParticipant(' . $p['TournamentParticipantID'] . ')">Accept</button>';
			echo '<button onclick="kickParticipant(' . $p['TournamentParticipantID'] . ')">Reject</button>';
			echo '<br>';
		}
	}

	foreach ($accepted as $p) {
		echo $p['Name'];
		if ($tournament_owner && $tournament['ProgressState'] == 'unstarted') {
			echo '<button onclick="revokeParticipant(' . $p['TournamentParticipantID'] . ')">Revoke</button>';
			echo '<button onclick="kickParticipant(' . $p['TournamentParticipantID'] . ')">Kick</button>';
		}
		echo '<br>';
	}
}
echo '</div>';