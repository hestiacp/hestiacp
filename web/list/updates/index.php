<?php
error_reporting(NULL);
$TAB = 'UPDATES';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v-list-sys-vesta-updates json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);
    exec (VESTA_CMD."v-list-sys-vesta-autoupdate plain", $output, $return_var);
    $autoupdate = $output['0'];
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_updates.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
