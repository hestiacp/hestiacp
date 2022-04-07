<?php

ob_start();
// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['userContext'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check token
verify_csrf($_GET);

if (!empty($_GET['listname'])) {
    $v_listname = $_GET['listname'];
    exec(HESTIA_CMD."v-delete-firewall-ipset ".escapeshellarg($v_listname), $output, $return_var);
}
check_return_code($return_var, $output);
unset($output);

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/firewall/ipset/");
exit;
