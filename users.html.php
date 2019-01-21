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
    </head>
    <body id="users">
        <div class="nav">
            <button type="button" onclick="window.location.href = '?page=logout'">Logout</button>
            <button type="button" onclick="window.location.href = '?page=users'">Manage Users</button>
            <button type="button" onclick="window.location.href = '?page=import'">Import Transactions</button>
            <button type="button" onclick="window.location.href = '?'">Transactions</button>
        </div>
        <form method="post" name="user_form" id="account_form">
            <?php if($action == 'edit') { ?>
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
            <button type="submit" name="action" value="save">Save</button>
            <button type="button" onclick="window.location.href = '?page=users'">Cancel</button>
            <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <td colspan="2"><h2>Users</h2></td>
                    </tr>
                </thead>
                <?php if(!count($users_array)) { ?>
                <tr>
                    <td colspan="2">No users.</td>
                </tr>
                <?php } else { 
                    foreach ($users_array as $user) { ?>
                <tr>
                    <td class="cell2">
                        <button type="button" onclick="window.location.href='?page=users&action=edit&uID=<?php echo $user['id']; ?>'"><?php echo $user['name']; ?></button>
                    </td>
                    <td class="cell1">
                        <button type="button" onclick="window.location.href='?page=users&action=delete&uID=<?php echo $user['id']; ?>'">Delete</button>
                    </td>
                </tr>
                <?php } ?>
            <?php } ?>
            </table>
            <button type="button" onclick="window.location.href='?page=users&action=new'">Add New User</button>
            <br>
            <button type="button" onclick="window.location.href = '?'">Back</button>
            <?php } ?>
        </form>
        <p class="error"><?php echo $message; ?></p>
    </body>
</html>