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

// If the stored reset token matches the current request one it means that we need 
// to prevent the action because the browser automatically reloaded the page when 
// the server turned on. This will prevent duplicate restarts.
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
            $_SESSION['error_msg'] = _('The system is going down for reboot NOW!');
            exec(HESTIA_CMD . "v-restart-system yes", $output, $return_var);
        }
        unset($output);
    }
}

header("Location: /list/server/");
exit();
