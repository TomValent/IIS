        <script>
            function onLoginSubmit(form) {
                $('#formResult').html(null)
                var formData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: form.action,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('login successful')
                        $('#formResult').html('login successful')
                        setTimeout(function(){
                            window.location.href = "page"
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
        <form class="form" method="post" action="../api.php/user/login">
            <span class="red">*</span><label for="login">Login</label></br>
            <input class="input" type="text" id="login" name="login"></br></br>
            <span class="red">*</span><label for="pass">Password</label></br>
            <input class="input" type="password" id="pass" name="pass"></br></br>
            <input type="button" value="Submit" onclick="onLoginSubmit(this.form)"/>
        </form>
        <div id="formResult"></div>
        <div class="alternative">
            <p>Do you want to create new account?</p>
            <a href="register"><button>Register</button></a>
        </div>
