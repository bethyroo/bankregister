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
    }
    //echo $sql;
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

function query_string($exclude = array(), $add = array()) {
    $return = '?';
    foreach($_GET as $key => $value) {
        if(in_array($key, $exclude)) continue;
        if(array_key_exists($key, $add)) continue;
        $return .= $key.'='.$value.'&';
    }
    // now add additional parameters
    foreach($add as $key=>$value) $return .= $key.'='.$value.'&';
    //echo $return;
    if($return == '?') return '.';
    $return = substr($return,0,-1);
    return $return;
}
/*********************************************************************
 * Search Functions                                                 *
 ********************************************************************/
function search_string($table) {
    $q = $_REQUEST['q'];
    if(!$q) return '';
    // build search string based on table and search parameter
    switch($table) {
        case 'transactions':
            return ' and (description like"%'.$q.'%" or value = '.floatval($q).')';
            break;
        case 'user':
            return ' and name like "%'.$q.'%"';
            break;
        case 'recurring':
            return ' and (description like"%'.$q.'%" or value = '.floatval($q).')';
            break;
        case 'accounts':
            return ' and name like"%'.$q.'%"';
            break;
    }
    return '';
}
/*********************************************************************
 * Account Functions                                                *
 ********************************************************************/
/**
 * 
 * @global mysqli $db
 * @param int $aID
 * @return boolean
 */
function get_accounts($aID = null) {
    global $db;
    $return = array();
    $sql = 'select a.*, (select sum(`value`) from transactions t where t.account = a.id and t.statement = "0") as total, '
            . '(select count(*) from recurring r where r.account = a.id) as recurring, '
            . '(select sum(`value`) from transactions s where s.account = a.id and s.tran_date < now() and s.statement = "0") as available'
            . ' from accounts a';
    if($aID) $sql .= ' where a.id = '.$aID;
    $sql .= search_string('accounts');
    $result = $db->query($sql);
    $i = 0;
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        foreach($row as $key => $value)
            $return[$i][$key] = $value;
        if($return[$i]['type']=='credit'&& $return[$i]['credit']) $return[$i]['available'] = ($return[$i]['credit']+$return[$i]['available']);
        $i++;
        //$return[] = array('id' => $row['id'], 'name' => $row['name'], 'type' => $row['type'], 'total' => $row['total'], 'recurring' => $row['recurring']);
    }
    return $return;
}


function save_account($aID) {
    $data = array();
    $data['name'] = $_REQUEST['name'];
    $data['type'] = $_REQUEST['type'];
    $data['credit'] = $_REQUEST['credit'];
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

function account_name($id) {
    global $db;
    $result = $db->query('select name from accounts where id = '.(int)$id);
    if($result->num_rows) {
        $row = $result->fetch_assoc();
        return $row['name'];
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
    $sql = 'select sum(value) as balance from transactions where statement = "0" and account = '.(int)$aID;
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
/*********************************************************************
 * Transaction Functions                                            *
 ********************************************************************/
function get_transactions($aID, $statement = false) {
    global $db;
    $return = array();
    $sql = 'select transactions.*, accounts.name from transactions join accounts on transactions.account = accounts.id where 1';
    if($aID) $sql .= ' and account = '.(int)$aID;
    $sql .= search_string('transactions');
    if($statement) $sql .= ' and (tran_date > (select max(tran_date) from transactions t where t.statement = "1") or outstanding = "1")';
    if($_SESSION['mobile'] && !$statement) {
        $sql .=' order by tran_date desc, transactions.id desc limit '.(int)$_REQUEST['limit'].',30';
    } else {
        $sql .=' order by tran_date, transactions.id';
    }
    //echo $sql;
    $result = $db->query($sql);
    $i = 0;
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        foreach($row as $key => $value) {
            $return[$i][$key] = $value;
        }
        if($return[$i]['statement']) $return[$i]['description'] = 'Statement Balance';
        $i++;
    }
    return $return;
}

function check_limit($aID) {
    global $db;
    $sql = 'select count(*) as items from transactions where account = '.(int)$aID;
    $sql .= search_string('transactions');
    $result = $db->query($sql);
    if($result->num_rows) {
        $row = $result->fetch_assoc();
        if($row['items'] > ((int)$_REQUEST['limit']+30)) return true;
    }
    return false;
}

function save_transaction($source, $id = 0){
    global $db;
    $data = array();
    $data['account'] = $source['account'];
    $data['description'] = $source['description'];
    $data['tran_date'] = $source['tran_date'];
    $data['value'] = $source['value'];
    $data['outstanding'] = $source['outstanding']=='1'?'1':'0';
    $data['statement'] = $source['statement']=='1'?'1':'0';
    if($data['statement']) $data['outstanding'] = '0';
    // check if a transfer
    if(isset($source['transfer']) && $source['transfer'] == '1') {
        $to = get_accounts($source['to'])[0];
        $data['description'] = 'Transfer '.($source['how']?'To ':'From ').$to['name'];
        $data['value'] = $source['how']?-$data['value']:$data['value'];
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
            'description' => 'Transfer '.($source['how']?'From ':'To ').get_accounts($data['account'])[0]['name'],
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
/*********************************************************************
 * Recurring Functions                                              *
 ********************************************************************/
function get_recurring($aID) {
    global $db;
    $return = array();
    $sql = 'select * from recurring where account = '.(int)$aID.' order by id';
    $sql .= search_string('recurring');
    $result = $db->query($sql);
    $i = 0;
    if($result->num_rows) while($row = $result->fetch_assoc()) {
        foreach($row as $key => $value) {
            $return[$i][$key] = $value;
            $return[$i] = unserialize_frequency($return[$i]);
        }
        $i++;
    }
    return $return;
}

function save_recurring($id = 0){
    global $db;
    $data = array();
    $data['account'] = $_REQUEST['account'];
    $data['description'] = $_REQUEST['description'];
    $data['value'] = $_REQUEST['value'];
    $data['transfer'] = isset($_REQUEST['transfer'])?'1':'0';
    $data['frequency'] = serialize_frequency();
    $data['next'] = calculate_next($data);
    $result = db_perform('recurring', $data, ($id?'update':'insert'),($id?' id='.$id:''));
    return ($result === true);
}


function load_recurring($id) {
    global $db;
    if(!(int)$id) return;
    $result = $db->query('select * from recurring where id = '.(int)$id);
    $return = $result->fetch_assoc();
    $return = unserialize_frequency($return);
    return $return;
}

function delete_recurring($id) {
    global $db;
    if(!(int)$id) return false;
    $link = transaction_islinked($id);
    $sql = 'delete from recurring where id = '.(int)$id;
    return ($db->query($sql)===true);
}

function serialize_frequency() {
    $data = array();
    // frequency
    $data['unit'] = (int)$_POST['unit'];
    $data['increment'] = (int)$_POST['increment'];
    $data['weekday'] = (int)$_POST['weekday'];
    $data['weekend'] = (int)$_POST['weekend'];
    // also need start date
    $data['start'] = $_POST['start'];
    // also process transfer details
    if($_POST['transfer']) {
        $data['to'] = $_POST['to'];
    }
    $return = '';
    foreach($data as $key => $value) {
        $return .= $key.':'.$value.';';
    }
    return $return;
}

function unserialize_frequency($data) {
    $temp = explode(';',$data['frequency']);
    foreach($temp as $item) {
        list($key, $value) = explode(':',$item);
        if(!isset($data[$key])) $data[$key] = $value;
    }
    // now get human readable frequency
    $data = frequency_text($data);
    return $data;
}

function frequency_text($data) {
    global $frequency_days;
    $suffix = '';
    $return = 'Every ';
    if($data['increment']>1) {
        $return .= $data['increment'];
        $suffix = 's';
    }
    switch($data['unit']) {
        case '0'://days
            $return .= ' day'.$suffix;
            break;
        case '1':// weeks
            $return .= ' week'.$suffix.' on ';
            $return .= $frequency_days[$data['weekday']];
            break;
        case '2':// months
            $return .= ' month'.$suffix;
            break;
        case '3':// years
            $return .= ' year'.$suffix;
            break;
    }
    $data['text'] = $return;
    return $data;
}

function calculate_next($data, $date = '') {
    if($date == '') $date = date('Y-m-d', strtotime('tomorrow'));
    $frequency = unserialize_frequency($data);
    $start = strtotime($frequency['start']);
    $end = strtotime($date);
    $current = $start;
    switch($frequency['unit']) {
        case '0':// days
            $increment = 60*60*24*$frequency['increment'];
            while($current <= $end) {// increment by 1 unit
                $current += $increment;
            }
            // now we have target date - check for weekend modifier
            $old = $current;
            $current = weekend_modify($current, $frequency['weekend']);
            if($current <= $end) {
                $current = weekend_modify($old+$increment, $frequency['weekend']);
            }
            break;
        case '1':// weeks
            // index until it is on the right weekday
            while(date('w',$current)!=$frequency['weekday']) $current += 60*60*24;
            $increment = 60*60*24*7*$frequency['increment'];
            while($current <= $end) {
                $current += $increment;
            }
            break;
        case '2':// months
            while($current <= $end) {
                $current = strtotime("+".$frequency['increment']." month", $current);
            }
            $old = $current;
            $current = weekend_modify($current,$frequency['weekend']);
            if($current <= $end) {
                $current = weekend_modify(strtotime("+".$frequency['increment']." month", $old), $frequency['weekend']);
            }
            break;
        case '3':// years
            while($current <= $end) {
                $current = strtotime("+".$frequency['increment']." year", $current);
            }
            $old = $current;
            $current = weekend_modify($current,$frequency['weekend']);
            if($current <= $end) {
                $current = weekend_modify(strtotime("+".$frequency['increment']." year", $old), $frequency['weekend']);
            }
            break;
    }
    //echo $current;exit();
    return date('Y-m-d', $current);
}

function weekend_modify($current, $weekend) {
    // first get day of week
    $day = date('N', $current);
    if($day < 6 || $weekend == '0') return $current;
    // if we are here, it is weekend
    if($weekend == '1') {// before
        while(date('N',$current)>5) // still weekend, subtract a day
                $current -= 60*60*24;
    } else {// after
        while(date('N',$current) > 5) // still weekend, add another
                $current += 60*60*24;
    }
    return $current;
}

function count_recurring() {
    global $db;
    $sql = "select count(*) as num from recurring where next < now()";
    $result = $db->query($sql);
    $return = $result->fetch_assoc();
    return $return['num'];
}

function perform_recurring() {
    global $db;
    $sql = "select * from recurring where next < now()";
    $result = $db->query($sql);
    if(!$result->num_rows) return false;
    while($row = $result->fetch_assoc()) {
        $data = $row;
        $data = unserialize_frequency($data);
        $data['tran_date'] = $data['next'];
        while(strtotime($data['tran_date']) < time()) {
            save_transaction($data);
            $data['tran_date'] = calculate_next($data, $data['tran_date']);
            //echo($data['tran_date']);
        }
        // now save updated next date
        $save = array('next' => $data['tran_date']);
        db_perform('recurring', $save, 'update','id='.$data['id']);
    }
    return true;
}
/*********************************************************************
 * User Functions                                                   *
 ********************************************************************/
function get_users($id = 0) {
    global $db;
    $return = array();
    $sql = 'select * from users';
    $sql .= search_string('users');
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