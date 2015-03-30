<?php
session_start();
$TAB = 'DNS';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if (empty($_GET['domain'])){
    exec (VESTA_CMD."v-list-dns-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);
    if ($_SESSION['user'] == 'admin') {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_dns.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_dns.html');
    }
} else {
    exec (VESTA_CMD."v-list-dns-records '".$user."' '".escapeshellarg($_GET['domain'])."' 'json'", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);
    if ($_SESSION['user'] == 'admin') {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_dns_rec.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_dns_rec.html');
    }
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
