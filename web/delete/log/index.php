<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

// Clear log
header("Location: /list/log/");
$v_username = escapeshellarg($user);
exec (HESTIA_CMD."v-delete-user-log ".$v_username." ".$output, $return_var);
check_return_code($return_var,$output);
unset($output);

// Render page
render_page($user, $TAB, 'list_log');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

exit;
