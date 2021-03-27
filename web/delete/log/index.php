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

// Check if administrator is viewing system log (currently 'admin' user)
if (($_SESSION['userContext'] === "admin") && ($_GET['user']) === 'admin') {
    $user=$_GET['user'];
    $token=$_SESSION['token'];
}

// Set correct page reload target
if (($_SESSION['userContext'] === "admin") && ($_GET['user']) === 'admin') {
    header("Location: /list/log/?user=$user&token=$token");
} else {
    header("Location: /list/log/");
}

// Clear log
$v_username = escapeshellarg($user);
exec (HESTIA_CMD."v-delete-user-log ".$v_username." ".$output, $return_var);
check_return_code($return_var,$output);
unset($output);
unset($token);

// Render page
render_page($user, $TAB, 'list_log');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

exit;
