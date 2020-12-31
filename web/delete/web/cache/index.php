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

// Delete as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];
}

if (!empty($_GET['domain'])) {
    $v_username = escapeshellarg($user);
    $v_domain = escapeshellarg($_GET['domain']);
    exec (HESTIA_CMD."v-purge-web-domain-nginx-cache ".$v_username." ".$v_domain, $output, $return_var);
    check_return_code($return_var,$output);
}
$_SESSION['ok_msg'] = _('Nginx cache has been sucessfully purged');
header("Location: /edit/web/?domain=".$_GET['domain']);
exit;
