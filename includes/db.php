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
            $vals[] = '"'.$value.'"';
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
    }echo $sql;
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
    $sql = 'select a.*, (select sum(`value`) from transactions t where t.account = a.id) as total from accounts a';
    if($aID) $sql .= ' where a.id = '.$aID;
    $result = $db->query($sql);
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        $return[] = array('id' => $row['id'], 'name' => $row['name'], 'type' => $row['type'], 'total' => $row['total']);
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

function get_account_by_name($name, $create = false) {
    global $db;
    $result = $db->query('select * from accounts where name = "'.$name.'"');
    if($result->num_rows) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } elseif($create) {
        return add_account($name);
    }
    return false;
}

function add_account($name) {
    $data['name'] = $name;
    $data['type'] = 'bank';
    db_perform('accounts', $data);
    global $db;
    return $db->insert_id;
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

function get_transactions($aID) {
    global $db;
    $return = array();
    $sql = 'select * from transactions where account = '.(int)$aID;
    $result = $db->query($sql);
    $i = 0;
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        foreach($row as $key => $value) {
            $return[$i][$key] = $value;
        }
        $i++;
    }
    return $return;
}

function save_transaction($id = 0){
    global $db;
    $data = array();
    $data['account'] = $_REQUEST['account'];
    $data['description'] = $_REQUEST['description'];
    $data['tran_date'] = $_REQUEST['tran_date'];
    $data['value'] = $_REQUEST['value'];
    $data['outstanding'] = isset($_REQUEST['outstanding'])?'1':'0';
    // check if a transfer
    if(isset($_POST['transfer'])) {
        $to = get_accounts($_POST['to'])[0];
        $data['description'] = 'Transfer '.($_POST['how']?'To ':'From ').$to['name'];
        $data['value'] = $_POST['how']?-$data['value']:$data['value'];
        $db->begin_transaction();
        // Save first transaction
        if(db_perform('transactions', $data, ($id?'update':'insert'),($id?' id='.$id:''))===false) {
            $db->rollback();
            return false;
        }
        $id = $db->insert_id;
        // now build data for second account
        $data2 = array(
            'account' => $to['id'],
            'description' => 'Transfer '.($_POST['how']?'From ':'To ').get_accounts($data['account'])[0]['name'],
            'tran_date' => $data['tran_date'],
            'value' => -$data['value'],
            'link' => $id
        );
        // save second transaction
        if(db_perform('transactions', $data2, 'insert')===false) {
            $db->rollback();
            return false;
        }
        // update first with link to second
        $data = array('link'=> $db->insert_id);
        if(db_perform('transactions', $data, ($id?'update':'insert'),($id?' id='.$id:''))===false) {
            $db->rollback();
            return false;
        }
        // commit the records
        $db->commit();
        return true;
    } elseif($id && transaction_islinked($id)) {// save existing linked transaction
        $db->begin_transaction();
        // save this transaction
        if(db_perform('transactions', $data, 'update',' id='.$id)===false) {
            $db->rollback();
            return false;
        }
        // update linked transaction
        $data2 = array(
            'tran_date' => $data['tran_date'],
            'value' => -$data['value'],
        );
        if(db_perform('transactions', $data2, 'update', ' id = '. transaction_islinked($id))===false) {
            $db->rollback();
            return false;
        }
        // commit records
        $db->commit();
        return true;
    } else {
        $result = db_perform('transactions', $data, ($id?'update':'insert'),($id?' id='.$id:''));
        return ($result === true);
    }
}

function transaction_islinked($id) {
    global $db;
    $sql = 'select link from transactions where id = '.(int)$id;
    $result = $db->query($sql);
    if($result->num_rows) $row = $result->fetch_assoc();
    return $row['link'];
}

function load_transaction($id) {
    global $db;
    if(!(int)$id) return;
    $result = $db->query('select * from transactions where id = '.(int)$id);
    return $result->fetch_assoc();
}

function delete_transaction($id) {
    global $db;
    if(!(int)$id) return false;
    $db->begin_transaction();
    $link = transaction_islinked($id);
    $sql = 'delete from transactions where id = '.(int)$id;
    $sql2 = 'delete from transactions where id = '.(int)$link;
    if($db->query($sql)===true && $db->query($sql2)===true) {
        $db->commit();
        return true;
    }
    $db->rollback();
    return false;
}

function get_users($id = 0) {
    global $db;
    $return = array();
    $sql = 'select * from users';
    if($id) $sql .= ' where id = '.(int)$id;
    $result = $db->query($sql);
    $i = 0;
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        foreach($row as $key => $value) {
            $return[$i][$key] = $value;
        }
        $i++;
    }
    return $return;
}

function save_user($id = 0) {
    $data = array();
    if($_REQUEST['password'] != $_REQUEST['password2']) return false;
    $data['name'] = $_REQUEST['name'];
    if($_REQUEST['password'])
        $data['password'] = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
    $result = db_perform('users', $data, ($id?'update':'insert'),($id?' id='.$id:''));
    return ($result === true);
}

function delete_user($id) {
    global $db;
    if(!(int)$id) return false;
    $sql = 'select count(*) as users from users';
    $result = $db->query($sql);
    if($result->num_rows) $row = $result->fetch_assoc();
    if($row['users'] == 1) return false;// cannot delete last user
    $sql = 'delete from users where id = '.(int)$id;
    $db->query($sql);
    return true;
}