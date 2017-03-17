<?php
error_reporting(NULL);
$TAB = 'SERVER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Set restart flag
    $v_restart = 'yes';
    if (empty($_POST['v_restart'])) $v_restart = 'no';

    // Update config
    if (!empty($_POST['v_config'])) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." nginx ".$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }

}

// List config
exec (VESTA_CMD."v-list-sys-nginx-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);
$v_worker_processes = $data['CONFIG']['worker_processes'];
$v_worker_connections = $data['CONFIG']['worker_connections'];
$v_send_timeout = $data['CONFIG']['send_timeout'];
$v_proxy_connect_timeout = $data['CONFIG']['proxy_connect_timeout'];
$v_proxy_send_timeout = $data['CONFIG']['proxy_send_timeout'];
$v_proxy_read_timeout = $data['CONFIG']['proxy_read_timeout'];
$v_client_max_body_size = $data['CONFIG']['client_max_body_size'];
$v_gzip = $data['CONFIG']['gzip'];
$v_gzip_comp_level = $data['CONFIG']['gzip_comp_level'];
$v_charset = $data['CONFIG']['charset'];
$v_config_path = $data['CONFIG']['config_path'];
$v_service_name = strtoupper('nginx');

// Read config
$v_config = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path);

// Render page
render_page($user, $TAB, 'edit_server_nginx');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
