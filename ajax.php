<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page ajax.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');

switch($_REQUEST['action']) {
    case 'outstanding':
        $id = (int)$_REQUEST['id'];
        $aID = (int)$_REQUEST['aID'];
        $outstanding = $_REQUEST['outstanding'];
        $return = '<xml>';
        if(update_outstanding($id, $outstanding)) {
            $row = check_balance($aID);
            $return .= '<result>success</result>';
            $return .= '<id>'.$id.'</id>';
            $return .= '<outstanding>'.$outstanding.'</outstanding>';
            $return .= '<total>'.$row['total'].'</total>';
            $return .= '<statement>'.$row['statement'].'</statement>';
            
        } else {
            $return .= '<result>fail</result>';
        }
        $return .= '</xml>';
        echo $return;
    exit();
}