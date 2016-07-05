<?php
error_reporting(NULL);
$TAB = 'BACKUP';

// Main include
include($_SERVER['DOCUMENT_ROOT'].'/inc/main.php');

// Data
exec (VESTA_CMD."v-list-user-backup-exclusions $user json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);

// Render page
render_page($user, $TAB, 'list_backup_exclusions');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
