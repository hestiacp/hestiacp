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
    $user = $_GET['user'];
}

if (!empty($_GET['domain'])) {
    $v_username = $user;
    $v_domain = $_GET['domain'];
    v_exec('v-unsuspend-domain', [$v_username, $v_domain]);
}

$back = getenv('HTTP_REFERER');
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/web/");
exit;
