<?php
session_start();

// Check user
if (!isset($_SESSION['user'])) {
    header("Location: /login/");
}

// Set vairables
date_default_timezone_set('UTC');
$user = $_SESSION['user'];
$vesta_cmd="/usr/bin/sudo /usr/local/vesta/bin/";
$TAB = 'PACKAGES';

// Define functions
require_once '../../inc/main.php';

// Header
require_once '../../templates/header.html';

// Top Menu
$command = "$vesta_cmd"."v_list_user '".$_SESSION['user']."' 'json'";
exec ($command, $output, $return_var);
if ( $return_var > 0 ) {
    header("Location: /error/");
}
$panel = json_decode(implode('', $output), true);
if ( $_SESSION['user'] == 'admin' ) {
    require_once '../../templates/admin/panel.html';
} else {
    require_once '../../templates/header.html';
}

require_once '../../templates/footer.html';
