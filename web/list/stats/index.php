<?php
error_reporting(NULL);
$TAB = 'STATS';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Data
if ($user == 'admin') {
    if (empty($_GET['user'])) {
        exec (HESTIA_CMD."v-list-users-stats json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data, true);
        unset($output);
    } else {
        $v_user = escapeshellarg($_GET['user']);
        exec (HESTIA_CMD."v-list-user-stats $v_user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data, true);
        unset($output);
    }

    exec (HESTIA_CMD."v-list-sys-users 'json'", $output, $return_var);
    $users = json_decode(implode('', $output), true);
    unset($output);
} else {
    exec (HESTIA_CMD."v-list-user-stats $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);
}

// Render page
render_page($user, $TAB, 'list_stats');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
