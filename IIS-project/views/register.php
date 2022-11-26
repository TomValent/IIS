        <script>
            function onRegisterSubmit(form) {
                $('#formResult').html(null)

                var formData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: form.action,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('register successful')
                        $('#formResult').html('register successful')
                        setTimeout(function(){
                            window.location.href = <?php echo url("/index.php/login") ?>;
                        }, 500);
                    },
                    error: function(response)
                    {
                        let error = 'server-side error occurred'
                        try {
                            error = response.responseJSON.error
                        }
                        catch(TypeError) {
                        }
                        $('#formResult').html(error)
                    },
                });
            }
        </script>
        <form class="form" method="post" action=<?php echo url("/api.php/user/register") ?>>
            <label for="username">Username</label></br>
            <input class="input" type="text" id="username" name="username"><br><br>
            <label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"><br><br>
            <label for="pass">Password</label></br>
            <input class="input" type="password" id="pass" name="pass"><br><br>
            <input type="button" value="Submit" onclick="onRegisterSubmit(this.form)"/>
        </form>
        <div id="formResult"></div>
        <div class="alternative">
            <p>Do you want to log in?</p>
            <a href="login"><button>Log in</button></a>
        </div>
