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
    <body id="account">
        <?php include "nav.html.php"; ?>
        <div id="content">
        <?php if($action == 'edit') { ?>
        <form method="post" name="account_form" id="account_form">
            <button type="button" onclick="window.location.href = '?page=account'">Back</button>
            <input type="hidden" name="id" value="<?php echo $account['id']; ?>">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo $account['name']; ?>">
            <br>
            <label>Type:</label>
            <br>
            <select name="type">
                <option value="bank" <?php if(!isset($account) || $account['type']=='bank') echo "selected=selected"; ?>>
                    Bank Account
                </option>
                <option value="credit" <?php if($account['type']=='credit') echo "selected=selected"; ?>>
                    Credit Card
                </option>
            </select>
            <label>Credit Limit:</label>
            <input type="text" name="credit" value="<?php echo $account['credit']; ?>">
            
            <button type="submit" name="action" value="save">Save</button>
            
            <button type="submit" name="action" value="delete">Delete</button>
        </form>
        <p class="error"><?php echo $message; ?></p>
        <?php } else { 
            // show list of accounts
            include 'account_list.html.php'; 
        } ?>
        </div>
    </body>
</html>
