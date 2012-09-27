<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$user = $_POST['user'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v_delete_user'; $restart = 'no';
            break;
        case 'suspend': $cmd='v_suspend_user'; $restart = 'no';
            break;
        case 'unsuspend': $cmd='v_unsuspend_user'; $restart = 'no';
            break;
        case 'update counters': $cmd='v_update_user_counters';
            break;
        case 'rebuild': $cmd='v_rebuild_user'; $restart = 'no';
            break;
        case 'rebuild web': $cmd='v_rebuild_web_domains'; $restart = 'no';
            break;
        case 'rebuild dns': $cmd='v_rebuild_dns_domains'; $restart = 'no';
            break;
        case 'rebuild mail': $cmd='v_rebuild_mail_domains';
            break;
        case 'rebuild db': $cmd='v_rebuild_databases';
            break;
        case 'rebuild cron': $cmd='v_rebuild_cron_jobs';
            break;
        default: header("Location: /list/user/"); exit;
    }
} else {
    switch ($action) {
        case 'update counters': $cmd='v_update_user_counters';
            break;
        default: header("Location: /list/user/"); exit;
    }
}

foreach ($user as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$value." ".$restart, $output, $return_var);
    $changes = 'yes';
}

if ((!empty($restart)) && (!empty($changes))) {
    exec (VESTA_CMD."v_restart_web", $output, $return_var);
    exec (VESTA_CMD."v_restart_dns", $output, $return_var);
    exec (VESTA_CMD."v_restart_cron", $output, $return_var);
}

header("Location: /list/user/");
