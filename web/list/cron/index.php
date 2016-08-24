<?php
error_reporting(NULL);
$TAB = 'CRON';

// Main include
include($_SERVER['DOCUMENT_ROOT'].'/inc/main.php');

// Data
exec (VESTA_CMD."v-list-cron-jobs $user json", $output, $return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data,true);
unset($output);

// Render page
render_page($user, $TAB, 'list_cron');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
