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
                    data: data,
                    success: (obj)=>{
                        // console.log('request success (' + request.url + ')')
                        if (typeof request.success === "function") {
                            request.success(obj)
                        }
                    },
                    error: function(response) {
                        // console.log(response)
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
                request.type = 'GET'
                api.ajax(request)
            },
            post: function(request) {
                request.type = 'POST'
                api.ajax(request)
            }
        };

        function getContent(url, target, request_data, success) {
            api.get({
                url: url,
                data: request_data,
                success: (data) => {
                    $(target)[0].innerHTML = data
                    if (typeof success === "function") {
                        success(data)
                    }
                }
            })
        }

        function urlParams() {
            let params = {}
            location.search.substr(1).split("&").forEach(function (item) {
                params[item.split("=")[0]] = item.split("=")[1]
            })
            return params
        }

        function loadMenu() {
            getContent(<?php echo url("/index.php/frags/page_menu")?>, "#page-menu", {'url': window.location.pathname})
        }

        function logout() {
            api.get({
                url: <?php echo url("/api.php/user/logout")?>,
                success: ()=> {
                    if (typeof onLogout === "function") {
                        onLogout()
                        loadMenu()
                    }
                    else if (typeof onLoad === "function") {
                        onLoad()
                        loadMenu()
                    }
                    else window.location.reload()
                }
            })

            api.get({
                url: <?php echo url("../api.php/user/logged_user")?>,
                success: (data)=> {
                    if (typeof data.username !== "undefined") {

                    }
                }
            })
        }

        function setupModal(id) {
            let modal = $(id)[0];
            let modal_btn = $(id +' .close')[0];
            modal_btn.onclick = function() {
                modal.style.display = "none";
            }
        }

        function openModal(id) {
            let modal = $(id)[0];
            modal.style.display = "block";
        }

        function closeModal(id) {
            let modal = $(id)[0];
            modal.style.display = "none";
        }

        let inactivityTimer;
        let inactivityWarning;

        function onInactivity() {
            clearTimeout(inactivityTimer);
            clearTimeout(inactivityWarning);
            logout()
            closeModal('#inactivityModal')
        }

        function onInactivityWarning() {
            openModal('#inactivityModal')
        }

        function onActivity() {
            closeModal('#inactivityModal')
            clearTimeout(inactivityTimer);
            clearTimeout(inactivityWarning);
            const timeout = 14*60 // 14 minutes
            inactivityTimer = setTimeout("onInactivity()", (timeout+60)*1000);
            inactivityWarning = setTimeout("onInactivityWarning()", timeout*1000);
        }

        $(document).on('click mousemove scroll', onActivity);
        $(() => {
            setupModal('#inactivityModal')
            onActivity()
            loadMenu()
        })

	</script>
</head>
