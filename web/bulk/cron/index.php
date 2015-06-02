<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit();
}

$job = $_POST['job'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v-delete-cron-job';
            break;
        case 'suspend': $cmd='v-suspend-cron-job';
            break;
        case 'unsuspend': $cmd='v-unsuspend-cron-job';
            break;
        case 'delete-cron-reports': $cmd='v-delete-cron-reports';
            exec (VESTA_CMD.$cmd." ".$user, $output, $return_var);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully diabled');
            unset($output);
            header("Location: /list/cron/");
            exit;
            break;
        case 'add-cron-reports': $cmd='v-add-cron-reports';
            exec (VESTA_CMD.$cmd." ".$user, $output, $return_var);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully enabled');
            unset($output);
            header("Location: /list/cron/");
            exit;
            break;
        default: header("Location: /list/cron/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v-delete-cron-job';
            break;
        case 'delete-cron-reports': $cmd='v-delete-cron-reports';
            exec (VESTA_CMD.$cmd." ".$user, $output, $return_var);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully diabled');
            unset($output);
            header("Location: /list/cron/");
            exit;
            break;
        case 'add-cron-reports': $cmd='v-add-cron-reports';
            exec (VESTA_CMD.$cmd." ".$user, $output, $return_var);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully enabled');
            unset($output);
            header("Location: /list/cron/");
            exit;
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
    exec (VESTA_CMD."v-restart-cron", $output, $return_var);
}

header("Location: /list/cron/");
