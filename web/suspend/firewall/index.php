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

if (!empty($_GET['rule'])) {
    $v_rule = $_GET['rule'];
    v_exec('v-suspend-firewall-rule', [$v_rule]);
}

$back = getenv('HTTP_REFERER');
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/firewall/");
exit;
