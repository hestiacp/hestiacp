<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit();
}

$domain = $_POST['domain'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v-delete-web-domain';
                        $cmd_dns='v-delete-dns-domain';
                        $cmd_mail='v-delete-mail-domain';
            break;
        case 'suspend': $cmd='v-suspend-web-domain';
                        $cmd_dns='v-suspend-dns-domain';
                        $cmd_mail='v-suspend-mail-domain';
            break;
        case 'unsuspend': $cmd='v-unsuspend-web-domain';
                        $cmd_dns='v-unsuspend-dns-domain';
                        $cmd_mail='v-unsuspend-mail-domain';
            break;
        default: header("Location: /list/web/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v-delete-web-domain';
                        $cmd_dns='v-delete-dns-domain';
                        $cmd_mail='v-delete-mail-domain';
            break;
        default: header("Location: /list/web/"); exit;
    }
}

foreach ($domain as $value) {
    // WEB
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value." no", $output, $return_var);
    $restart_web = 'yes';

    // DNS
    if ($return_var == 0) {
        exec (VESTA_CMD."v-list-dns-domain ".$user." ".$value." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD.$cmd_dns." ".$user." ".$value." no", $output, $return_var);
            $restart_dns = 'yes';
        }
    }

    // Mail
    if ($return_var == 0) {
        exec (VESTA_CMD."v-list-mail-domain ".$user." ".$value." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD.$cmd_mail." ".$user." ".$value." no", $output, $return_var);
        }
    }
}

if (!empty($restart_web)) {
    exec (VESTA_CMD."v-restart-web", $output, $return_var);
}

if (!empty($restart_dns)) {
    exec (VESTA_CMD."v-restart-dns", $output, $return_var);
}

header("Location: /list/web/");
