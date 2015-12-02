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

// DNS domain
if ((!empty($_GET['domain'])) && (empty($_GET['record_id'])))  {
    $v_domain = $_GET['domain'];
    v_exec('v-delete-dns-domain', [$user, $v_domain]);

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
    $v_domain = $_GET['domain'];
    $v_record_id = $_GET['record_id'];
    v_exec('v-delete-dns-record', [$user, $v_domain, $v_record_id]);

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
