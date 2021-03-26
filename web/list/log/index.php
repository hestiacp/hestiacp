<?php
error_reporting(NULL);
$TAB = 'LOG';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Data
if (($_SESSION['userContext'] === "admin") && ($_GET['user'])) {
    $user=escapeshellarg($_GET['user']);
}
exec (HESTIA_CMD."v-list-user-log $user json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data);
unset($output);

// Render page
render_page($user, $TAB, 'list_log');
