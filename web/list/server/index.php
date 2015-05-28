<?php
session_start();
$TAB = 'SERVER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v-list-sys-info json", $output, $return_var);
    $sys = json_decode(implode('', $output), true);
    unset($output);
    exec (VESTA_CMD."v-list-sys-services json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_services.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
