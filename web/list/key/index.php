<?php
error_reporting(NULL);
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

exec (HESTIA_CMD . "v-list-user-ssh-key ".escapeshellarg($user)." json", $output, $return_var);

$data = json_decode(implode('', $output), true);

// Render page\
render_page($user, $TAB, 'list_key');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
?>