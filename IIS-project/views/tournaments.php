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
		<?php if (isset($_SESSION["login"])): ?>
        <div class="button_container">
            <button><a href="/index.php/newTournament">Create Tournament</a></button>
        </div></br>
        <?php endif ?>
        Tournaments:
        <div id="tournaments"></div>

        <?php
            function joinTournament($tournamentID): void
            {
                $pdo = createDB();
                $sql = "SELECT type FROM Tournament WHERE TournamentID = :id";
                $smth= $pdo->prepare($sql);
                $result = $smth->execute(["id" => $tournamentID]);
                var_dump($smth->queryString);
                var_dump(" " . $result);exit;//but whyyy

                $data = [

                ];

                $sql = "";

    //			try {
    //				$stmt= $pdo->prepare($sql);
    //				$stmt->execute($data);
    //			} catch (Exception $e) {
    //				error("Error in joining tournament. Please try again");
    //			}
    //            unset($_POST["joined"]);
            }

            if (isset($_POST["joined"])) {
                    joinTournament($_POST["id"]);
            }
        ?>
