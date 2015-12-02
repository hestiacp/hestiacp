<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit;
}

if ((!empty($_GET['ip'])) && (!empty($_GET['chain']))) {
    $v_ip = $_GET['ip'];
    $v_chain = $_GET['chain'];
    v_exec('v-delete-firewall-ban', [$v_ip, $v_chain]);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/firewall/banlist/");
exit;
