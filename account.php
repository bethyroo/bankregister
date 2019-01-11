<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page account.php
 * 
 * This page handles account settings.
 */
if (!isset($handler) || !$handler)
    die('access denied!');
$accounts_array = get_accounts();
$action = $_REQUEST['action'];
$aID = $_REQUEST['id']?$_REQUEST['id']:$_REQUEST['aID'];
switch($action) {
    case 'save':
        if(save_account($aID)) {
            header('location: ?page=account');
        } else {
            $message = 'Error saving.';
            $account = $_POST;
            $action = 'edit';
        }
        break;
    case 'edit':
        $temp = get_accounts($aID);
        $account = $temp[0];
    case 'new':
        $action = 'edit';
        break;
    case 'delete':
        if(delete_account($aID)) {
            $message = 'Account deleted.';
            header('location: .');
        } else {
            $message = 'Accounts with a balance cannot be deleted.';
        }
        break;
}
$title = 'Manage Accounts';
$page = 'account.html.php';