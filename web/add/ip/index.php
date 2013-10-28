<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'IP';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_ip'])) $errors[] = __('ip address');
        if (empty($_POST['v_netmask'])) $errors[] = __('netmask');
        if (empty($_POST['v_interface'])) $errors[] = __('interface');
        if (empty($_POST['v_owner'])) $errors[] = __('assigned user');

        // Protect input
        $v_ip = escapeshellarg($_POST['v_ip']);
        $v_netmask = escapeshellarg($_POST['v_netmask']);
        $v_name = escapeshellarg($_POST['v_name']);
        $v_nat = escapeshellarg($_POST['v_nat']);

        $v_interface = $_POST['v_interface'];
        $v_shared = $_POST['v_shared'];
        if ($v_shared == 'on') {
            $ip_status = 'shared';
        } else {
            $ip_status = 'dedicated';
            $v_dedicated = 'yes';
        }

        $v_owner = $_POST['v_owner'];

        // Check for errors
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
        } else {
            // Add IP
            $v_interface = escapeshellarg($_POST['v_interface']);
            $v_owner = $_POST['v_owner'];
            exec (VESTA_CMD."v-add-sys-ip ".$v_ip." ".$v_netmask." ".$v_interface."  ".$v_owner." '".$ip_status."' ".$v_name." ".$v_nat, $output, $return_var);
            $v_owner = $_POST['v_owner'];
            $v_interface = $_POST['v_interface'];
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('IP_CREATED_OK',$_POST['v_ip'],$_POST['v_ip']);
                unset($v_ip);
                unset($v_netmask);
                unset($v_name);
                unset($v_nat);
            }
        }
    }
    exec (VESTA_CMD."v-list-sys-interfaces 'json'", $output, $return_var);
    $interfaces = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v-list-sys-users 'json'", $output, $return_var);
    $users = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_ip.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
