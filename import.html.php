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
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <h1>Import Transactions From CSV</h1>
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
    </body>
</html>