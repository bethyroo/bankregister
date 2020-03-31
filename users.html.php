<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page list.html.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, minimum-scale=1,maximum-scale=1, user-scalable=no">
        <link rel="apple-touch-icon-precomposed" href="http://<?php echo $_SERVER['SERVER_NAME']. dirname($_SERVER['PHP_SELF']); ?>/apple-touch-icon-precomposed.png"/>
        <link rel="shortcut icon" href="favicon.ico" />
        <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['mobile']?'mobile.css?2':'main.css'; ?>">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <title><?php echo $title; ?></title>
    </head>
    <body id="users">
        <?php include "nav.html.php"; ?>
        <div id="content">
        <form method="post" name="user_form" id="account_form">
            <?php if($action == 'edit') { ?>
            <button type="button" onclick="window.location.href = '?page=users'">Back</button>
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo $user['name']; ?>">
            <br>
            <label><?php echo $user['id']?'Change ':'';?>Password:</label>
            <input type="password" name="password">
            <br>
            <label>Confirm:</label>
            <input type="password" name="password2">
            <br>
            <button type="submit" name="action" value="delete">Delete</button>
            <button type="submit" name="action" value="save">Save</button>
            <?php } else { ?>
            <ul id="list_item">
                <li>
                    <a href="?">Back</a>
                </li>
                <?php if(count($users_array)) 
                    foreach ($users_array as $user) { ?>
                    <li>
                        <a href='?page=users&action=edit&uID=<?php echo $user['id']; ?>'>
                            <?php echo $user['name']; ?><img src="arrow.png">
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href='?page=users&action=new'>
                        Add New User<img src="arrow.png">
                    </a>
                </li>
            </ul>
            <?php } ?>
        </form>
        <p class="error"><?php echo $message; ?></p>
        </div>
    </body>
</html>