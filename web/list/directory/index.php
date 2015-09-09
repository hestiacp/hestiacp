<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

if (empty($panel)) {
    $command = VESTA_CMD."v-list-user '".$user."' 'json'";
    exec ($command, $output, $return_var);
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
    $panel = json_decode(implode('', $output), true);
}

$path_a = !empty($_REQUEST['dir_a']) ? $_REQUEST['dir_a'] : '';
$path_b = !empty($_REQUEST['dir_b']) ? $_REQUEST['dir_b'] : '';
$GLOBAL_JS  = '<script type="text/javascript">GLOBAL.START_DIR_A = "' . $path_a . '";</script>';
$GLOBAL_JS .= '<script type="text/javascript">GLOBAL.START_DIR_B = "' . $path_b . '";</script>';
$GLOBAL_JS .= '<script type="text/javascript">GLOBAL.ROOT_DIR = "' . $panel[$user]['HOME'] . '";</script>';

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/file_manager/main.php');
