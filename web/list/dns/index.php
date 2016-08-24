<?php
error_reporting(NULL);
$TAB = 'DNS';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Data & Render page
if (empty($_GET['domain'])){
    exec (VESTA_CMD."v-list-dns-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);

    render_page($user, $TAB, 'list_dns');
} else {
    exec (VESTA_CMD."v-list-dns-records '".$user."' '".escapeshellarg($_GET['domain'])."' 'json'", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);

    render_page($user, $TAB, 'list_dns_rec');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
