<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

v_exec('v-delete-cron-reports', [$user], false);
$_SESSION['error_msg'] = __('Cronjob email reporting has been successfully disabled');

header("Location: /list/cron/");
exit;
