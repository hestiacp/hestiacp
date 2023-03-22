<?php

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (empty($_GET['user'])) {
    $_GET['user'] = '';
}
if ($_GET['user'] === 'system') {
    $TAB = 'SERVER';
} else {
    $TAB = 'LOG';
}

// Redirect non-administrators if they request another user's log
if (($_SESSION['userContext'] !== 'admin') && (!empty($_GET['user']))) {
    header('location: /login/');
    exit();
}

// Data
if (($_SESSION['userContext'] === "admin") && (!empty($_GET['user']))) {
    // Check token
    verify_csrf($_GET);
    $user=escapeshellarg($_GET['user']);
}

exec(HESTIA_CMD."v-list-user-log $user json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data);
unset($output);
if (empty($_SESSION['look'])) {
    unset($_SESSION['look']);
}

// Render page
if($user === 'system'){
    $user = "'".$_SESSION['user']."'";
}
render_page($user, $TAB, 'list_log');
