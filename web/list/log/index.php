<?php
// Init
error_reporting(NULL);
$TAB = 'LOG';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
exec (VESTA_CMD."v-list-user-log $user json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data);
unset($output);

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_log.html');

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
