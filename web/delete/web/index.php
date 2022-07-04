<?php

ob_start();
include($_SERVER['DOCUMENT_ROOT'] . '/inc/main.php');

// Check token
verify_csrf($_GET);

// Delete as someone else?
if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user = quoteshellarg($user);
}

if (!empty($_GET['domain'])) {
    $v_domain = quoteshellarg($_GET['domain']);
    exec(HESTIA_CMD . 'v-delete-web-domain ' . $user . ' ' . $v_domain . " 'yes'", $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header('Location: ' . $back);
    exit;
}

header('Location: /list/web/');
exit;
