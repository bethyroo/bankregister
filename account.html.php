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
        <style>
            body{
                background-color: #999900;
                background-image: url(minions.jpg);
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-position: center;
            }
        </style>
    </head>
    <body>
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
            <input type="submit" name="action" value="save">
            <button type="button" onclick="window.location.href = '?page=account'">Cancel</button>
            <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>
                <?php if(!count($accounts_array)) { ?>
                <tr>
                    <td>No accounts.</td>
                </tr>
                <?php } else { 
                    foreach ($accounts_array as $account) { ?>
                <tr>
                    <td><?php echo $account['name']; ?></td>
                    <td><button type="button" onclick="window.location.href='?page=account&action=edit&aID=<?php echo $account['id']; ?>'">Edit</button></td>
                    <td><button type="button" onclick="window.location.href='?page=account&action=delete&aID=<?php echo $account['id']; ?>'">Delete</button></td>
                </tr>
                <?php } ?>
            <?php } ?>
            </table>
            <button type="button" onclick="window.location.href='?page=account&action=new'">Add New Account</button>
            <br>
            <button type="button" onclick="window.location.href = '?'">Back</button>
            <?php } ?>
        </form>
        <p class="error"><?php echo $message; ?></p>
    </body>
</html>