<?php
error_reporting(NULL);
$TAB = 'RRD';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header('Location: /list/user');
    exit;
}

// Data
exec (VESTA_CMD."v-list-sys-rrd json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);

// Render page
render_page($user, $TAB, 'list_rrd');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
