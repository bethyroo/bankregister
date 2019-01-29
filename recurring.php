<?php
/*
 * @Project bankregister
 * @author Bethyroo
 * @page list.php
 * 
 * This page lists the current transactions.
 */
if(!isset($handler) || !$handler) die('access denied!');
// Frequency arrays
$frequency_unit = array(
    'Days',
    'Weeks',
    'Months',
    'Years'
);
$frequency_weekend = array(
    'Ignore',
    'Before',
    'After'
);
$frequency_days = array(
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
);
// First get accounts list
$accounts_array = get_accounts();

if(isset($_REQUEST['aID'])) foreach($accounts_array as $account) {
    if($account['id'] == $_REQUEST['aID']) {
        $aID = $account['id'];
        break;
    }
}
$transaction = $_POST;
switch($_REQUEST['action']) {
    case 'save':
    case 'add':
        if(save_recurring($_POST['id'])) {
            header('location: .?page=recurring&aID='.$aID);
        } else {
            $message = 'Error saving transaction.';
        }
        break;
    case 'edit':
        $transaction = load_recurring($_REQUEST['id']);
        //var_dump($transaction);
        break;
    
    case 'new':
        $transaction = array('id' => 0);
        break;
    case 'delete':
        if(delete_recurring($_POST['id'])) {
            header('location: .?page=recurring&aID='.$aID);
        } else {
            $message = 'Error deleting transaction.';
        }
    case 'perform':
        if(perform_recurring()){
            header('location: ?');
        } else {
            $message = "No recurring transactions to perform.";
        }
}
$account_info = get_accounts($aID);
$account_info = $account_info[0];
$transactions = get_recurring($aID);
$title = 'Recurring Transactions';
$page = ($_SESSION['mobile'] && !$aID && !$_REQUEST['q'])?'account.html.php':'recurring.html.php';
?>

