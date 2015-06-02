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

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['package'])) {
        $v_package = escapeshellarg($_GET['package']);
        exec (VESTA_CMD."v-delete-user-package ".$v_package, $output, $return_var);
    }
    check_return_code($return_var,$output);
    unset($output);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/package/");
exit;
