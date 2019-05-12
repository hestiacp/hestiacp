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

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check package argument
if (empty($_GET['package'])) {
    header("Location: /list/package/");
    exit;
}

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['package'])) {
        $v_package = escapeshellarg($_GET['package']);
        exec (HESTIA_CMD."v-copy-user-package ".$v_package." ".$v_package."-copy", $output, $return_var);
    }

    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = 'Error: unable to copy package.';
            $_SESSION['error_msg'] = $error;
    }
    unset($output);
}

header("Location: /list/package/");
exit;
