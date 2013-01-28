<?php
session_start();
$TAB = 'PACKAGE';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v-list-user-packages json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_packages.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
