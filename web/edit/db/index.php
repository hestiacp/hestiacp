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
    $user=escapeshellarg($_GET['user']);
}

// List datbase
$v_database = escapeshellarg($_GET['database']);
exec (VESTA_CMD."v-list-database ".$user." ".$v_database." 'json'", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse database
$v_username = $user;
$v_database = $_GET['database'];
$v_dbuser = $data[$v_database]['DBUSER'];
$v_password = "••••••••";
$v_host = $data[$v_database]['HOST'];
$v_type = $data[$v_database]['TYPE'];
$v_charset = $data[$v_database]['CHARSET'];
$v_date = $data[$v_database]['DATE'];
$v_time = $data[$v_database]['TIME'];
$v_suspended = $data[$v_database]['SUSPENDED'];
if ( $v_suspended == 'yes' ) {
    $v_status =  'suspended';
} else {
    $v_status =  'active';
}

// Check POST request
if (!empty($_POST['save'])) {
    $v_username = $user;

    // Change database user
    if (($v_dbuser != $_POST['v_dbuser']) && (empty($_SESSION['error_msg']))) {
        $v_dbuser = preg_replace("/^".$user."_/", "", $_POST['v_dbuser']);
        $v_dbuser = escapeshellarg($v_dbuser);
        exec (VESTA_CMD."v-change-database-user ".$v_username." ".$v_database." ".$v_dbuser, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_dbuser = $user."_".preg_replace("/^".$user."_/", "", $_POST['v_dbuser']);
    }

    // Change database password
    if (($v_password != $_POST['v_password']) && (empty($_SESSION['error_msg']))) {
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        exec (VESTA_CMD."v-change-database-password ".$v_username." ".$v_database." ".$v_password, $output, $return_var);
        check_return_code($return_var,$output);    
        unset($output);
        unlink($v_password);
        $v_password = "••••••••";
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
