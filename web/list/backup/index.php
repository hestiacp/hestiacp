<?php
error_reporting(NULL);
$TAB = 'BACKUP';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if (empty($_GET['backup'])){
    exec (VESTA_CMD."v-list-user-backups $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data,true);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_backup.html');
} else {
    exec (VESTA_CMD."v-list-user-backup $user '".escapeshellarg($_GET['backup'])."' json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data,true);
    unset($output);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_backup_detail.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
