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

if(!file_exists('/backup/'.$backup)){
    $v_username = escapeshellarg($user);
    exec (HESTIA_CMD."v-schedule-user-backup ".$v_username, $output, $return_var);
    if ($return_var == 0) {
        $_SESSION['error_msg'] = __('BACKUP_SCHEDULED');
    } else {
        $_SESSION['error_msg'] = implode('<br>', $output);
        if (empty($_SESSION['error_msg'])) {
            $_SESSION['error_msg'] = __('Error: hestia did not return any output.');
        }
    
        if ($return_var == 4) {
            $_SESSION['error_msg'] = __('BACKUP_EXISTS');
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