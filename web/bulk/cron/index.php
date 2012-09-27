<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$job = $_POST['job'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v_delete_cron_job';
            break;
        case 'suspend': $cmd='v_suspend_cron_job';
            break;
        case 'unsuspend': $cmd='v_unsuspend_cron_job';
            break;
        default: header("Location: /list/cron/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v_delete_cron_job';
            break;
        default: header("Location: /list/cron/"); exit;
    }
}

foreach ($job as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value." no", $output, $return_var);
    $restart = 'yes';
}

if (!empty($restart)) {
    exec (VESTA_CMD."v_restart_cron", $output, $return_var);
}

header("Location: /list/cron/");
