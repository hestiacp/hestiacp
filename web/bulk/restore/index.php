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

$action = $_POST['action'];
$backup = $_POST['backup'];

$web = 'no';
$dns = 'no';
$mail = 'no';
$db = 'no';
$cron = 'no';
$udir = 'no';

if (!empty($_POST['web'])) $web = implode(',', $_POST['web']);
if (!empty($_POST['dns'])) $dns = implode(',', $_POST['dns']);
if (!empty($_POST['mail'])) $mail = implode(',', $_POST['mail']);
if (!empty($_POST['db'])) $db = implode(',', $_POST['db']);
if (!empty($_POST['cron'])) $cron = 'yes';
if (!empty($_POST['udir'])) $udir = implode(',', $_POST['udir']);

if ($action == 'restore') {
    $return_var = v_exec('v-schedule-user-restore', [$user, $backup, $web, $dns, $mail, $db, $cron, $udir]);
    switch ($return_var) {
        case 0:
            $_SESSION['error_msg'] = __('RESTORE_SCHEDULED');
            break;
        case 4:
            $_SESSION['error_msg'] = __('RESTORE_EXISTS');
            break;
    }
}

header("Location: /list/backup/?backup=" . $_POST['backup']);
