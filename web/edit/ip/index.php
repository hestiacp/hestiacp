<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'IP';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check ip argument
if (empty($_GET['ip'])) {
    header("Location: /list/ip/");
    exit;
}

$v_username = $user;
$v_ip = $_GET['ip'];

// List ip
v_exec('v-list-sys-ip', [$v_ip, 'json'], true, $output);
$data = json_decode($output, true);

// Parse ip
$v_netmask = $data[$v_ip]['NETMASK'];
$v_interace = $data[$v_ip]['INTERFACE'];
$v_name = $data[$v_ip]['NAME'];
$v_nat = $data[$v_ip]['NAT'];
$v_ipstatus = $data[$v_ip]['STATUS'];
if ($v_ipstatus == 'dedicated') $v_dedicated = 'yes';
$v_owner = $data[$v_ip]['OWNER'];
$v_date = $data[$v_ip]['DATE'];
$v_time = $data[$v_ip]['TIME'];
$v_suspended = $data[$v_ip]['SUSPENDED'];
if ( $v_suspended == 'yes' ) {
    $v_status =  'suspended';
} else {
    $v_status =  'active';
}

// List users
v_exec('v-list-sys-users', ['json'], false, $output);
$users = json_decode($output, true);

// Check POST request
if (!empty($_POST['save'])) {
    $v_ip = $_POST['v_ip'];

    // Change Status
    if (($v_ipstatus == 'shared') && (empty($_POST['v_shared'])) && (empty($_SESSION['error_msg']))) {
        v_exec('v-change-sys-ip-status', [$v_ip, 'dedicated']);
        $v_dedicated = 'yes';
    }
    if (($v_ipstatus == 'dedicated') && (!empty($_POST['v_shared'])) && (empty($_SESSION['error_msg']))) {
        v_exec('v-change-sys-ip-status', [$v_ip, 'shared']);
        unset($v_dedicated);
    }

    // Change owner
    if (($v_owner != $_POST['v_owner']) && (empty($_SESSION['error_msg']))) {
        $v_owner = $_POST['v_owner'];
        v_exec('v-change-sys-ip-owner', [$v_ip, $v_owner]);
    }

    // Change associated domain
    if (($v_name != $_POST['v_name']) && (empty($_SESSION['error_msg']))) {
        $v_name = $_POST['v_name'];
        v_exec('v-change-sys-ip-name', [$v_ip, $v_name]);
    }

    // Change NAT address
    if (($v_nat != $_POST['v_nat']) && (empty($_SESSION['error_msg']))) {
        $v_nat = $_POST['v_nat'];
        v_exec('v-change-sys-ip-nat', [$v_ip, $v_nat]);
    }

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
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_ip.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
