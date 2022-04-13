<?php

define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

include($_SERVER['DOCUMENT_ROOT']."/inc/helpers.php");

$v_real_user_ip = get_real_user_ip();
$v_ip = escapeshellarg($v_real_user_ip);

function api_error($exit_code, $message, bool $add_log = false, $user = 'system') {
    global $v_real_user_ip;

    $message = trim(is_array($message) ? implode("\n", $message) : $message);

    // Add log
    if ($add_log) {
        hst_add_history_log("[$v_real_user_ip] $message", 'API', 'Error', $user);
    }

    // Print the message with http_code and exit_code
    $http_code = ($exit_code >= 100) ? $exit_code : exit_code_to_http_code($exit_code);
    header("Hestia-Exit-Code: $exit_code");
    http_response_code($http_code);
    echo $message;
    exit;
}

// Check request format
if (isset($_POST['access_key_id'])) {
    $request_data = $_POST;
} else if (($json_data = json_decode(file_get_contents("php://input"), true)) != null) {
    $request_data = $json_data;
} else {
    api_error(405, "Data received is null or invalid, check https://docs.hestiacp.com/admin_docs/api.html");
}

exec(HESTIA_CMD."v-list-sys-config json", $output, $return_var);
$settings = json_decode(implode('', $output), true);
unset($output, $return_var);

// Get the status of api v2
$api_v2_status = (!empty($settings['config']['API_V2']) && is_numeric($settings['config']['API_V2'])) ? $settings['config']['API_V2'] : 0;

// Check if API is disabled for all users
if ($api_v2_status == 0) {
    api_error(E_DISABLED, "API has been disabled");
}

// Check if API V2 is enabled for the user
if ($settings['config']['API_ALLOWED_IP'] != 'allow-all') {
    $ip_list = explode(',', $settings['config']['API_ALLOWED_IP']);
    $ip_list[] = '';
    if (!in_array($v_real_user_ip, $ip_list) && !in_array('0.0.0.0', $ip_list)) {
        api_error(E_FORBIDDEN, "IP is not allowed to connect with API");
    }
}

// Get POST Params
$hst_access_key_id = trim($request_data['access_key_id'] ?? '');
$hst_secret_access_key = trim($request_data['secret_access_key'] ?? '');
$hst_return = (($request_data['returncode'] ?? 'no') === 'yes') ? 'code' : 'data';
$hst_cmd = trim($request_data['cmd'] ?? '');
$hst_cmd_args = [];
for ($i = 1; $i <= 9; $i++) {
    if (isset($request_data["arg{$i}"])) {
        $hst_cmd_args["arg{$i}"] = trim($request_data["arg{$i}"]);
    }
}

if (empty($hst_cmd)) {
    api_error(E_INVALID, "Command not provided");
} else if (!preg_match('/^[a-zA-Z0-9_-]+$/', $hst_cmd)) {
    api_error(E_INVALID, "$hst_cmd command invalid");
}

if (empty($hst_access_key_id) || empty($hst_secret_access_key)) {
    api_error(E_PASSWORD, "Authentication failed");
}

// Authenticates the key and checks permission to run the script
exec(HESTIA_CMD."v-check-access-key ".escapeshellarg($hst_access_key_id)." ".escapeshellarg($hst_secret_access_key)." ".escapeshellarg($hst_cmd)." $v_ip json", $output, $return_var);
if ($return_var > 0) {
    //api_error($return_var, "Key $hst_access_key_id - authentication failed");
    api_error($return_var, $output);
}
$key_data = json_decode(implode('', $output), true) ?? [];
unset($output, $return_var);

$key_user = $key_data['USER'];
$user_arg_position = (isset($key_data['USER_ARG_POSITION']) && is_numeric($key_data['USER_ARG_POSITION'])) ? $key_data['USER_ARG_POSITION'] : -1;

# Check if API connection is enabled
if (($key_user == 'admin' && $api_v2_status < 1) || ($key_user != 'admin' && $api_v2_status < 2)) {
    api_error(E_DISABLED, "API has been disabled");
}

// Checks if the value entered in the "user" argument matches the user of the key
if ($key_user != 'admin' && $user_arg_position > 0 && $hst_cmd_args["arg{$user_arg_position}"] != $key_user) {
    api_error(E_FORBIDDEN, "Key $hst_access_key_id - the \"user\" argument doesn\'t match the key\'s user");
}

// Prepare command
$cmdquery = HESTIA_CMD.escapeshellcmd($hst_cmd);

// Prepare arguments
foreach ($hst_cmd_args as $cmd_arg) {
    $cmdquery .= " ".escapeshellarg($cmd_arg);
}

// Run cmd query
exec($cmdquery, $output, $cmd_exit_code);
$cmd_output = trim(implode("\n", $output));
unset($output);

header("Hestia-Exit-Code: $cmd_exit_code");

if ($hst_return == 'code') {
    echo $cmd_exit_code;
} else {
    if ($cmd_exit_code > 0) {
        http_response_code(exit_code_to_http_code($cmd_exit_code));
    } else {
        http_response_code(!empty($cmd_output) ? 200 : 204);

        if (!empty($cmd_output) && json_decode($cmd_output, true)) {
            header('Content-Type: application/json; charset=utf-8');
        }
    }

    echo $cmd_output;
}
