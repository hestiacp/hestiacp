<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if ($_SESSION['userContext'] === 'admin') {
    if (!empty($_GET['user'])) {
        $v_username = quoteshellarg($_GET['user']);
        exec(HESTIA_CMD."v-delete-user ".$v_username, $output, $return_var);
    }
    check_return_code($return_var, $output);
    unset($_SESSION['look']);
    unset($output);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/user/");
exit;
