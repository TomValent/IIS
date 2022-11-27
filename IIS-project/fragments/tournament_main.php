<?php

if (!isset($_GET['id'])) {
	echo 'Tournament does not exist';
	exit();
}

try {
	$id = $_GET['id'];

	$tournament = new Tournament($id);
	if (!$tournament->exists()) {
		echo 'Tournament does not exist';
		exit();
	}

	$user_id = getUserID();

	$tournament_owner = $user_id === $tournament["CreatorID"];
	echo '<div class="tournament-top">';
	echo '<p>Tournament</p>';
	echo '<p>'.$tournament['Name'];
	if (!$tournament->isApproved()) {
		echo '<span class="label">PENDING APPROVAL</span>';
	}
	else if ($tournament->isOngoing()) {
		echo '<span class="label ongoing">ONGOING</span>';
	}
	echo '</p>';

	echo '<div id="tournament-management">';
	if ($user_id !== NULL) {

		if ($tournament->isApproved()) {
			if ($tournament_owner || isAdmin()) {
				if ($tournament->isOngoing()) {
					if ($tournament->finalRoundComplete()) {
						echo '<button onclick="endTournament()">Finish</button>';
					}
					else {
						if (isAdmin()) {
							echo '<button onclick="endTournament()">Abort tournament</button>';
						}
						if ($tournament->roundHasResults($tournament['ActualRound'])) {
							echo '<button id="button-next-round" onclick="onNextRound()">Next round</button>';
						}
					}
				} else if ($tournament['ProgressState'] == 'unstarted') {
					echo '<button onclick="startTournament()">Start tournament</button>';
				}
			}
		}
		else {
			if (isAdmin()) {
				echo '<button onclick="approveTournament()">Approve tournament</button>';
			}
		}
		if ($tournament_owner || isAdmin()) {
			$_GET["edit"] = "true";
			echo '<a href="editTournament?id=' . $id . '&edit=true"><button>Edit tournament</button></a>';
			echo '<button onclick="deleteTournament()">Delete tournament</button>';
		}



		if ($tournament['type'] == 'team') {
			$has_teams = Database::getInstance()->userHasTeams($user_id);
			if ($has_teams && $tournament['ProgressState'] == 'unstarted') {
				echo '<span id="joinButtonContainer" style="visibility: hidden">';
				echo '<button onclick="joinTournament(this.parentElement)">Join with team</button>';
				echo '</span>';
			}
		}
		else {
			if ($tournament['ProgressState'] == 'unstarted') {
				$state = Database::getInstance()->getMemberParticipantState($id, $user_id);
				if ($state == 'none') {
					echo '<button onclick="joinTournament()">Join</button>';
				} else {
					if ($state == 'pending') {
						echo '<span class="label pending">PENDING</span>';
					}
					echo '<button onclick="leaveTournament()">Leave</button>';
				}
			}
		}
	}
	echo '<button onclick="viewDetails()">Details</button>';
	echo '</div>';
	echo '</div>';

	echo '<div id="tournament-main">';
	if ($tournament['ProgressState'] != 'unstarted') {
		echo '<div id="tournament-rounds">';

		foreach ($tournament->rounds as $r => $matches) {
			echo '<div class="round">';
			echo '<p> Round ' . $r .' </p>';
			echo '<div class="round-matches">';
			foreach ($matches as &$m) {
				$date = date_create($m['StartTime']);
				$bye = $m['isBye'] ? 'true' : 'false';
				$name1 = $m['Name'][0];
				$name2 = $m['Name'][1];
				$attrib1 = Tournament::winnerAttrib($m, 0);
				$attrib2 = Tournament::winnerAttrib($m, 1);
				echo '<div class="match">';
				echo '<div class="match-time">' . date_format($date, 'H:i d.m.y') . '</div>';
				echo '<span class="match-p1' . $attrib1 . '">' . $name1 . '<span class="match-pts">' . $m['Points1'] . ' pts</span><br></span>';
				if (!$m['isBye']) {
					echo '<span class="match-p2' . $attrib2 . '">' . $name2 . '<span class="match-pts">' . $m['Points2'] . ' pts</span><br></span>';
				}
				else {
					echo '<span class="match-p2">' . $name2 . '<br></span>';
				}
				if ($tournament->isOngoing() && ($tournament_owner || isAdmin()) && $m['Round'] == $tournament['ActualRound']) {
					echo '<button class="set-result-button" onclick="setResult(' . $m['MatchID'] . ')">Set result</button>';
				}
				else {
					echo '<button class="set-result-button" style="visibility: hidden">_</button>';
				}
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
	}
	else {
		require_once "tournament_participants.php";
	}
	echo '</div>';

} catch (PDOException $e) {
	echo $e->getMessage();
	die();
}