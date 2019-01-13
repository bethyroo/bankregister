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
    </head>
    <body>
        <form method="post" name="user_form" id="account_form">
            <?php if($action == 'edit') { ?>
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo $user['name']; ?>">
            <br>
            <label><?php echo $user['id']?'Change ':'';?>Password:</label>
            <input type="password" name="password">
            <br>
            <label>Confirm Password:</label>
            <input type="password" name="password2">
            <br>
            <input type="submit" name="action" value="save">
            <button type="button" onclick="window.location.href = '?page=users'">Cancel</button>
            <?php } else { ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>
                <?php if(!count($users_array)) { ?>
                <tr>
                    <td>No users.</td>
                </tr>
                <?php } else { 
                    foreach ($users_array as $user) { ?>
                <tr>
                    <td><?php echo $user['name']; ?></td>
                    <td>
                        <button type="button" onclick="window.location.href='?page=users&action=edit&uID=<?php echo $user['id']; ?>'">Edit</button>
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