<?php

$TAB = 'Access Key';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

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

    // APIs
    exec(HESTIA_CMD."v-list-apis json", $output, $return_var);
    $apis = json_decode(implode('', $output), true);
    $apis = array_filter($apis, function ($api) use ($user_plain) {
        return ($user_plain == 'admin' || $api['ROLE'] == 'user');
    });
    ksort($apis);
    unset($output);

    render_page($user, $TAB, 'list_access_key');
} else {
    exec(HESTIA_CMD."v-list-access-keys json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_filter($data, function ($item) use ($user_plain) {
        $key_user = !empty($item['USER']) ? $item['USER'] : '';
        return ($key_user == $user_plain);
    });

    uasort($data, function ($a, $b) {
        return $a['DATE'] <=> $b['DATE'] ?: $a['TIME'] <=> $b['TIME'];
    });
    unset($output);

    // Render page
    render_page($user, $TAB, 'list_access_keys');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
