<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

if (!empty($_GET['rule'])) {
    $v_rule = escapeshellarg($_GET['rule']);
    exec (VESTA_CMD."v-unsuspend-firewall-rule ".$v_rule, $output, $return_var);
}
check_return_code($return_var,$output);
unset($output);

$back=getenv("HTTP_REFERER");
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/firewall/");
exit;
