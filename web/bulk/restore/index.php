<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$action = $_POST['action'];
$backup = escapeshellarg($_POST['backup']);

$web = 'no';
$dns = 'no';
$mail = 'no';
$db = 'no';
$cron = 'no';
$udir = 'no';

if (!empty($_POST['web'])) $web = escapeshellarg(implode(",",$_POST['web']));
if (!empty($_POST['dns'])) $dns = escapeshellarg(implode(",",$_POST['dns']));
if (!empty($_POST['mail'])) $mail = escapeshellarg(implode(",",$_POST['mail']));
if (!empty($_POST['db'])) $db = escapeshellarg(implode(",",$_POST['db']));
if (!empty($_POST['cron'])) $cron = 'yes';
if (!empty($_POST['udir'])) $udir = escapeshellarg(implode(",",$_POST['udir']));

if ($action == 'restore') {
    exec (VESTA_CMD."v-schedule-user-restore ".$user." ".$backup." ".$web." ".$dns." ".$mail." ".$db." ".$cron." ".$udir, $output, $return_var);
    if ($return_var == 0) {
        $_SESSION['restore_msg'] = __('RESTORE_SCHEDULED');
    } else {
        $_SESSION['restore_msg'] = implode('<br>', $output);
        if (empty($_SESSION['restore_msg'])) {
            $_SESSION['restore_msg'] = __('Error: vesta did not return any output.');
        }
        if ($return_var == 4) {
            $_SESSION['restore_msg'] = __('RESTORE_EXISTS');
        }
    }
}

header("Location: /list/backup/?backup=" . $_POST['backup']);
