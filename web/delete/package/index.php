<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['package'])) {
        $v_package = escapeshellarg($_GET['package']);
        exec (VESTA_CMD."v_delete_user_package ".$v_package, $output, $return_var);
        unset($output);
    }
}

header("Location: /list/package/");
