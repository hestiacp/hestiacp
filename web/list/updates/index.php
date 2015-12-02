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
    v_exec('v-list-sys-vesta-updates', ['json'], false, $output);
    $data = json_decode($output, true);

    v_exec('v-list-sys-vesta-autoupdate', ['plain'], false, $output);
    $autoupdate = strtok($output, "\n");

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_updates.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
