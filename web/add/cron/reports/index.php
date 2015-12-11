<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

exec (VESTA_CMD."v-add-cron-reports ".$user, $output, $return_var);
$_SESSION['error_msg'] = __('Cronjob email reporting has been successfully enabled');
unset($output);

header("Location: /list/cron/");
exit;
