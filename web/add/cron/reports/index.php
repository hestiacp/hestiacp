<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/main.php');

v_exec('v-add-cron-reports', [$user], false);
$_SESSION['error_msg'] = __('Cronjob email reporting has been successfully enabled');

header('Location: /list/cron/');
exit;
