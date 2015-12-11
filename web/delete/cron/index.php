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

if (!empty($_GET['job'])) {
    $v_job = $_GET['job'];
    v_exec('v-delete-cron-job', [$user, $v_job]);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/cron/");
exit;
