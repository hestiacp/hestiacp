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
        v_exec('v-list-users-stats', ['json'], false, $output);
        $data = json_decode($output, true);
        $data = array_reverse($data, true);
    } else {
        $v_user = $_GET['user'];
        v_exec('v-list-user-stats', [$v_user, 'json'], false, $output);
        $data = json_decode($output, true);
        $data = array_reverse($data, true);
    }

    v_exec('v-list-sys-users', ['json'], false, $output);
    $users = json_decode($output, true);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_stats.html');
} else {
    v_exec('v-list-user-stats', [$user, 'json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_stats.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
