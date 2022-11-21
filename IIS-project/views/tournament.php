<div class='right'>
    <button><a href='/index.php/tournaments'>Back to tournaments</a></button>
</div>
<script>

    function getOwnedTeams() {
        api.get({
            url: <?php echo url("/api.php/user/owned_teams") ?>,
            success: (data) => {
                let teams = ["Select team"].concat(data.teams);
                sel.html.innerHTML = "";
                for (const t of teams) {
                    let option = document.createElement("option");
                    option.value = t;
                    option.text = t;
                    sel.html.appendChild(option);
                }
            }
        })
    }

    let sel = {
        par: null,
        id: null,
        html: document.createElement("select")
    };

    function hideTeamSelect() {
        if (sel.par) {
            let b = sel.par.getElementsByTagName("button")[0]
            b.style.display = 'inline'
            sel.par = null
        }
        sel.html.selectedIndex = 0
        sel.html.style.display = 'none'
    }

    function showTeamSelect(id, elem) {
        if (sel.par === elem) {
            return
        }
        hideTeamSelect()
        let b = elem.getElementsByTagName("button")[0]
        b.style.display = 'none'
        sel.html.style.display = 'inline'
        elem.appendChild(sel.html)
        sel.par = elem
        sel.id = id
    }

    sel.html.addEventListener(
        'change',
        () => {
            if (sel.html.selectedIndex == 0) {
                return
            }
            let team_name = sel.html.value
            console.log('join r: ' + team_name)

            api.post({
                url: <?php echo url("/api.php/tournament/join") ?>,
                data: {
                    id: sel.id,
                    team_name: team_name
                },
                success: (data) => {
                    console.log('team joined')
                    hideTeamSelect()
                },
                error: () => {
                    hideTeamSelect()
                }
            })
        },
        false
    );

    getOwnedTeams();

    function joinTournament(id, elem) {
        if (elem)  {
            // team join
            showTeamSelect(id, elem)
        }
        else {
            // member join
            api.post({
                url: <?php echo url("/api.php/tournament/join") ?>,
                data: {id: id},
                // success: getTournaments
            })
        }
    }

    function leaveTournament(id, team_id) {
        let data = {
            id: id
        }
        if (team_id) {
            data.team_id = team_id
        }
        api.post({
            url: <?php echo url("/api.php/tournament/leave") ?>,
            data: data,
            // success: getTournaments
        })
    }

    function updateParticipant(url, tournament_id, participant_id) {
        api.post({
            url: url,
            data: {t_id: tournament_id, p_id: participant_id},
            // success: getTournaments
        })
    }

    function acceptParticipant(tournament_id, participant_id) {
        updateParticipant(<?php echo url("/api.php/tournament/accept") ?>, tournament_id, participant_id);
    }

    function revokeParticipant(tournament_id, participant_id) {
        updateParticipant(<?php echo url("/api.php/tournament/revoke") ?>, tournament_id, participant_id);
    }

    function kickParticipant(tournament_id, participant_id) {
        updateParticipant(<?php echo url("/api.php/tournament/kick") ?>, tournament_id, participant_id);
    }

    function rejectParticipant(tournament_id, participant_id) {
        updateParticipant(<?php echo url("/api.php/tournament/kick") ?>, tournament_id, participant_id);
    }

    function startTournament(tournament_id) {
        let data = {
            id: tournament_id
        }
        api.post({
            url: <?php echo url("/api.php/tournament/start") ?>,
            data: data,
        })
    }

    function endTournament(tournament_id) {
        let data = {
            id: tournament_id
        }
        api.post({
            url: <?php echo url("/api.php/tournament/end") ?>,
            data: data,
        })
    }

    function deleteTournament(tournament_id) {
        let data = {
            id: tournament_id
        }
        api.post({
            url: <?php echo url("/api.php/tournament/delete") ?>,
            data: data,
        })
    }

</script>
<?php

    if (!isset($_GET['id'])) {
        echo 'Tournament does not exist';
        exit();
    }

    try {
        $id = $_GET['id'];
        $pdo = createDB();

        $stmt = $pdo->prepare("SELECT * FROM Tournament WHERE TournamentID=:id");
        $stmt->execute(['id' => $id]);
        $tournament = $stmt->fetch();
        if (!$tournament) {
            echo 'Tournament does not exist';
            exit();
        }

        $tournament_owner = false;
		echo 'Tournament<br>';
		echo $tournament['Name'].'<br>';

        if ($tournament['type'] == 'team') {
			$participants = Database::getInstance()->getTeamParticipants($id);
		}
        else {
			$participants = Database::getInstance()->getMemberParticipants($id);
        }

        if (isset($_SESSION["id"])) {
			$user_id = $_SESSION["id"];
            $tournament_owner = $user_id == $tournament["CreatorID"];
            echo '<div id="tournament_management" style="display: inline">';
			if ($tournament_owner) {
                if (isset($_SESSION["isAdmin"])){
					if ($tournament['ProgressState'] == 'unstarted') {
						echo '<button onclick="startTournament(' . $id . ')">Start tournament</button>';
					} else if ($tournament['ProgressState'] == 'ongoing' && isset($_POST["finished"])) {
						echo '<button onclick="endTournament(' . $id . ')">End tournament</button>';
                    }
                }
                if (isset($_SESSION["isAdmin"])) {
                    $_GET["edit"] = "true";
                    echo '<div class="tournament_list_row"> '
						.'<div class="button_container" style="display: inline">'
						. '<button><a href="editTournament?id=' . $id . '&edit=true">Edit tournament</a></button>'
						. '</div></div><br>';
				}
			}
			if ($_SESSION["isAdmin"]) {
				echo '<button onclick="deleteTournament(' . $id . ')">Delete tournament</button>';
			}

			if ($tournament['type'] == 'team') {
				echo '<div id="join" style="display:inline;">';
				echo '<button onclick="joinTournament(' . $id . ', this.parentElement)">Join</button>';
				echo '</div>';
                $teams = array();
                foreach ($participants as $p) {
                    if ($p['LeaderID'] == $user_id) {
                        $teams[] = $p;
                    }
			    }
                if (count($teams) > 0) {
                    echo '<div>';
					echo 'My participating teams:<br>';
                    foreach ($teams as $t) {
						echo $t['Name'];
                        if ($t['AcceptanceState'] == 'pending') {
							echo '<span class="label pending">PENDING</span>';
						}
						echo '<button onclick="leaveTournament(' . $id . ', ' . $t['TeamID'] .')">Leave</button>';
						echo '<br>';
					}
					echo '</div>';
                }
			} else {

				$state = Database::getInstance()->getMemberParticipantState($id, $user_id);
				if ($state == 'none') {
					echo '<button onclick="joinTournament(' . $id . ')">Join</button>';
				} else {
					if ($state == 'pending') {
						echo '<span class="label pending">PENDING</span>';
					}
					echo '<button onclick="leaveTournament(' . $id . ')">Leave</button>';
				}
			}
			echo '</div>';
		}

		echo '<br>Participants:<br>';
        if ($tournament_owner) {
			foreach ($participants as $p) {
                if ($p['AcceptanceState'] == 'pending') {
					echo $p['Name'];
					echo '<span class="label pending">PENDING</span>';
					echo '<button onclick="acceptParticipant(' . $id . ', ' . $p['TournamentParticipantID'] . ')">Accept</button>';
					echo '<button onclick="kickParticipant(' . $id . ', ' . $p['TournamentParticipantID'] . ')">Reject</button>';
					echo '<br>';
                }

			}
		}
		foreach ($participants as $p) {
			if ($p['AcceptanceState'] == 'approved') {
				echo $p['Name'];
				echo '<button onclick="revokeParticipant(' . $id . ', ' . $p['TournamentParticipantID'] . ')">Revoke</button>';
				echo '<button onclick="kickParticipant(' . $id . ', ' . $p['TournamentParticipantID'] . ')">Kick</button>';
				echo '<br>';
			}
		}

        echo '<div id="content">';
        echo '</div>';

    } catch (PDOException $e) {
		echo $e->getMessage();
        die();
    }
?>

