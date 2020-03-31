<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page import.html.php
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
    <body>
        <?php include "nav.html.php"; ?>
        <h1>Import Transactions From CSV</h1>
        <div id="content">
        <form method="post" enctype="multipart/form-data">
            <label>File (csv)</label>
            <input type="file" name="csv" id="csv">
            <br>
            <input type="checkbox" name="erase" value="1" <?php echo $_POST['checked']?'checked="checked"':''; ?>>
            <label>Erase existing transactions</label>
            <br>
            <input type="checkbox" name="create" value="1" <?php echo $_POST['checked']?'checked="checkked"':''; ?>>
            <label>Create new accounts if not existing</label>
            <br>
            <button type="submit" name="import" value="1">Import</button>
        </form>
        <p class="error"><?php echo $message; ?></p>
        </div>
    </body>
</html>