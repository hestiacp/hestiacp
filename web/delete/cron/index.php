<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

    // Delete as someone else?
    if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
        $user=$_GET['user'];
    }

    if (!empty($_GET['job'])) {
        $v_username = escapeshellarg($user);
        $v_job = escapeshellarg($_GET['job']);
        exec (VESTA_CMD."v_delete_cron_job ".$v_username." ".$v_job, $output, $return_var);
    }
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
    }
    unset($output);

//}

$back=getenv("HTTP_REFERER");
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}
header("Location: /list/cron/");
exit;
