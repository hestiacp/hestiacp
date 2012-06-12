<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'DNS';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {

    // Cancel
    if (!empty($_POST['cancel'])) {
        header("Location: /list/dns/");
    }

    // Action
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_domain'])) $errors[] = 'domain';
        if (empty($_POST['v_ip'])) $errors[] = 'ip';
        if (empty($_POST['v_template'])) $errors[] = 'template';
        if (empty($_POST['v_exp'])) $errors[] = 'expiriation date';
        if (empty($_POST['v_soa'])) $errors[] = 'SOA';
        if (empty($_POST['v_ttl'])) $errors[] = 'TTL';

        // Protect input
        $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
        $v_domain = escapeshellarg($v_domain);
        $v_ip = escapeshellarg($_POST['v_ip']);
        $v_template = escapeshellarg($_POST['v_template']);
        $v_exp = escapeshellarg($_POST['v_exp']);
        $v_soa = escapeshellarg($_POST['v_soa']);
        $v_ttl = escapeshellarg($_POST['v_ttl']);

        // Check for errors
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = "Error: field ".$error_msg." can not be blank.";
        } else {

            // Add DNS
            exec (VESTA_CMD."v_add_dns_domain ".$user." ".$v_domain." ".$v_ip." ".$v_template." ".$v_exp." ".$v_soa." ".$v_ttl, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = 'Error: vesta did not return any output.';
                $_SESSION['error_msg'] = $error;
            }
            unset($output);

            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = "OK: domain <b>".$_POST[v_domain]."</b> has been created successfully.";
                unset($v_domain);
            }
        }
    }

    exec (VESTA_CMD."v_list_dns_templates json", $output, $return_var);
    $templates = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v_list_user_ns ".$user." json", $output, $return_var);
    $soa = json_decode(implode('', $output), true);
    $v_soa = $soa[0];
    unset($output);

    $v_ttl = 14400;
    $v_exp = date('Y-m-d', strtotime('+1 year'));

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_add_dns.html');
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_dns.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
