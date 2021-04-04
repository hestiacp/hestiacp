<?php
error_reporting(NULL);
$TAB = 'SERVER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Render page
render_page($user, $TAB, 'list_server_preview');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
