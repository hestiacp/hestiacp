<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];
}

if (!empty($_GET['system'])) {
    $v_username = escapeshellarg($user);
    $v_system = escapeshellarg($_GET['system']);
    exec (VESTA_CMD."v-delete-user-backup-exclusions ".$v_username." ".$v_system, $output, $return_var);
}
check_return_code($return_var,$output);
unset($output);

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/backup/exclusions/");
exit;
