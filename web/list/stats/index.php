<?php
error_reporting(NULL);
$TAB = 'STATS';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($user == 'admin') {
    if (empty($_GET['user'])) {
        exec (VESTA_CMD."v-list-users-stats json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data, true);
        unset($output);
    } else {
        $v_user = escapeshellarg($_GET['user']);
        exec (VESTA_CMD."v-list-user-stats $v_user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data, true);
        unset($output);
    }

    exec (VESTA_CMD."v-list-sys-users 'json'", $output, $return_var);
    $users = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_stats.html');
} else {
    exec (VESTA_CMD."v-list-user-stats $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_stats.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
