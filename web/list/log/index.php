<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'LOG';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
$return_var = v_exec('v-list-user-log', [$user, 'json'], false, $output);
check_error($return_var);
$data = json_decode($output, true);
$data = array_reverse($data);

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_log.html');

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
