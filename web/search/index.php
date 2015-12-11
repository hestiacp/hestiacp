<?php
// Init
error_reporting(NULL);
session_start();
$TAB = 'SEARCH';
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check query
$q = $_GET['q'];
if (empty($q)) {
    $back=getenv("HTTP_REFERER");
    if (!empty($back)) {
        header("Location: ".$back);
        exit;
    }
    header("Location: /");
    exit;
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

$lang = 'ru_RU.utf8';
//setlocale(LC_ALL, $lang);

// Data
if ($_SESSION['user'] == 'admin') {
    $q = escapeshellarg($q);
    exec (VESTA_CMD."v-search-object ".$q." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_search.html');
} else {
    exec (VESTA_CMD."v-search-user-object ".$user." ".$q." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_search.html');
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
