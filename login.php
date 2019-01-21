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
    </head>
    <body id="list">
        <h1>Bank Register</h1>
        <form method="post" action="" id="account_form">
            <label for="name">Username:</label>
            <input type="text" name="name" id="name"/>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password"/>
            <br>
            <button type="submit" name="submit" value="Login">Login</button>
        </form>
        <p class="error"><?php echo $error; ?></p>
    </body>
</html>