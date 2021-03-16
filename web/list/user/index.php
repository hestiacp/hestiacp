<?php
error_reporting(NULL);
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Do not show the users list if user is impersonating another user
if (isset($_SESSION['look'])) {
    header("Location: /login/");
    exit;
}

// Data
if ($_SESSION['userContext'] === 'admin') {
    exec (HESTIA_CMD . "v-list-users json", $output, $return_var);
} else {
    exec (HESTIA_CMD . "v-list-user ".$user." json", $output, $return_var);
}
$data = json_decode(implode('', $output), true);
$data = array_reverse($data,true);

// Render page
render_page($user, $TAB, 'list_user');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
