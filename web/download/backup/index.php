<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
$backup = basename($_GET['backup']);

$downloaded = false;
// Check if the backup exists
if (!file_exists('/backup/'.$backup)) {
    //try to download
    if ((!empty($_SESSION['user'])) && ($_SESSION['user'] != 'admin')) {
        if (strpos($backup, $user.'.') === 0) {
            exec(HESTIA_CMD."v-download-backup " . escapeshellarg($backup), $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if(!$_SESSION['error_msg']){
                $downloaded = true;
            }
        }else{
            exit(0);
        }
    }else{
        exec(HESTIA_CMD."v-download-backup " . escapeshellarg($backup),$output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        if(!$_SESSION['error_msg']){
            $downloaded = true;
        }
    }
    if (!file_exists('/backup/'.$backup)) {
        exit(0);
        $downloaded = false;
    }
}

// Data
if ($_SESSION['user'] == 'admin') {
    header('Content-type: application/gzip');
    header("Content-Disposition: attachment; filename=\"".$backup."\";" ); 
    header("X-Accel-Redirect: /backup/" . $backup);
}

if ((!empty($_SESSION['user'])) && ($_SESSION['user'] != 'admin')) {
    if (strpos($backup, $user.'.') === 0) {
        header('Content-type: application/gzip');
        header("Content-Disposition: attachment; filename=\"".$backup."\";" ); 
        header("X-Accel-Redirect: /backup/" . $backup);
    }
}