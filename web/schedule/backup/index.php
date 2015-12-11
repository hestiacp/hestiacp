<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$return_var = v_exec('v-schedule-user-backup', [$user]);
switch ($return_var) {
    case 0:
        $_SESSION['error_msg'] = __('BACKUP_SCHEDULED');
        break;
    case 4:
        $_SESSION['error_msg'] = __('BACKUP_EXISTS');
        break;
}

header("Location: /list/backup/");
exit;
