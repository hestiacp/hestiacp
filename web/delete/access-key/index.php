<?php

ob_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_GET);

if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user = escapeshellarg($_GET['user']);
    $user_plain = $_GET['user'];
}

// Checks if API V2 is enabled
$api_v2_status = (!empty($_SESSION['API_V2']) && is_numeric($_SESSION['API_V2'])) ? $_SESSION['API_V2'] : 0;
if (($user_plain == 'admin' && $api_v2_status < 1) || ($user_plain != 'admin' && $api_v2_status < 2)) {
    header("Location: /edit/user/");
    exit;
}

if (!empty($_GET['key'])) {
    $v_key = escapeshellarg(trim($_GET['key']));

    // Key data
    exec(HESTIA_CMD."v-list-access-key ".$v_key." json", $output, $return_var);
    $key_data = json_decode(implode('', $output), true);
    unset($output);

    if (empty($key_data) || $key_data['USER'] != $user_plain) {
        header("Location: /list/access-key/");
        exit;
    }

    exec(HESTIA_CMD."v-delete-access-key ".$v_key, $output, $return_var);
    check_return_code($return_var, $output);
    unset($output);
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}
header("Location: /list/key/");
exit;
