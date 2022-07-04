<?php

// Init
ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

// Mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account']))) {
    $v_domain = quoteshellarg($_GET['domain']);
    exec(HESTIA_CMD."v-unsuspend-mail-domain ".$user." ".$v_domain, $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) {
            $error = _('Error: Hestia did not return any output.');
        }
        $_SESSION['error_msg'] = $error;
    }
    unset($output);
    $back=getenv("HTTP_REFERER");
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/");
    exit;
}

// Mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account']))) {
    $v_username = quoteshellarg($user);
    $v_domain = quoteshellarg($_GET['domain']);
    $v_account = quoteshellarg($_GET['account']);
    exec(HESTIA_CMD."v-unsuspend-mail-account ".$user." ".$v_domain." ".$v_account, $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) {
            $error = _('Error: Hestia did not return any output.');
        }
        $_SESSION['error_msg'] = $error;
    }
    unset($output);
    $back=getenv("HTTP_REFERER");
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /list/mail/?domain=".$_GET['domain']);
    exit;
}

$back=getenv("HTTP_REFERER");
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/mail/");
exit;
