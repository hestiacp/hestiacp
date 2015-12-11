<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit;
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
            v_exec($cmd, [$user], false);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully diabled');
            header("Location: /list/cron/");
            exit;
        case 'add-cron-reports': $cmd='v-add-cron-reports';
            v_exec($cmd, [$user], false);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully enabled');
            header("Location: /list/cron/");
            exit;
        default: header("Location: /list/cron/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v-delete-cron-job';
            break;
        case 'delete-cron-reports': $cmd='v-delete-cron-reports';
            v_exec($cmd, [$user], false);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully diabled');
            header("Location: /list/cron/");
            exit;
        case 'add-cron-reports': $cmd='v-add-cron-reports';
            v_exec($cmd, [$user], false);
            $_SESSION['error_msg'] = __('Cronjob email reporting has been successfully enabled');
            header("Location: /list/cron/");
            exit;
        default: header("Location: /list/cron/"); exit;
    }
}

foreach ($job as $value) {
    v_exec($cmd, [$user, $value, 'no'], false);
    $restart = 'yes';
}

if (!empty($restart)) {
    v_exec('v-restart-cron', [], false);
}

header("Location: /list/cron/");
