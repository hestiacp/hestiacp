<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Delete as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];
}

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

// Mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
    $v_username = escapeshellarg($user);
    $v_domain = escapeshellarg($_GET['domain']);
    exec (VESTA_CMD."v-delete-mail-domain ".$v_username." ".$v_domain, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/");
    exit;
}

// Mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account'])))  {
    $v_username = escapeshellarg($user);
    $v_domain = escapeshellarg($_GET['domain']);
    $v_account = escapeshellarg($_GET['account']);
    exec (VESTA_CMD."v-delete-mail-account ".$v_username." ".$v_domain." ".$v_account, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/?domain=".$_GET['domain']);
    exit;
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/mail/");
exit;
