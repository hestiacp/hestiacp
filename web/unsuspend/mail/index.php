<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

if (!empty($_GET['user'])) {
    $user=$_GET['user'];
}

// Mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
    $v_username = escapeshellarg($user);
    $v_domain = escapeshellarg($_GET['domain']);
    exec (VESTA_CMD."v-unsuspend-mail-domain ".$v_username." ".$v_domain, $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = __('Error: vesta did not return any output.');
        $_SESSION['error_msg'] = $error;
    }
    unset($output);
    $back=getenv("HTTP_REFERER");
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
    exec (VESTA_CMD."v-unsuspend-mail-account ".$v_username." ".$v_domain." ".$v_account, $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = __('Error: vesta did not return any output.');
        $_SESSION['error_msg'] = $error;
    }
    unset($output);
    $back=getenv("HTTP_REFERER");
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/?domain=".$_GET['domain']);
    exit;
}

$back=getenv("HTTP_REFERER");
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/mail/");
exit;
