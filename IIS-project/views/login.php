        <script>
            function onLoginSubmit(form) {
                $('#result').html(null)
                var formData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: form.action,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('login successful')
                        $('#result').html('login successful')
                        setTimeout(function(){
                            window.location.href = <?php echo url("/index.php/page") ?>
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
                        $('#result').html(error)
                    },
                });
            }
        </script>
        <form class="form" method="post" action=<?php echo url("/api.php/user/login") ?>>
            <label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"><br><br>
            <label for="pass">Password</label></br>
            <input class="input" type="password" id="pass" name="pass"><br><br>
            <input type="button" value="Submit" onclick="onLoginSubmit(this.form)"/>
        </form>
        <div id="result"></div>
        <div class="alternative">
            <p>Do you want to create new account?</p>
            <button><a href="register">Register</a></button>
        </div>
