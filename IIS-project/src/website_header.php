<head>
	<link rel="icon" type="image/png" href=<?php echo url("/assets/favicon.ico") ?>>
	<link rel="icon" type="image/png" sizes="32x32" href=<?php echo url("/assets/favicon-32x32.png") ?>>
	<link rel="icon" type="image/png" sizes="16x16" href=<?php echo url("/assets/favicon-16x16.png") ?>>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
	<link rel="stylesheet" href=<?php echo url("/CSS/styles.css") ?>>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
	<script>

        let api = {
            ajax: function (request) {
                let data = {}
                if (typeof request.data !== "undefined") {
                    data = request.data;
                }
                $.ajax({
                    type: request.type,
                    url: request.url,
                    dataType: 'json',
                    data: data,
                    success: (obj)=>{
                        console.log('request success (' + request.url + ')')
                        if (typeof request.success === "function") {
                            request.success(obj)
                        }
                    },
                    error: function(response) {
                        let msg = 'server-side error occurred'
                        try {
                            msg = response.responseJSON.error
                        }
                        catch(TypeError) {
                        }
                        console.log('(' + request.url + ') ' + msg)
                        if (typeof request.error === "function") {
                            request.error(msg)
                        }
                    }
                });
            },
            get: function(request) {
                request.type = 'GET';
                api.ajax(request)
            },
            post: function(request) {
                request.type = 'POST';
                api.ajax(request)
            }
        };

        function logout() {
            api.get({
                url: <?php echo url("/api.php/user/logout") ?>,
                success: ()=> {
                    if (typeof onLogout === "function") onLogout()
                    window.location.href = <?php echo url("/index.php") ?>
                }
            })
        }

	</script>
</head>
