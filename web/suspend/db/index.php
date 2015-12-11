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

if (!empty($_GET['database'])) {
    $v_username = $user;
    $v_database = $_GET['database'];
    v_exec('v-suspend-database', [$v_username, $v_database]);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: $back");
    exit;
}

header("Location: /list/db/");
exit;
