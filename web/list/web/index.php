<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'WEB';
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    exec (VESTA_CMD."v-list-web-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_web.html');
} else {
    exec (VESTA_CMD."v-list-web-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_web.html');
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
