<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$backup = escapeshellarg($_GET['backup']);

$web = 'no';
$dns = 'no';
$mail = 'no';
$db = 'no';
$cron = 'no';
$udir = 'no';

if ($_GET['type'] == 'web') $web = escapeshellarg($_GET['object']);
if ($_GET['type'] == 'dns') $dns = escapeshellarg($_GET['object']);
if ($_GET['type'] == 'mail') $mail = escapeshellarg($_GET['object']);
if ($_GET['type'] == 'db') $db = escapeshellarg($_GET['object']);
if ($_GET['type'] == 'cron') $cron = 'yes';
if ($_GET['type'] == 'udir') $udir = escapeshellarg($_GET['object']);

if (!empty($_GET['type'])) {
    $restore_cmd = VESTA_CMD."v-schedule-user-restore ".$user." ".$backup." ".$web." ".$dns." ".$mail." ".$db." ".$cron." ".$udir;
} else {
    $restore_cmd = VESTA_CMD."v-schedule-user-restore ".$user." ".$backup;
}

exec ($restore_cmd, $output, $return_var);
if ($return_var == 0) {
    $_SESSION['error_msg'] = __('RESTORE_SCHEDULED');
} else {
    $_SESSION['error_msg'] = implode('<br>', $output);
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['error_msg'] = __('Error: vesta did not return any output.');
    }
    if ($return_var == 4) {
        $_SESSION['error_msg'] = __('RESTORE_EXISTS');
    }
}

header("Location: /list/backup/?backup=" . $_GET['backup']);
