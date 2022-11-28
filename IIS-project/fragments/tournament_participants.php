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

function printName($team) {
	echo '<a href="team?id='. $team['TeamID'] .'">';
	echo $team['Name'];
	echo '</a>';
}

echo '<div class="tournament-participants">';
if ($user_id !== NULL) {
	if ($tournament['type'] == 'team') {
		$teams = Database::getInstance()->getTeamParticipantsWithLeader($id, NULL, $user_id);
		if (count($teams) > 0) {
			echo '<div>';
			echo 'My participating teams:<br>';
			echo '<div class="tournament-teams">';
			foreach ($teams as $t) {
				echo '<div>';
				printName($t);
				if ($t['AcceptanceState'] == 'pending') {
					echo '<span class="label pending">PENDING</span>';
				}
				if ($tournament['ProgressState'] == 'unstarted') {
					echo '<button onclick="leaveTournament(' . $t['TeamID'] . ')">Leave</button>';
				}
				echo '</div>';
			}
			echo '</div>';
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

echo '<div>';
if ($total == 0) {
	echo '<br>No participants<br>';
}
else {

	echo '<br>Participants<br>';
	echo '<div class="tournament-teams">';
	if (($tournament_owner) && $tournament['ProgressState'] == 'unstarted') {
		foreach ($pending as $p) {
			echo '<div>';
			printName($p);
			echo '<span class="label pending">PENDING</span>';
			echo '<button onclick="acceptParticipant(' . $p['TournamentParticipantID'] . ')">Accept</button>';
			echo '<button onclick="kickParticipant(' . $p['TournamentParticipantID'] . ')">Reject</button>';
			echo '</div>';
		}
	}

	foreach ($accepted as $p) {
		echo '<div>';
		printName($p);
		if ($tournament_owner && $tournament['ProgressState'] == 'unstarted') {
			echo '<button onclick="revokeParticipant(' . $p['TournamentParticipantID'] . ')">Revoke</button>';
			echo '<button onclick="kickParticipant(' . $p['TournamentParticipantID'] . ')">Kick</button>';
		}
		echo '</div>';
	}
	echo '</div>';
}
echo '</div>';
echo '</div>';