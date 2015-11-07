<?php
error_reporting(NULL);
$TAB = 'DB';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
exec (VESTA_CMD."v-list-databases $user json", $output, $return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data, true);
unset($output);

if ($_SESSION['user'] == 'admin') {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_db.html');
} else {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_db.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');

