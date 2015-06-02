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

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Check empty fields
    if (empty($_POST['v_ip'])) $errors[] = __('ip address');
    if (empty($_POST['v_netmask'])) $errors[] = __('netmask');
    if (empty($_POST['v_interface'])) $errors[] = __('interface');
    if (empty($_POST['v_owner'])) $errors[] = __('assigned user');
    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
    }

    // Protect input
    $v_ip = escapeshellarg($_POST['v_ip']);
    $v_netmask = escapeshellarg($_POST['v_netmask']);
    $v_name = escapeshellarg($_POST['v_name']);
    $v_nat = escapeshellarg($_POST['v_nat']);
    $v_interface = escapeshellarg($_POST['v_interface']);
    $v_owner = escapeshellarg($_POST['v_owner']);
    $v_shared = $_POST['v_shared'];

    // Check shared checkmark
    if ($v_shared == 'on') {
        $ip_status = 'shared';
    } else {
        $ip_status = 'dedicated';
        $v_dedicated = 'yes';

    }

    // Add IP
    if (empty($_SESSION['error_msg'])) {
        exec (VESTA_CMD."v-add-sys-ip ".$v_ip." ".$v_netmask." ".$v_interface."  ".$v_owner." '".$ip_status."' ".$v_name." ".$v_nat, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_owner = $_POST['v_owner'];
        $v_interface = $_POST['v_interface'];
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('IP_CREATED_OK',htmlentities($_POST['v_ip']),htmlentities($_POST['v_ip']));
        unset($v_ip);
        unset($v_netmask);
        unset($v_name);
        unset($v_nat);
    }
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// List network interfaces
exec (VESTA_CMD."v-list-sys-interfaces 'json'", $output, $return_var);
$interfaces = json_decode(implode('', $output), true);
unset($output);

// List users
exec (VESTA_CMD."v-list-sys-users 'json'", $output, $return_var);
$users = json_decode(implode('', $output), true);
unset($output);

// Display body
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_ip.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
