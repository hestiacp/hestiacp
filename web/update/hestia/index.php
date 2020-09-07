<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['pkg'])) {
        $v_pkg = escapeshellarg($_GET['pkg']);
        exec (HESTIA_CMD."v-update-sys-hestia ".$v_pkg, $output, $return_var);
    }

    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = sprintf(_('Error: %s update failed',$v_pkg);
            $_SESSION['error_msg'] = $error;
    }
    unset($output);
}

header("Location: /list/updates/");
exit;
