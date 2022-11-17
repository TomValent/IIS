<head>
	<link rel="icon" type="image/png" href=<?php echo url("/assets/favicon.ico") ?>>
	<link rel="icon" type="image/png" sizes="32x32" href=<?php echo url("/assets/favicon-32x32.png") ?>>
	<link rel="icon" type="image/png" sizes="16x16" href=<?php echo url("/assets/favicon-16x16.png") ?>>
	<link rel="stylesheet" href=<?php echo url("/CSS/styles.css") ?>>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
	<script>
        function logout() {
            $.get(<?php echo url("/api.php/user/logout") ?>).done(function() {
                if (typeof onLogout !== "undefined") onLogout();
            }).fail(function(data, textStatus, xhr) {
                console.log("request failed");
            });
        }

        function get(url, done) {
            $.ajax({
                url: url
            })
                .done(done)
                .fail(function(data, textStatus, xhr) {
                    console.log("request failed");
                });
        }

	</script>
</head>
