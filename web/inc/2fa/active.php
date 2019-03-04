<?php

define('NO_AUTH_REQUIRED',true);

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (isset($_GET['user'])) {
    $v_user = escapeshellarg($_GET['user']);

    // Get user speciefic parameters
    exec (HESTIA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);

    // Check if 2FA is active
    if ($data[$_GET['user']]['TWOFA'] != '') {
        header("HTTP/1.0 200 OK");
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        exit;
    }
}