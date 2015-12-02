<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$backup = $_GET['backup'];

$web = 'no';
$dns = 'no';
$mail = 'no';
$db = 'no';
$cron = 'no';
$udir = 'no';

if ($_GET['type'] == 'web') $web = $_GET['object'];
if ($_GET['type'] == 'dns') $dns = $_GET['object'];
if ($_GET['type'] == 'mail') $mail = $_GET['object'];
if ($_GET['type'] == 'db') $db = $_GET['object'];
if ($_GET['type'] == 'cron') $cron = 'yes';
if ($_GET['type'] == 'udir') $udir = $_GET['object'];

if (!empty($_GET['type'])) {
    $restore_args = [$user, $backup, $web, $dns, $mail, $db, $cron, $udir];
} else {
    $restore_args = [$user, $backup];
}

$return_var = v_exec('v-schedule-user-restore', $restore_args);
switch ($return_var) {
    case 0:
        $_SESSION['error_msg'] = __('RESTORE_SCHEDULED');
        break;
    case 4:
        $_SESSION['error_msg'] = __('RESTORE_EXISTS');
        break;
}

header("Location: /list/backup/?backup=" . $_GET['backup']);
