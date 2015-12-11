<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['pkg'])) {
        $v_pkg = $_GET['pkg'];
        v_exec('v-update-sys-vesta', [$v_pkg]);
    }
}

header("Location: /list/updates/");
exit;
