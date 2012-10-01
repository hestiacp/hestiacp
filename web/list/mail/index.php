<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'MAIL';
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {

    if (empty($_GET['domain'])){
        exec (VESTA_CMD."v_list_mail_domains $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data);
        unset($output);
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_mail.html');
    } else {
        exec (VESTA_CMD."v_list_mail_accounts '".$user."' '".$_GET['domain']."' 'json'", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data);
        unset($output);
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_mail_acc.html');
    }
} else {
    if (empty($_GET['domain'])){
        exec (VESTA_CMD."v_list_mail_domains $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data);
        unset($output);

        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_mail.html');
    } else {
        exec (VESTA_CMD."v_list_mail_accounts '".$user."' '".$_GET['domain']."' 'json'", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data);
        unset($output);
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_mail_acc.html');
    }
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
