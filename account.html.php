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
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" type="text/css" href="main.css">
        <style>
            
        </style>
    </head>
    <body id="account">
        <div class="nav">
            <button type="button" onclick="window.location.href = '?page=logout'">Logout</button>
            <button type="button" onclick="window.location.href = '?page=users'">Manage Users</button>
            <button type="button" onclick="window.location.href = '?page=import'">Import Transactions</button>
            <button type="button" onclick="window.location.href = '?'">Transactions</button>
        </div>
        <form method="post" name="account_form" id="account_form">
            <?php if($action == 'edit') { ?>
            <input type="hidden" name="id" value="<?php echo $account['id']; ?>">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo $account['name']; ?>">
            <br>
            <label>Type:</label>
            <br>
            <input type="radio" name="type" value="bank" <?php if(!isset($account) || $account['type']=='bank') echo "checked=checked"; ?>>
            <label>Bank Account</label>
            <br>
            <input type="radio" name="type" value="credit" <?php if($account['type']=='credit') echo "checked=checked"; ?>>
            <label>Credit Card</label>
            <br>
            <label>Credit Limit:</label>
            <input type="text" name="credit" value="<?php echo $account['credit']; ?>">
            
            <button type="submit" name="action" value="save">Save</button>
            <button type="button" onclick="window.location.href = '?page=account'">Cancel</button>
            <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <td><h2>Account</h2></td>
                        <td></td>
                    </tr>
                </thead>
                <?php if(!count($accounts_array)) { ?>
                <tr>
                    <td>No accounts.</td>
                </tr>
                <?php } else { 
                    foreach ($accounts_array as $account) { ?>
                <tr>
                    <td><button type="button" onclick="window.location.href='?page=account&action=edit&aID=<?php echo $account['id']; ?>'"><?php echo $account['name']; ?></button></td>
                    <td><button type="button" onclick="window.location.href='?page=account&action=delete&aID=<?php echo $account['id']; ?>'">Delete</button></td>
                </tr>
                <?php } ?>
            <?php } ?>
                <tr>
                    <td colspan="3">
                        <button type="button" onclick="window.location.href='?page=account&action=new'">Add New Account</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <button type="button" onclick="window.location.href = '?'">Back</button>
                    </td>
                </tr>
            </table>
            <?php } ?>
        </form>
        <p class="error"><?php echo $message; ?></p>
    </body>
</html>