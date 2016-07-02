<?php
// Init
error_reporting(NULL);
$TAB = 'SEARCH';

$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Main include
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

// Data
$q = escapeshellarg($q);
$command = $_SESSION['user'] == 'admin'
           ? "v-search-object $q json"
           : "v-search-user-object $user $q json";

exec (VESTA_CMD . $command, $output, $return_var);
$data = json_decode(implode('', $output), true);

// Render page
render_page($user, $TAB, 'list_search');
