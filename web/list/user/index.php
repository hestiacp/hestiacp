<?php
error_reporting(NULL);
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Data
if ($user == 'admin') {
    exec (VESTA_CMD . "v-list-users json", $output, $return_var);
} else {
    exec (VESTA_CMD . "v-list-user ".$user." json", $output, $return_var);
}
$data = json_decode(implode('', $output), true);
$data = array_reverse($data,true);

// Render page
render_page($user, $TAB, 'list_user');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
