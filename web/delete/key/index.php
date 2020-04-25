<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];;
}

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

if (!empty($_GET['key'])) {
    $v_key = escapeshellarg(trim($_GET['key']));
    $v_key = str_replace('/','\\/', $v_key);
    exec (HESTIA_CMD."v-delete-user-ssh-key ".$user." ".$v_key);
    check_return_code($return_var,$output);
}

unset($output);

//die();
$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}
header("Location: /list/key/");
exit;