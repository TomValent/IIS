        <div class='right'>
            <button><a href='/index.php/page'>Back to main page</a></button>
        </div>
        <script>

            function getTournaments() {
                get(<?php echo url("/api.php/tournament/list") ?>, function(data) {
                    $('#tournaments').html(data.body);
                });
            }

            function onLogout() {
                getTournaments();
            }

            // initialize jQuery
            $(function() {
                getTournaments();
            });

        </script>
        <?php getMyTeamID($_SESSION["id"]); ?>
        </br></br>
		<?php if (isset($_SESSION["login"])): ?>

        <?php endif ?>
        Tournaments:
        <div id="tournaments"></div>
        <?php
            function getMyTeamID($id): void
            {
                $pdo = createDB();
                $stmt1 = $pdo->prepare("SELECT TeamID, Name FROM Team WHERE LeaderID = :id");
				$stmt1->execute(["id" => $id]);
				$teams = $stmt1->fetchAll();

                echo "<form class='table' method='post'>
                        <select name='id'>";
                foreach ($teams as $team) {
                    $teamID = $team["TeamID"];
                    $teamName = $team["Name"];
                    echo "<option value='$teamID'>$teamName</option>";
                }
                echo "</br><input type='submit' value='Select'/></select></form>";
                echo "post ".$_POST["id"];
				error_log("set post => " . $_POST["id"]);
            }

            function error(string $msg): void
            {
                echo $msg;
                echo "  <div class='right'>
                            <button><a href='/index.php/tournaments'>Go back to tournaments</a></button>
                        </div>";
                exit;
            }

            function joinTournament($tournamentID): void
            {
                $pdo = createDB();
                error_log("join clicked");
				$stmt = $pdo->prepare("SELECT type FROM Tournament WHERE TournamentID = :id");
				$stmt->execute(["id" => $tournamentID]);
				$type = $stmt->fetch()["type"];

                $data = [
                    "t_id" => $tournamentID,
                ];

				switch ($type) {
                    case "member":
						$sql = "INSERT INTO TournamentParticipant VALUES (default, :t_id, :memberID, NULL, 'pending', 0)";
                        $data["memberID"] = $_SESSION["id"];
						try {
							$stmt= $pdo->prepare($sql);
							$stmt->execute($data);
						} catch (Exception $e) {
							error("Error in joining tournament. Please try again");
						}

                        break;
                    case "team":
						$sql = "INSERT INTO TournamentParticipant VALUES (default, :t_id, NULL, :team_id, 'pending', 0)";

						if (isset($_POST["id"])) {
							$data["team_id"] = $_POST["id"];
						}

						error_log("data " . $data["t_id"] . " " . $_POST["id"]);
						$stmt = $pdo->prepare($sql);
                        $stmt->execute($data);

                        break;
                }
                unset($_POST["joined"]);
            }

            if (isset($_POST["joined"])) {
                joinTournament($_POST["id"]);
            }
        ?>
