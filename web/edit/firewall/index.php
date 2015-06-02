<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'FIREWALL';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check ip argument
if (empty($_GET['rule'])) {
    header("Location: /list/firewall/");
    exit;
}

// List rule
$v_rule = escapeshellarg($_GET['rule']);
exec (VESTA_CMD."v-list-firewall-rule ".$v_rule." 'json'", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse rule
$v_rule = $_GET['rule'];
$v_action = $data[$v_rule]['ACTION'];
$v_protocol = $data[$v_rule]['PROTOCOL'];
$v_port = $data[$v_rule]['PORT'];
$v_ip = $data[$v_rule]['IP'];
$v_comment = $data[$v_rule]['COMMENT'];
$v_date = $data[$v_rule]['DATE'];
$v_time = $data[$v_rule]['TIME'];
$v_suspended = $data[$v_rule]['SUSPENDED'];
if ( $v_suspended == 'yes' ) {
    $v_status =  'suspended';
} else {
    $v_status =  'active';
}

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    $v_rule = escapeshellarg($_GET['rule']);
    $v_action = escapeshellarg($_POST['v_action']);
    $v_protocol = escapeshellarg($_POST['v_protocol']);
    $v_port = str_replace(" ",",", $_POST['v_port']);
    $v_port = preg_replace('/\,+/', ',', $v_port);
    $v_port = trim($v_port, ",");
    $v_port = escapeshellarg($v_port);
    $v_ip = escapeshellarg($_POST['v_ip']);
    $v_comment = escapeshellarg($_POST['v_comment']);

    // Change Status
    exec (VESTA_CMD."v-change-firewall-rule ".$v_rule." ".$v_action." ".$v_ip."  ".$v_port." ".$v_protocol." ".$v_comment, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);

    $v_rule = $_GET['v_rule'];
    $v_action = $_POST['v_action'];
    $v_protocol = $_POST['v_protocol'];
    $v_port = str_replace(" ",",", $_POST['v_port']);
    $v_port = preg_replace('/\,+/', ',', $v_port);
    $v_port = trim($v_port, ",");
    $v_ip = $_POST['v_ip'];
    $v_comment = $_POST['v_comment'];

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Display body
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_firewall.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
