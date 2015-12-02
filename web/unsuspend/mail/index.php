<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit;
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
    $v_username = $user;
    $v_domain = $_GET['domain'];
    v_exec('v-unsuspend-mail-domain', [$v_username, $v_domain]);
    $back = getenv('HTTP_REFERER');
    if (!empty($back)) {
        header("Location: $back");
        exit;
    }
    header("Location: /list/mail/");
    exit;
}

// Mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account'])))  {
    $v_username = $user;
    $v_domain = $_GET['domain'];
    $v_account = $_GET['account'];
    v_exec('v-unsuspend-mail-account', [$v_username, $v_domain, $v_account]);
    $back = getenv('HTTP_REFERER');
    if (!empty($back)) {
        header("Location: $back");
        exit;
    }
    header("Location: /list/mail/?domain=".$_GET['domain']);
    exit;
}

$back = getenv('HTTP_REFERER');
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/mail/");
exit;
