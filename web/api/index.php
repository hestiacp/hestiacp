<?php
//die("Error: Disabled");
define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

include($_SERVER['DOCUMENT_ROOT']."/inc/helpers.php");

/**
 * Displays the error message, checks the proper code and saves a log if needed.
 *
 * @param int $exit_code
 * @param string $message
 * @param bool $add_log
 * @param string $user
 * @return void
 */
function api_error($exit_code, $message, bool $add_log = false, $user = 'system') {
    $message = trim(is_array($message) ? implode("\n", $message) : $message);

    // Add log
    if ($add_log) {
        $v_real_user_ip = get_real_user_ip();
        hst_add_history_log("[$v_real_user_ip] $message", 'API', 'Error', $user);
    }

    // Print the message with http_code and exit_code
    $http_code = ($exit_code >= 100) ? $exit_code : exit_code_to_http_code($exit_code);
    header("Hestia-Exit-Code: $exit_code");
    http_response_code($http_code);
    echo (!preg_match('/^Error:/', $message)) ? "Error: $message" : $message;
    exit;
}

/**
 * Legacy connection format using hash or user and password.
 *
 * @param string $hst_hash
 * @param string $hst_user
 * @param string $hst_password
 * @param string $hst_returncode
 * @param string $hst_cmd
 * @param string $hst_arg1
 * @param string $hst_arg2
 * @param string $hst_arg3
 * @param string $hst_arg4
 * @param string $hst_arg5
 * @param string $hst_arg6
 * @param string $hst_arg7
 * @param string $hst_arg8
 * @param string $hst_arg9
 * @return void
 */
function api_legacy($hst_hash, $hst_user, $hst_password, $hst_returncode, $hst_cmd, $hst_arg1, $hst_arg2, $hst_arg3, $hst_arg4, $hst_arg5, $hst_arg6, $hst_arg7, $hst_arg8, $hst_arg9) {
    exec(HESTIA_CMD."v-list-sys-config json", $output, $return_var);
    $settings = json_decode(implode('', $output), true);
    unset($output);

    if ($settings['config']['API'] != 'yes') {
        echo 'Error: API has been disabled';
        exit;
    }

    if ($settings['config']['API_ALLOWED_IP'] != 'allow-all') {
        $ip_list = explode(',', $settings['config']['API_ALLOWED_IP']);
        $ip_list[] = '';
        if (!in_array(get_real_user_ip(), $ip_list)) {
            echo 'Error: IP is not allowed to connect with API';
            exit;
        }
    }

    //This exists, so native JSON can be used without the repeating the code twice, so future code changes are easier and don't need to be replicated twice
    // Authentication
    if (empty($hst_hash)) {
        if ($hst_user != 'admin') {
            echo 'Error: authentication failed';
            exit;
        }

        $password = $hst_password;
        if (!isset($password)) {
            echo 'Error: missing authentication';
            exit;
        }
        $v_ip = escapeshellarg(get_real_user_ip());
        $output = '';
        exec(HESTIA_CMD."v-get-user-salt admin ".$v_ip." json", $output, $return_var);
        $pam = json_decode(implode('', $output), true);
        $salt = $pam['admin']['SALT'];
        $method = $pam['admin']['METHOD'];

        if ($method == 'md5') {
            $hash = crypt($password, '$1$'.$salt.'$');
        }
        if ($method == 'sha-512') {
            $hash = crypt($password, '$6$rounds=5000$'.$salt.'$');
            $hash = str_replace('$rounds=5000', '', $hash);
        }
        if ($method == 'des') {
            $hash = crypt($password, $salt);
        }

        // Send hash via tmp file
        $v_hash = exec('mktemp -p /tmp');
        $fp = fopen($v_hash, "w");
        fwrite($fp, $hash."\n");
        fclose($fp);

        // Check user hash
        exec(HESTIA_CMD."v-check-user-hash admin ".$v_hash." ".$v_ip, $output, $return_var);
        unset($output);

        // Remove tmp file
        unlink($v_hash);

        // Check API answer
        if ($return_var > 0) {
            echo 'Error: authentication failed';
            exit;
        }
    } else {
        $key = '/usr/local/hestia/data/keys/'.basename($hst_hash);
        $v_ip = escapeshellarg(get_real_user_ip());
        exec(HESTIA_CMD."v-check-api-key ".escapeshellarg($key)." ".$v_ip, $output, $return_var);
        unset($output);
        // Check API answer
        if ($return_var > 0) {
            echo 'Error: authentication failed';
            exit;
        }
    }

    // Prepare arguments
    if (isset($hst_cmd)) $cmd = escapeshellarg($hst_cmd);
    if (isset($hst_arg1)) $arg1 = escapeshellarg($hst_arg1);
    if (isset($hst_arg2)) $arg2 = escapeshellarg($hst_arg2);
    if (isset($hst_arg3)) $arg3 = escapeshellarg($hst_arg3);
    if (isset($hst_arg4)) $arg4 = escapeshellarg($hst_arg4);
    if (isset($hst_arg5)) $arg5 = escapeshellarg($hst_arg5);
    if (isset($hst_arg6)) $arg6 = escapeshellarg($hst_arg6);
    if (isset($hst_arg7)) $arg7 = escapeshellarg($hst_arg7);
    if (isset($hst_arg8)) $arg8 = escapeshellarg($hst_arg8);
    if (isset($hst_arg9)) $arg9 = escapeshellarg($hst_arg9);

    // Build query
    $cmdquery = HESTIA_CMD.$cmd." ";
    if (!empty($arg1)) {
        $cmdquery = $cmdquery.$arg1." ";
    }
    if (!empty($arg2)) {
        $cmdquery = $cmdquery.$arg2." ";
    }
    if (!empty($arg3)) {
        $cmdquery = $cmdquery.$arg3." ";
    }
    if (!empty($arg4)) {
        $cmdquery = $cmdquery.$arg4." ";
    }
    if (!empty($arg5)) {
        $cmdquery = $cmdquery.$arg5." ";
    }
    if (!empty($arg6)) {
        $cmdquery = $cmdquery.$arg6." ";
    }
    if (!empty($arg7)) {
        $cmdquery = $cmdquery.$arg7." ";
    }
    if (!empty($arg8)) {
        $cmdquery = $cmdquery.$arg8." ";
    }
    if (!empty($arg9)) {
        $cmdquery = $cmdquery.$arg9;
    }

    // Check command
    if ($cmd == "'v-make-tmp-file'") {
        // Used in DNS Cluster
        $fp = fopen('/tmp/'.basename($hst_arg2), 'w');
        fwrite($fp, $hst_arg1."\n");
        fclose($fp);
        $return_var = 0;
    } else {
        // Run normal cmd query
        exec($cmdquery, $output, $return_var);
    }

    if ((!empty($hst_returncode)) && ($hst_returncode == 'yes')) {
        echo $return_var;
    } else {
        if (($return_var == 0) && (empty($output))) {
            echo "OK";
        } else {
            echo implode("\n", $output)."\n";
        }
    }

    exit;
}

/**
 * Connection using access key.
 *
 * @param array{access_key: string, secret_key: string, cmd: string, arg1?: string, arg2?: string, arg3?: string, arg4?: string, arg5?: string, arg6?: string, arg7?: string, arg8?: string, arg9?: string, returncode?: string} $request_data
 * @return void
 */
function api_connection(array $request_data) {
    $v_real_user_ip = get_real_user_ip();

    exec(HESTIA_CMD."v-list-sys-config json", $output, $return_var);
    $settings = json_decode(implode('', $output), true);
    unset($output, $return_var);

    // Get the status of api
    $api_status = (!empty($settings['config']['API_SYSTEM']) && is_numeric($settings['config']['API_SYSTEM'])) ? $settings['config']['API_SYSTEM'] : 0;
    if ($api_status == 0) {
        // Check if API is disabled for all users
        api_error(E_DISABLED, "API has been disabled");
    }

    // Check if API access is enabled for the user
    if ($settings['config']['API_ALLOWED_IP'] != 'allow-all') {
        $ip_list = explode(',', $settings['config']['API_ALLOWED_IP']);
        $ip_list[] = '';
        if (!in_array($v_real_user_ip, $ip_list) && !in_array('0.0.0.0', $ip_list)) {
            api_error(E_FORBIDDEN, "IP is not allowed to connect with API");
        }
    }

    // Get POST Params
    $hst_access_key_id = trim($request_data['access_key'] ?? '');
    $hst_secret_access_key = trim($request_data['secret_key'] ?? '');
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
    exec(HESTIA_CMD."v-check-access-key ".escapeshellarg($hst_access_key_id)." ".escapeshellarg($hst_secret_access_key)." ".escapeshellarg($hst_cmd)." ".escapeshellarg($v_real_user_ip)." json", $output, $return_var);
    if ($return_var > 0) {
        //api_error($return_var, "Key $hst_access_key_id - authentication failed");
        api_error($return_var, $output);
    }
    $key_data = json_decode(implode('', $output), true) ?? [];
    unset($output, $return_var);

    $key_user = $key_data['USER'];
    $user_arg_position = (isset($key_data['USER_ARG_POSITION']) && is_numeric($key_data['USER_ARG_POSITION'])) ? $key_data['USER_ARG_POSITION'] : -1;

    # Check if API access is enabled for nonadmin users
    if ($key_user != 'admin' && $api_status < 2) {
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

    exit;
}

// Get request data
if (isset($_POST['access_key']) || isset($_POST['user']) || isset($_POST['hash'])) {
    $request_data = $_POST;
} else if (($json_data = json_decode(file_get_contents("php://input"), true)) != null) {
    $request_data = $json_data;
} else {
    api_error(405, "Error: data received is null or invalid, check https://docs.hestiacp.com/admin_docs/api.html");
}

// Try to get access key in the hash
if (!isset($request_data['access_key']) && isset($request_data['hash']) && substr_count($request_data['hash'], ':') == 1) {
    $hash_parts = explode(':', $request_data['hash']);
    if (strlen($hash_parts[0]) == 20 && strlen($hash_parts[1]) == 40) {
        $request_data['access_key'] = $hash_parts[0];
        $request_data['secret_key'] = $hash_parts[1];
        unset($request_data['hash']);
    }
}

// Check data format
if (isset($request_data['access_key']) && isset($request_data['secret_key'])) {
    api_connection($request_data);
} else if (isset($request_data['user']) || isset($request_data['hash'])) {
    $array = array('user', 'password', 'hash', 'returncode', 'cmd', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7', 'arg8', 'arg9');
    foreach ($array as $key) {
        if (empty($request_data[$key])) {
            $request_data[$key] = '';
        }
    }

    api_legacy($request_data['hash'], $request_data['user'], $request_data['password'], $request_data['returncode'], $request_data['cmd'], $request_data['arg1'], $request_data['arg2'], $request_data['arg3'], $request_data['arg4'], $request_data['arg5'], $request_data['arg6'], $request_data['arg7'], $request_data['arg8'], $request_data['arg9']);
} else {
    api_error(405, "Error: data received is null or invalid, check https://docs.hestiacp.com/admin_docs/api.html");
}
