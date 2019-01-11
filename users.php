<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page users.php
 * 
 * This page handles creating/editing users
 */
if (!isset($handler) || !$handler)
    die('access denied!');

$uID = (int)$_REQUEST['uID'];

$title = 'Manage Users';
$users_array = get_users();
switch($_REQUEST['action']) {
    case 'save':
    case 'add':
        if(save_user($uID)) {
            unset($_SESSION['new_install']);
            header('location: .?page=users');
        } else {
            $message = 'Error saving user.';
            $user['id'] = $_POST['id'];
            $user['name'] = $_POST['name'];
        }
        break;
    case 'edit':
        $title = 'Edit User';
        $action = 'edit';
        $user = get_users($uID);
        $user = $user[0];
        break;
    case 'new':
        $title = 'Add User';
        $action = 'edit';
        break;
    case 'delete':
        if(delete_user($uID)) {
            $message = 'User deleted.';
            header('location: .?page=users');
        } else {
            $message = 'Failed to delete user.';
        }
}
$page = 'users.html.php';