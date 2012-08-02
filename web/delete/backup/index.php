<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

//if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['backup'])) {
        $v_username = escapeshellarg($user);
        $v_backup = escapeshellarg($_GET['backup']);
        exec (VESTA_CMD."v_delete_user_backup ".$v_username." ".$v_backup, $output, $return_var);
    }
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
    }
    unset($output);

//}

header("Location: /list/backup/");
