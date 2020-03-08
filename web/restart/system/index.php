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

// If the session has the same reset token as the current request prevent restarting again.
// This happens when the server is restarted, the admin panel goes down and the browser reloads
// the /restart/index.php page once the server goes online causing restart loop.
$reset_token_dir = '/var/tmp/';
if (isset($_GET['system_reset_token']) && is_numeric($_GET['system_reset_token'])) {
    clearstatcache();
    $reset_token_file = $reset_token_dir . 'hst_reset_' . $_GET['system_reset_token'];
    if (file_exists($reset_token_file)) {
        unlink($reset_token_file);
        sleep(5);
        header('location: /list/server/');
        exit();
    }
    if ($_SESSION['user'] == 'admin') {
        if (!empty($_GET['hostname'])) {
            touch($reset_token_file);
            $_SESSION['error_msg'] = 'The system is going down for reboot NOW!';
            touch($reset_token_file . '_persistent');
            exec(HESTIA_CMD . "v-restart-system yes", $output, $return_var);
        }
        unset($output);
    }
}

header("Location: /list/server/");
exit();
