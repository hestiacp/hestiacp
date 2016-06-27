<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}


if($_GET['delete'] == 1){
    $v_username = escapeshellarg($user);
    $v_id = escapeshellarg((int)$_GET['notification_id']);
    exec (VESTA_CMD."v-delete-user-notification ".$v_username." ".$v_id, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);
} else {
    $v_username = escapeshellarg($user);
    $v_id = escapeshellarg((int)$_GET['notification_id']);
    exec (VESTA_CMD."v-acknowledge-user-notification ".$v_username." ".$v_id, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);
}

exit;
