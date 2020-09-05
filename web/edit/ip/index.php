<?php
error_reporting(NULL);
ob_start();
$TAB = 'IP';

// Main include
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

// List ip
$v_ip = escapeshellarg($_GET['ip']);
exec (HESTIA_CMD."v-list-sys-ip ".$v_ip." 'json'", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse ip
$v_username = $user;
$v_ip = $_GET['ip'];
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
exec (HESTIA_CMD."v-list-sys-users 'json'", $output, $return_var);
$users = json_decode(implode('', $output), true);
unset($output);

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('Location: /login/');
        exit();
    }

    $v_ip = escapeshellarg($_POST['v_ip']);

    // Change Status
    if (($v_ipstatus == 'shared') && (empty($_POST['v_shared'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-change-sys-ip-status ".$v_ip." 'dedicated'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_dedicated = 'yes';
    }
    if (($v_ipstatus == 'dedicated') && (!empty($_POST['v_shared'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-change-sys-ip-status ".$v_ip." 'shared'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unset($v_dedicated);
    }

    // Change owner
    if (($v_owner != $_POST['v_owner']) && (empty($_SESSION['error_msg']))) {
        $v_owner = escapeshellarg($_POST['v_owner']);
        exec (HESTIA_CMD."v-change-sys-ip-owner ".$v_ip." ".$v_owner, $output, $return_var);
        check_return_code($return_var,$output);
        $v_owner = $_POST['v_owner'];
        unset($output);
    }

    // Change associated domain
    if (($v_name != $_POST['v_name']) && (empty($_SESSION['error_msg']))) {
        $v_name = escapeshellarg($_POST['v_name']);
        exec (HESTIA_CMD."v-change-sys-ip-name ".$v_ip." ".$v_name, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change NAT address
    if (($v_nat != $_POST['v_nat']) && (empty($_SESSION['error_msg']))) {
        $v_nat = escapeshellarg($_POST['v_nat']);
        exec (HESTIA_CMD."v-change-sys-ip-nat ".$v_ip." ".$v_nat, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = _('Changes has been saved.');
    }
}

// Render page
render_page($user, $TAB, 'edit_ip');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
