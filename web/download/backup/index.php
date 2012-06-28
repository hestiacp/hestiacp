<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
$backup = $_GET['backup'];

// Data
if ($_SESSION['user'] == 'admin') {
    header('Content-type: application/gzip');
    header("Content-Disposition: attachment; filename=\"".$backup."\";" ); 
    header("X-Accel-Redirect: /backup/" . $backup);
}

if ((!empty($_SESSION['user'])) && ($_SESSION['user'] != 'admin')) {
    if (preg_match("/^".$user."/i", $backup)) {
        header('Content-type: application/gzip');
        header("Content-Disposition: attachment; filename=\"".$backup."\";" ); 
        header("X-Accel-Redirect: /backup/" . $backup);
    }
}

?>
