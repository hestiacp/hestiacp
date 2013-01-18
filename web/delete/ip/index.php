<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['ip'])) {
        $v_ip = escapeshellarg($_GET['ip']);
        exec (VESTA_CMD."v-delete-sys-ip ".$v_ip, $output, $return_var);
    }
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = _('Error: vesta did not return any output.');
            $_SESSION['error_msg'] = $error;
    }
    unset($output);

}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/ip/");
exit;
