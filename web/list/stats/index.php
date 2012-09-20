<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'STATS';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
exec (VESTA_CMD."v_list_user_stats $user json", $output, $return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data);
unset($output);
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_stats.html');

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
