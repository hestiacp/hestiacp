<?php
// Init
//error_reporting(NULL);
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
    if (!empty($_POST['back'])) {
        header("Location: /list/dns/");
    }

    // DNS domain
    if ((!empty($_GET['domain'])) && (empty($_GET['record_id'])))  {
        $v_username = escapeshellarg($user);
        $v_domain = escapeshellarg($_GET['domain']);
        exec (VESTA_CMD."v_delete_dns_domain ".$v_username." ".$v_domain, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        } else {
            $_SESSION['ok_msg'] = "OK: dns domain <b>".$_GET['domain']."</b> has been deleted.";
                unset($v_lname);
        }
        unset($output);

        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_delete_dns.html');
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/delete_dns.html');
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    // DNS record
    if ((!empty($_GET['domain'])) && (!empty($_GET['record_id'])))  {
        $v_username = escapeshellarg($user);
        $v_domain = escapeshellarg($_GET['domain']);
        $v_record_id = escapeshellarg($_GET['record_id']);
        exec (VESTA_CMD."v_delete_dns_domain_record ".$v_username." ".$v_domain." ".$v_record_id, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        } else {
            $_SESSION['ok_msg'] = "OK: dns record has been deleted.";
                unset($v_lname);
        }
        unset($output);

        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_delete_dns_rec.html');
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/delete_dns_rec.html');
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
