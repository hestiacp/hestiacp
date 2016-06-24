<?php
error_reporting(NULL);
session_start();
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    if ($user == 'admin') {
        exec (VESTA_CMD . "v-list-users json", $output, $return_var);
    } else {
        exec (VESTA_CMD . "v-list-user ".$user." json", $output, $return_var);
    }
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data,true);
    display_error_block();
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_user.html');
} else {
    exec (VESTA_CMD . "v-list-user ".$user." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    display_error_block();
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_user.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
