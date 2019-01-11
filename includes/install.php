<?php

/*
 * @Project bankregister
 * @author Bethyroo
 * @page includes/install.php
 */
if (!isset($handler) || !$handler)
    die('access denied!');
// set current revision for update purposes
$version = 1;

function install() {
    global $db, $version;
    // install/update database
    $result = $db->query('SHOW TABLES');
    if($result->num_rows == 0) {
        // install tables
        $sql = "create table configuration (`key` varchar(55) primary key not null default '', `value` varchar(255) not null default '')";
        $db->query($sql);
        $sql = "create table accounts (id int(11) auto_increment primary key, name varchar(255) not null default '', type enum('bank','credit') not null default 'bank')";
        $db->query($sql);
        $sql = "create table transactions (id int(11) auto_increment primary key, account int(11) not null default 0, description varchar(55) not null default '', value float(10,2), tran_date date, outstanding enum('0','1') not null default '0')";
        $db->query($sql);
        $sql = "create table recurring (id int(11) auto_increment primary key, account int(11) not null default 0, description varchar(55) not null default '', value float(10,2), frequency text, next date)";
        $db->query($sql);
        $sql = "create table users (id int(11) auto_increment primary key, name varchar(55) not null default '', password varchar(255) not null default '')";
        $db->query($sql);
        $result = $db->query('SHOW TABLES');
        write_config('VERSION', 1);
        // add new admin user
        $stmt = $db->prepare("insert into users (name, password) values (?, ?)");
        $stmt->bind_param("ss", $user, $password);
        $user = $_POST['user'];
        $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
    }
    // load constants
    $result = $db->query('SELECT * from configuration');
    if($result->num_rows) while($row = $result->fetch_assoc()) define(strtoupper($row['key']), $row['value']);
    // now update if required
    if(VERSION < $version) {
        // add upgrade scripts here
        write_config('VERSION', $version);
    }
}

function init() {
    if(file_exists('includes/config.php')) return;
    // check if user submitted settings
    if(isset($_POST['db'])){
        // now see if all settings provided
        if(!$_POST['user']) $error = 'You must provide a username.';
        if(!$_POST['password']) $error = 'You must provide a password.';
        if(!$_POST['db_server']) $error = 'You must enter a server.';
        if(!$_POST['db_user']) $error = 'You must provide a database username.';
        if(!$_POST['db_password']) $error = 'You must provide a database password.';
        if(!$_POST['db_name']) $error = 'You must provide a database name.';
        if($_POST['password'] != $_POST['password2']) $error = 'Your passwords do not match.';
        if(!$error) {
            // save credentials
            $file = fopen('includes/config.php', 'w') or die('No write permission on includes directory!');
            fwrite($file, "<?php\n");
            fwrite($file, 'if (!isset($handler) || !$handler) die("access denied!");'."\n");
            fwrite($file, "define('DB_SERVER', '$_POST[db_server]');\n");
            fwrite($file, "define('DB_USER', '$_POST[db_user]');\n");
            fwrite($file, "define('DB_PASSWORD', '$_POST[db_password]');\n");
            fwrite($file, "define('DB_NAME', '$_POST[db_name]');\n");
            fclose($file);
            return true;
        }
    }
    // assume no input from user
    ?>
<html>
    <head>
        <title>Configure New Install</title>
    </head>
    <body>
        <h1>Setup New Install</h1>
        <p><?php echo $error; ?></p>
        <form method="post">
            <label>Database Server</label>
            <input type="text" name="db_server" value="<?php echo isset($_POST['db_server'])?$_POST['db_server']:'localhost'; ?>">
            <br>
            <label>Database Username</label>
            <input type="text" name="db_user" value="<?php echo $_POST['db_user']; ?>">
            <br>
            <label>Database Password</label>
            <input type="text" name="db_password" value="<?php echo $_POST['db_password']; ?>">
            <br>
            <label>Database Name</label>
            <input type="text" name="db_name" value="<?php echo $_POST['db_name']; ?>">
            <br>
            <label>New Admin Name</label>
            <input type="text" name="user" value="<?php echo $_POST['user']; ?>">
            <br>
            <label>New Admin Password</label>
            <input type="password" name="password" value="">
            <br>
            <label>Confirm Password</label>
            <input type="password" name="password2" value="">
            <br>
            <input type="submit" name="db" value="Install">
        </form>
    </body>
</html>
<?php
    exit();
}