<?php
/*
 * @Project bankregister
 * @author Bethyroo
 * @page list.php
 * 
 * This page lists the current transactions.
 */
if(!isset($handler) || !$handler) die('access denied!');
// First get accounts list
$accounts_array = get_accounts();
$aID = (int)$_REQUEST['aID'];

$transaction = $_POST;
switch($_REQUEST['action']) {
    case 'save':
    case 'add':
        if(save_transaction($_REQUEST, $_POST['id'])) {
            header('location: .?aID='.$aID);
        } else {
            $message = 'Error saving transaction.';
        }
        break;
    case 'edit':
        $transaction = load_transaction($_REQUEST['id']);
        break;
    case 'new':
        $transaction = array('id' => 0);
    case 'delete':
        if(delete_transaction($_POST['id'])) {
            header('location: .?aID='.$aID);
        } else {
            $message = 'Error deleting transaction.';
        }
}
$account_info = get_accounts($aID);
$account_info = $account_info[0];
$transactions = get_transactions($aID);
$more = check_limit($aID);
// see if any recurring
$recurring = count_recurring();
$page = ($_SESSION['mobile'] && !$aID && !$_REQUEST['q'])?'account.html.php':'list.html.php';
?>

