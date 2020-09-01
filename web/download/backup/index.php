<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('Location: /login/');
    exit();
}

$backup = $_GET['backup'];  
   
if(!file_exists('/backup/'.$backup)){
    $v_username = escapeshellarg($user);
    $backup = escapeshellarg($_GET['backup']);
    exec (HESTIA_CMD."v-schedule-user-backup-download ".$v_username." ".$backup , $output, $return_var);
    if ($return_var == 0) {
        $_SESSION['error_msg'] = _('BACKUP_DOWNLOAD_SCHEDULED');
    } else {
        $_SESSION['error_msg'] = implode('<br>', $output);
        if (empty($_SESSION['error_msg'])) {
            $_SESSION['error_msg'] = _('Error: Hestia did not return any output.');
        }    
    }
    unset($output);
    header("Location: /list/backup/");
    exit;

}else{
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
}