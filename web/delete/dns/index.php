<?php

ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Delete as someone else?
if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// Check token
verify_csrf($_GET);

// DNS domain
if ((!empty($_GET['domain'])) && (empty($_GET['record_id']))) {
    $v_domain = escapeshellarg($_GET['domain']);
    exec(HESTIA_CMD."v-delete-dns-domain ".$user." ".$v_domain, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);

    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/dns/");
    exit;
}

// DNS record
if ((!empty($_GET['domain'])) && (!empty($_GET['record_id']))) {
    $v_username = escapeshellarg($user);
    $v_domain = escapeshellarg($_GET['domain']);
    $v_record_id = escapeshellarg($_GET['record_id']);
    exec(HESTIA_CMD."v-delete-dns-record ".$v_username." ".$v_domain." ".$v_record_id, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    if($return_var > 0){
        header("Location: /list/dns/");
        exit;
    }else{
        header("Location: /list/dns/?domain=".$_GET['domain']);
        exit;
    }
    
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/dns/");
exit;
