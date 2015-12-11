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

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['package'])) {
        $v_package = $_GET['package'];
        v_exec('v-delete-user-package', [$v_package]);
    }
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/package/");
exit;
