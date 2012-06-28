<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['backup'])) {
        $v_username = escapeshellarg($user);
        $v_backup = escapeshellarg($_GET['backup']);
        exec (VESTA_CMD."v_delete_user_backup ".$v_username." ".$v_backup, $output, $return_var);
        unset($output);
    }
}

header("Location: /list/backup/");
