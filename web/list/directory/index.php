<?php
error_reporting(NULL);

include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

// Check login_as feature
if (($_SESSION['user'] == 'admin') && (!empty($_SESSION['look']))) {
    $user=$_SESSION['look'];
}

if (empty($panel)) {
    $command = HESTIA_CMD."v-list-user ".escapeshellarg($user)." 'json'";
    exec ($command, $output, $return_var);
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
    $panel = json_decode(implode('', $output), true);
}

$path_a = !empty($_REQUEST['dir_a']) ? htmlentities($_REQUEST['dir_a']) : '';
$path_b = !empty($_REQUEST['dir_b']) ? htmlentities($_REQUEST['dir_b']) : '';
$GLOBAL_JS  = '<script type="text/javascript">GLOBAL.START_DIR_A = "' . $path_a . '";</script>';
$GLOBAL_JS .= '<script type="text/javascript">GLOBAL.START_DIR_B = "' . $path_b . '";</script>';
$GLOBAL_JS .= '<script type="text/javascript">GLOBAL.ROOT_DIR = "' . $panel[$user]['HOME'] . '";</script>';
