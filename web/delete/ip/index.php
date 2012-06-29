<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['ip'])) {
        $v_ip = escapeshellarg($_GET['ip']);
        exec (VESTA_CMD."v_delete_sys_ip ".$v_ip, $output, $return_var);
        unset($output);
    }
}

header("Location: /list/ip/");
