        <script>
            function onRegisterSubmit(form) {
                $('#result').html(null)

                var formData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: form.action,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('register successful')
                        $('#result').html('register successful')
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
                        $('#result').html(error)
                    },
                });
            }
        </script>
        <form class="form" method="post" action=<?php echo url("/api.php/user/register") ?>>
            <span class="red">*</span><label for="username">Username</label></br>
            <input class="input" type="text" id="username" name="username"><br><br>
            <span class="red">*</span><label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"><br><br>
            <span class="red">*</span><label for="pass">Password</label></br>
            <input class="input" type="password" id="pass" name="pass"><br><br>
            <input type="button" value="Submit" onclick="onRegisterSubmit(this.form)"/>
        </form>
        <div class="alternative">
            <p>Do you want to log in?</p>
            <button><a href="login">Log in</a></button>
        </div>
