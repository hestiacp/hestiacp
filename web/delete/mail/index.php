<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Delete as someone else?
if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user=quoteshellarg($user);
}

// Check token
verify_csrf($_GET);

// Mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account']))) {
    $v_username = quoteshellarg($user);
    $v_domain = quoteshellarg($_GET['domain']);
    exec(HESTIA_CMD."v-delete-mail-domain ".$user." ".$v_domain, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
    $back = $_SESSION['back'];
    if($return_var > 0){
       header("Location: /list/mail/");
    }
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/");
    exit;
}

// Mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account']))) {
    $v_domain = quoteshellarg($_GET['domain']);
    $v_account = quoteshellarg($_GET['account']);
    exec(HESTIA_CMD."v-delete-mail-account ".$user." ".$v_domain." ".$v_account, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
    if($return_var > 0){
       header("Location: /list/mail/");
    }else{
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/?domain=".$_GET['domain']);
    exit;
    }
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/mail/");
exit;
