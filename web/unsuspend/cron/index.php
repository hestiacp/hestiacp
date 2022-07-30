<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

// Init
ob_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if (!empty($_GET['job'])) {
    $v_job = quoteshellarg($_GET['job']);
    exec(HESTIA_CMD."v-unsuspend-cron-job ".$user." ".$v_job, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
}

$back=getenv("HTTP_REFERER");
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/cron/");
exit;
