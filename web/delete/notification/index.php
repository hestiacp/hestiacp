<?php

// Init
error_reporting(null);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if ($_GET['delete'] == 1) {
    $v_username = escapeshellarg($user);
    $v_id = escapeshellarg((int)$_GET['notification_id']);
    exec(HESTIA_CMD."v-delete-user-notification ".$v_username." ".$v_id, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
} else {
    $v_username = escapeshellarg($user);
    $v_id = escapeshellarg((int)$_GET['notification_id']);
    exec(HESTIA_CMD."v-acknowledge-user-notification ".$v_username." ".$v_id, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
}

exit;
