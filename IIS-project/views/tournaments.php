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
        <div class="button_container">
            <button><a href="/index.php/newTournament">Create Tournament</a></button>
        </div></br>
        Tournaments:
        <div id="tournaments"></div>

