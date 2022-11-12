        <script>

            function getTournaments() {
                get('/api.php/tournament/list', function(data) {
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
        Tournaments:
        <div id="tournaments">
        </div>

