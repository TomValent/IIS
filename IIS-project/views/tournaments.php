        <div class='right'>
            <button><a href='/index.php/page'>Back to main page</a></button>
        </div>
        <script>

            function getTournaments() {
                api.get({
                    url: <?php echo url("/api.php/tournament/list") ?>,
                    success: (data) => {
                        $('#tournaments').html(data.body)
                    }
                })
            }

            function onLogout() {
                getTournaments()
            }

            // initialize jQuery
            $(function() {
                getTournaments()
            });

        </script>
        </br></br>
		<?php if (isset($_SESSION["login"])): ?>
        <div class="button_container">
            <button><a href="/index.php/newTournament">Create Tournament</a></button>
        </div></br>
        <?php endif ?>
        Tournaments:
        <div id="tournaments"></div>

