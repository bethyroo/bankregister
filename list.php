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
$aID = (int)$accounts_array[0]['id'];
if(isset($_GET['aID'])) foreach($accounts_array as $account) {
    if($account['id'] == $_GET['aID']) {
        $aID = $account['id'];
        break;
    }
}
$transaction = $_POST;
switch($_REQUEST['action']) {
    case 'save':
    case 'add':
        if(save_transaction($_POST['id'])) {
            header('location: .');
        } else {
            $message = 'Error saving transaction.';
        }
        break;
    case 'edit':
        $transaction = load_transaction($_REQUEST['id']);
        break;
}
$account_info = get_accounts($aID);
$account_info = $account_info[0];
$sql = 'select * from transactions where account = '.(int)$aID;
$transactions = $db->query($sql);
$page = 'list.html.php';
?>

