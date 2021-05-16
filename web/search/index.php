<?php
// Init
error_reporting(NULL);
$TAB = 'SEARCH';

$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check query
$q = $_GET['q'];
$u = $_GET['u'];

#if (empty($q)) {
#    $back=getenv("HTTP_REFERER");
#    if (!empty($back)) {
#        header("Location: ".$back);
#        exit;
#    }
#    header("Location: /");
#    exit;
#}

// Data
$q = escapeshellarg($q);
$u = escapeshellarg($u);

if (($_SESSION['userContext'] === 'admin') && (!isset($_SESSION['look']))) {
    if (!empty($_GET['u'])) {
        $user = $u;
        exec (HESTIA_CMD . "v-search-user-object " .$user. " " .$q. " json", $output, $return_var);
    } else {
        exec (HESTIA_CMD . "v-search-object " .$q. " json", $output, $return_var);
    }
} else {
    exec (HESTIA_CMD . "v-search-user-object " .$user. " " .$q. " json", $output, $return_var);
}

$data = json_decode(implode('', $output), true);

// Render page
render_page($user, $TAB, 'list_search');
