<?php

if ($_GET['user'] === 'system') {
    $TAB = 'SERVER';
} else {
    $TAB = 'LOG';
}

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

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

// Render page
render_page($user, $TAB, 'list_log');
