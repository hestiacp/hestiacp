<?php
$TAB = 'DNS';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Data & Render page
if (empty($_GET['domain'])){
    exec (HESTIA_CMD."v-list-dns-domains ".$user." 'json'", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    if($_SESSION['userSortOrder'] == 'name'){
        ksort($data);
    }else{ 
        $data = array_reverse($data,true);
    }
    unset($output);

    render_page($user, $TAB, 'list_dns');
} else {
    exec (HESTIA_CMD."v-list-dns-records ".$user." ".quoteshellarg($_GET['domain'])." 'json'", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    if($_SESSION['userSortOrder'] == 'name'){
        ksort($data);
    }else{ 
        $data = array_reverse($data,true);
    }
    unset($output);

    render_page($user, $TAB, 'list_dns_rec');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
