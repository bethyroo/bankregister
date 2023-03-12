<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page login.php
 * 
 * This page handles login.
 */
if (!isset($handler) || !$handler)
    die('access denied!');

$error = '';

if(isset($_POST['name']) && isset($_POST['password'])) {
    $sql = 'select * from users where name = "'.$_POST['name'] .'"';
    $result = $db->query($sql);
    if($result->num_rows) {
        $row = $result->fetch_assoc();
        if(password_verify($_POST['password'], $row['password'])) {
            // login success
            $_SESSION['user'] = $row['id'];
            $error = 'Login success.';
            header('location: .');// reload page
        } else {
            $error = 'Invalid login.';
        }
    } else {
        $error = 'Invalid login.';
    }
}

?>
<html>
    <head>
        <title>Our Accounts</title>
        <link rel="stylesheet" type="text/css" href="main.css">
        <meta name="viewport" content="width=device-width, minimum-scale=1,maximum-scale=1, user-scalable=no">
        <link rel="apple-touch-icon-precomposed" href="http://<?php echo $_SERVER['SERVER_NAME']. dirname($_SERVER['PHP_SELF']); ?>/apple-touch-icon-precomposed.png"/>
        <link rel="shortcut icon" href="favicon.ico" />
    </head>
    <body id="login">
        <h1>Bank Register</h1>
        <form method="post" action="" id="login_form">
            <input type="hidden" name="mobile" id="mobile" value="1">
            <label for="name">Username:</label>
            <input type="text" name="name" id="name"/>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" />
            <br>
            <button type="submit" name="submit" value="Login">Login</button>
        </form>
        <p class="error"><?php echo $error; ?></p>
        <script>
            function is_touch_device() {
                return 'ontouchstart' in window;
            }
            if(is_touch_device()) document.getElementById("mobile").value = '1';
        </script>
    </body>
</html>
