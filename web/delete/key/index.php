<?php

ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user = $_GET['user'];
}

if (!empty($_GET['key'])) {
    $v_key = escapeshellarg(trim($_GET['key']));
    $v_user = escapeshellarg(trim($user));
    exec(HESTIA_CMD."v-delete-user-ssh-key ".$v_user." ".$v_key);
    check_return_code($return_var, $output);
}

unset($output);

//die();
$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}
header("Location: /list/key/");
exit;
