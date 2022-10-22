<html>
    <head>
        <title>Example</title>
    </head>
    <body>
        <p>This is an example of a simple HTML page with one paragraph.</p>
	<?php
        $db = mysqli_init();
        if (!mysqli_real_connect($db, 'localhost', 'tomas', 'root', 'devel', 0, '/var/run/mysql/mysql.sock')) {     //TODO
        die('cannot connect '.mysqli_connect_error());
        }
        else
        { echo $db->host_info . "\n"; }
    ?>
    </body>
</html> 
