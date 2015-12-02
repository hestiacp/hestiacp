<?php
error_reporting(NULL);

$TAB = 'DNS';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if (empty($_GET['domain'])){
    v_exec('v-list-dns-domains', [$user, 'json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
    if ($_SESSION['user'] == 'admin') {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_dns.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_dns.html');
    }
} else {
    v_exec('v-list-dns-records', [$user, $_GET['domain'], 'json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
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
