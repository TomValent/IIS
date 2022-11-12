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
                        $('#result').html('login successful')
                        window.location.href = "page";
                    },
                    error: function(response)
                    {
                        if (typeof response.responseJSON.error !== "undefined") {
                            $('#result').html(response.responseJSON.error)
                        }
                    },
                });
            }
        </script>
        <div class="right">
            <button><a href="/">Go back</a></button>
        </div>
        <form class="form" method="post" action="/api.php/user/login">
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