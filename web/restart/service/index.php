<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['srv'])) {
        $v_service = escapeshellarg($_GET['srv']);
        exec (VESTA_CMD."v-restart-service ".$v_service, $output, $return_var);
    }
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error =  _('SERVICE_ACTION_FAILED',_('restart'),$v_service);
            $_SESSION['error_srv'] = $error;
    }
    unset($output);
}

header("Location: /list/services/");
exit;
