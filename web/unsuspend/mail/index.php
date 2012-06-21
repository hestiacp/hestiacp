<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    // Mail domain
    if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
        $v_username = escapeshellarg($user);
        $v_domain = escapeshellarg($_GET['domain']);
        exec (VESTA_CMD."v_unsuspend_mail_domain ".$v_username." ".$v_domain, $output, $return_var);
        unset($output);
        header("Location: /list/mail/");
        exit;
    }

    // Mail account
    if ((!empty($_GET['domain'])) && (!empty($_GET['account'])))  {
        $v_username = escapeshellarg($user);
        $v_domain = escapeshellarg($_GET['domain']);
        $v_account = escapeshellarg($_GET['account']);
        exec (VESTA_CMD."v_unsuspend_mail_account ".$v_username." ".$v_domain." ".$v_account, $output, $return_var);
        unset($output);
        header("Location: /list/mail/?domain=".$_GET['domain']);
        exit;
    }
}

header("Location: /list/mail/");
