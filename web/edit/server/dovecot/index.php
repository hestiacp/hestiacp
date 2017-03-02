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
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot ".$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config1
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config1']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config1']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-1 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config2
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config2']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config2']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-2 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config3
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config3']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config3']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-3 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config4
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config4']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config4']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-4 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config5
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config5']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config5']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-5 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config6
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config6']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config6']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-6 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config7
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config7']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config7']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-7 " .$v_restart, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($new_conf);
    }

    // Update config8
    if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config8']))) {
        exec ('mktemp', $mktemp_output, $return_var);
        $new_conf = $mktemp_output[0];
        $fp = fopen($new_conf, 'w');
        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config8']));
        fclose($fp);
        exec (VESTA_CMD."v-change-sys-service-config ".$new_conf." dovecot-8 " .$v_restart, $output, $return_var);
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
exec (VESTA_CMD."v-list-sys-dovecot-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);

$v_config_path = $data['CONFIG']['config_path'];
$v_config_path1 = $data['CONFIG']['config_path1'];
$v_config_path2 = $data['CONFIG']['config_path2'];
$v_config_path3 = $data['CONFIG']['config_path3'];
$v_config_path4 = $data['CONFIG']['config_path4'];
$v_config_path5 = $data['CONFIG']['config_path5'];
$v_config_path6 = $data['CONFIG']['config_path6'];
$v_config_path7 = $data['CONFIG']['config_path7'];
$v_config_path8 = $data['CONFIG']['config_path8'];
$v_service_name = strtoupper('dovecot');

// Read config
$v_config = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path);
if (!empty($v_config_path1)) $v_config1 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path1);
if (!empty($v_config_path2)) $v_config2 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path2);
if (!empty($v_config_path3)) $v_config3 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path3);
if (!empty($v_config_path4)) $v_config4 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path4);
if (!empty($v_config_path5)) $v_config5 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path5);
if (!empty($v_config_path6)) $v_config6 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path6);
if (!empty($v_config_path7)) $v_config7 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path7);
if (!empty($v_config_path8)) $v_config8 = shell_exec(VESTA_CMD."v-open-fs-config ".$v_config_path8);

// Render page
render_page($user, $TAB, 'edit_server_dovecot');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
