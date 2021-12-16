<?php

// Init
error_reporting(null);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

// Check if administrator is viewing system log (currently 'admin' user)
if (($_SESSION['userContext'] === "admin") && (!empty($_GET['user']))) {
    $user=$_GET['user'];
    $token=$_SESSION['token'];
}

// Set correct page reload target
if (($_SESSION['userContext'] === "admin") && (!empty($_GET['user']))) {
    header("Location: /list/log/?user=$user&token=$token");
} else {
    header("Location: /list/log/");
}

// Clear log
$v_username = escapeshellarg($user);
exec(HESTIA_CMD."v-delete-user-log ".$v_username." ".$output, $return_var);
check_return_code($return_var, $output);
unset($output);
unset($token);

// Render page
render_page($user, $TAB, 'list_log');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

exit;
