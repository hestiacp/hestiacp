<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['srv'])) {
        if ($_GET['srv'] == 'iptables') {
            $return_var = v_exec('v-stop-firewall', [], false, $output);
        } else {
            $v_service = $_GET['srv'];
            $return_var = v_exec('v-stop-service', [$v_service], false, $output);
        }
    }
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = __('SERVICE_ACTION_FAILED', __('stop'), htmlentities($_GET['srv']));
        $_SESSION['error_srv'] = $error;
    }

}

header("Location: /list/server/");
exit;
