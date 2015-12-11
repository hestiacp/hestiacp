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

// DNS domain
if ((!empty($_GET['domain'])) && (empty($_GET['record_id'])))  {
    $v_username = $user;
    $v_domain = $_GET['domain'];
    v_exec('v-suspend-dns-domain', [$v_username, $v_domain]);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: $back");
        exit;
    }
    header("Location: /list/dns/");
    exit;
}

// DNS record
if ((!empty($_GET['domain'])) && (!empty($_GET['record_id'])))  {
    $v_username = $user;
    $v_domain = $_GET['domain'];
    $v_record_id = $_GET['record_id'];
    v_exec('v-suspend-dns-record', [$v_username, $v_domain, $v_record_id]);
    $back = $_SESSION['back'];
    if (!empty($back)) {
        header("Location: $back");
        exit;
    }
    header("Location: /list/dns/?domain=".$_GET['domain']);
    exit;
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/dns/");
exit;
