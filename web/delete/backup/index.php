<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user = $_GET['user'];
}

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit;
}

if (!empty($_GET['backup'])) {
    $v_backup = $_GET['backup'];
    v_exec('v-delete-user-backup', [$user, $v_backup]);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/backup/");
exit;
