<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'RRD';
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v_list_sys_rrd json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_rrd.html');
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
