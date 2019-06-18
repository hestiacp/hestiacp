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

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['hostname'])) {
        exec (HESTIA_CMD."v-restart-system yes", $output, $return_var);
        $_SESSION['error_msg'] = 'The system is going down for reboot NOW!';
    }
    unset($output);
}

header("Location: /list/server/");
exit;
