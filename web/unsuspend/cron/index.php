<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

if (!empty($_GET['user'])) {
    $user=$_GET['user'];
}

if (!empty($_GET['job'])) {
    $v_username = escapeshellarg($user);
    $v_job = escapeshellarg($_GET['job']);
    exec (VESTA_CMD."v-unsuspend-cron-job ".$v_username." ".$v_job, $output, $return_var);
}
check_return_code($return_var,$output);
unset($output);

$back=getenv("HTTP_REFERER");
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/cron/");
exit;
