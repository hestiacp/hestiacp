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
    v_exec('v-list-user-backups', [$user, 'json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_backup.html');
} else {
    v_exec('v-list-user-backup', [$user, $_GET['backup'], 'json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_backup_detail.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
