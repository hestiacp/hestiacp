<?php

ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if ($_SESSION['userContext'] === 'admin') {
    if (!empty($_GET['ip'])) {
        $v_ip = quoteshellarg($_GET['ip']);
        exec(HESTIA_CMD."v-delete-sys-ip ".$v_ip, $output, $return_var);
    }
    check_return_code($return_var, $output);
    unset($output);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/ip/");
exit;
