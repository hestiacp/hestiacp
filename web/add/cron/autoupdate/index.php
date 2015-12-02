<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inc/main.php');

if ($_SESSION['user'] == 'admin') {
    v_exec('v-add-cron-vesta-autoupdate', [], false);
    $_SESSION['error_msg'] = __('Autoupdate has been successfully enabled');
}

header('Location: /list/updates/');
exit;
