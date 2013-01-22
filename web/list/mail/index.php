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

$lang = 'ru_RU.utf8';
setlocale(LC_ALL, $lang);

// Data
if (empty($_GET['domain'])){
    exec (VESTA_CMD."v-list-mail-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data);
    unset($output);
    if ($_SESSION['user'] == 'admin') {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_mail.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_mail.html');
    }
} else {
    exec (VESTA_CMD."v-list-mail-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data);
    unset($output);
    if ($_SESSION['user'] == 'admin') {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_mail_acc.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_mail_acc.html');
    }
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
