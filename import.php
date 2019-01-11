<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page import.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');

if(isset($_POST['import'])) {
    // begin import
    $db->begin_transaction();
    if(isset($_POST['erase'])) {
        $db->query('truncate transactions;');
        $db->query('truncate recurring;');
    }
    if(!file_exists($_FILES['csv']['tmp_name'])) {
        $error = true;
        $message .= 'No file found.';
    }
    if(basename(strtolower(pathinfo($_FILES['csv']['name'],PATHINFO_EXTENSION)))!='csv'){
        $error = true;
        $message .= 'Invalid file type.';
    }
    if(!$error) {
        $import = array_map('str_getcsv', file($_FILES['csv']['tmp_name']));
        foreach($import as $row) {
            if($row[3] == 'Date')                continue;
            $data['account'] = get_account_by_name($row[0], isset($_POST['create']));
            if(!$data['account']) {
                $error = true;
                $message .= 'Account `'.$row[0].'` does not exist.';
                break;
            } 
            $data['description'] = $row[1];
            $data['value'] = $row[2];
            $data['tran_date'] = $row[3];
            if(db_perform('transactions', $data)!==true){
                $error = true;
                $message .= 'Error adding transaction:' .var_export($data, true);
                break;
            }
        }
    }
    if(!$error) {
        $db->commit();
    } else {
        $db->rollback();
    }
}
$title = 'Import Transactions';
$page = 'import.html.php';