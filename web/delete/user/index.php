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
    if (!empty($_GET['user'])) {
        $v_username = $_GET['user'];
        v_exec('v-delete-user', [$v_username]);
    }
    unset($_SESSION['look']);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/user/");
exit;
