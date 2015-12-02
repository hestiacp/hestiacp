<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'DB';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Check database id
if (empty($_GET['database'])) {
    header("Location: /list/db/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user = $_GET['user'];
}

$v_username = $user;
$v_database = $_GET['database'];

// List datbase
v_exec('v-list-database', [$user, $v_database, 'json'], true, $output);
$data = json_decode($output, true);

// Parse database
$v_dbuser = $data[$v_database]['DBUSER'];
$v_password = '';
$v_host = $data[$v_database]['HOST'];
$v_type = $data[$v_database]['TYPE'];
$v_charset = $data[$v_database]['CHARSET'];
$v_date = $data[$v_database]['DATE'];
$v_time = $data[$v_database]['TIME'];
$v_suspended = $data[$v_database]['SUSPENDED'];
$v_status = $v_suspended == 'yes' ? 'suspended' : 'active';

// Check POST request
if (!empty($_POST['save'])) {
    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit;
    }

    // Change database user
    if (($v_dbuser != $_POST['v_dbuser']) && (empty($_SESSION['error_msg']))) {
        $v_dbuser = preg_replace("/^".$user."_/", "", $_POST['v_dbuser']);
        v_exec('v-change-database-user', [$v_username, $v_database, $v_dbuser]);
        $v_dbuser = $user . '_' . $v_dbuser;
    }

    // Change database password
    if ((!empty($_POST['v_password'])) && (empty($_SESSION['error_msg']))) {
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        v_exec('v-change-database-password', [$v_username, $v_database, $v_password]);
        unlink($v_password);
        $v_password = $_POST['v_password'];
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }
}

// Display body
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_db.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
