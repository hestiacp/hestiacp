<?php

// Init
error_reporting(null);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if ($_SESSION['user'] == 'admin') {
    exec(HESTIA_CMD."v-add-cron-hestia-autoupdate", $output, $return_var);
    unset($output);
}

header("Location: /list/updates/");
exit;
