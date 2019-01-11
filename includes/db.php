<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page includes/db.php
 * 
 * This page handles the database connection.
 */
if(!isset($handler) || !$handler) die('access denied!');

require_once 'includes/config.php';
$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD,DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

function db_perform($table, $data, $type = 'insert', $where = '') {
    global $db;
    if($type == 'insert') {
        $sql = 'insert into '.$table . '(';
        $cols = array();
        $vals = array();
        foreach($data as $key => $value) {
            $cols[] = $key;
            $vals[] = "'".$value."'";
        }
        $sql .= implode(',',$cols).')';
        $sql .= ' values ('.implode(',',$vals).')';
    } else {
        $sql = 'update '.$table.' set ';
        foreach($data as $key => $value) {
            $sql .= $key.'="'.$value.'",';
        }
        $sql = substr($sql,0,-1);
        $sql .= ' where '.$where;
    }
    return $db->query($sql);
}

function write_config($name, $value) {
    global $db;
    // check if insert or update
    $result = $db->query('select * from configuration where `key` = "'.$name.'"');
    if($result->num_rows > 0) {// update
        $db->query('update configuration set `value` = "'.$value.'" where `key` = "'.$name.'"');
    } else {// insert
        $db->query('insert into configuration (`key`, `value`) values ("'.$name.'", "'.$value.'")');
    }
}

function get_accounts($aID = null) {
    global $db;
    $return = array();
    $sql = 'select * from accounts';
    if($aID) $sql .= ' where id = '.$aID;
    $result = $db->query($sql);
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        $return[] = array('id' => $row['id'], 'name' => $row['name'], 'type' => $row['type']);
    }
    return $return;
}


function save_account($aID) {
    $data = array();
    $data['name'] = $_REQUEST['name'];
    $data['type'] = $_REQUEST['type'];
    $result = db_perform('accounts', $data, ($aID?'update':'insert'),($aID?' id='.$aID:''));
    return ($result === true);
}

function delete_account($aID) {
    global $db;
    if(!(int)$aID) return false;
    $sql = 'select sum(value) as balance from transactions where account = '.(int)$aID;
    $result = $db->query($sql);
    if($result->num_rows) $row = $result->fetch_assoc();
    if($row['balance'] != 0) return false;// cannot delete account with a balance
    $sql = 'delete from transactions where account = '.(int)$aID;
    $db->query($sql);
    $sql = 'delete from recurring where account = '.(int)$aID;
    $db->query($sql);
    $sql = 'delete from accounts where id = '.(int)$aID;
    $db->query($sql);
    return true;
}

function save_transaction($id = 0){
    $data = array();
    $data['account'] = $_REQUEST['account'];
    $data['description'] = $_REQUEST['description'];
    $data['tran_date'] = $_REQUEST['tran_date'];
    $data['value'] = $_REQUEST['value'];
    $data['outstanding'] = isset($_REQUEST['outstanding'])?'1':'0';
    $result = db_perform('transactions', $data, ($id?'update':'insert'),($id?' id='.$id:''));
    return ($result === true);
}

function load_transaction($id) {
    global $db;
    if(!(int)$id) return;
    $result = $db->query('select * from transactions where id = '.(int)$id);
    return $result->fetch_assoc();
}
