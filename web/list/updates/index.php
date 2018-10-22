<?php
error_reporting(NULL);
$TAB = 'UPDATES';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header('Location: /list/user');
    exit;
}

// Data
exec (HESTIA_CMD."v-list-sys-hestia-updates json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);
exec (HESTIA_CMD."v-list-sys-hestia-autoupdate plain", $output, $return_var);
$autoupdate = $output['0'];
unset($output);

// Render page
render_page($user, $TAB, 'list_updates');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
