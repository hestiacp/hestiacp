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

$user = $_POST['user'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v-delete-user'; $restart = 'no';
            break;
        case 'suspend': $cmd='v-suspend-user'; $restart = 'no';
            break;
        case 'unsuspend': $cmd='v-unsuspend-user'; $restart = 'no';
            break;
        case 'update counters': $cmd='v-update-user-counters';
            break;
        case 'rebuild': $cmd='v-rebuild-user'; $restart = 'no';
            break;
        case 'rebuild web': $cmd='v-rebuild-web-domains'; $restart = 'no';
            break;
        case 'rebuild dns': $cmd='v-rebuild-dns-domains'; $restart = 'no';
            break;
        case 'rebuild mail': $cmd='v-rebuild-mail-domains';
            break;
        case 'rebuild db': $cmd='v-rebuild-databases';
            break;
        case 'rebuild cron': $cmd='v-rebuild-cron-jobs';
            break;
        default: header("Location: /list/user/"); exit;
    }
} else {
    switch ($action) {
        case 'update counters': $cmd='v-update-user-counters';
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
    exec (VESTA_CMD."v-restart-web", $output, $return_var);
    exec (VESTA_CMD."v-restart-dns", $output, $return_var);
    exec (VESTA_CMD."v-restart-cron", $output, $return_var);
}

header("Location: /list/user/");
