<?php
error_reporting(NULL);
$TAB = 'IP';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    v_exec('v-list-sys-ips', ['json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_ip.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
