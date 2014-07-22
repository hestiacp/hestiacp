<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v-add-cron-vesta-autoupdate", $output, $return_var);
    $_SESSION['error_msg'] = __('Autoupdate has been successfully enabled');
    unset($output);
}

header("Location: /list/updates/");
exit;
