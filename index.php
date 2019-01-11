<?php
/*
 * @Project bankregister
 * @author Bethyroo
 * @page index.php
 * 
 * This is the main handler page. All pages must be accessed through this page.
 */
error_reporting(E_WARNING);
session_start();
setlocale(LC_MONETARY, 'en_US');
$handler = true;

require_once 'includes/install.php';
init();
require_once 'includes/config.php';
require_once 'includes/db.php';
install();
if(!isset($_SESSION['user'])) {
    require 'login.php';
    exit;
}
// define some defaults
$page = 'list.html.php';
$title = 'Bank Account Register';
switch($_REQUEST['page']) {
    case 'logout':
        session_destroy();
        header('location: .');
    case 'account':
        require 'account.php';
        break;
    case 'transaction':
        require 'transaction.php';
        break;
    case 'users':
        require 'users.php';
        break;
    default:
        require 'list.php';
}
require $page;
?>