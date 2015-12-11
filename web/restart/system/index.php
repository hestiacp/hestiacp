<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['hostname'])) {
        v_exec('v-restart-system', ['yes'], false);
        $_SESSION['error_msg'] = 'The system is going down for reboot NOW!';
    }
}

header("Location: /list/server/");
exit;
