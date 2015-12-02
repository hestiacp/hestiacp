<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Delete as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user = $_GET['user'];
}

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit;
}

// Mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
    $v_domain = $_GET['domain'];
    v_exec('v-delete-mail-domain', [$user, $v_domain]);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: $back");
        exit;
    }
    header("Location: /list/mail/");
    exit;
}

// Mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account'])))  {
    $v_domain = $_GET['domain'];
    $v_account = $_GET['account'];
    v_exec('v-delete-mail-account', [$user, $v_domain, $v_account]);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: $back");
        exit;
    }
    header("Location: /list/mail/?domain=".$_GET['domain']);
    exit;
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/mail/");
exit;
