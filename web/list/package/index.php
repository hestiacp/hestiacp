<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'PACKAGE';
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v_list_user_packages json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_packages.html');
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
