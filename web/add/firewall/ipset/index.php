<?php
error_reporting(NULL);
ob_start();
$TAB = 'FIREWALL';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Check empty fields
    if (empty($_POST['v_ipname'])) $errors[] = _('Name');
    if (empty($_POST['v_datasource'])) $errors[] = _('Data Source');
    if (empty($_POST['v_ipver'])) $errors[] = _('Ip Version');
    if (empty($_POST['v_autoupdate'])) $errors[] = _('Autoupdate');

    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = sprintf(_('Field "%s" can not be blank.'),$error_msg);
    }

    $v_ipname = $_POST['v_ipname'];
    $v_datasource = $_POST['v_datasource'];
    $v_ipver = $_POST['v_ipver'];
    $v_autoupdate = $_POST['v_autoupdate'];

    // Add firewall ipset list
    if (empty($_SESSION['error_msg'])) {
        exec (HESTIA_CMD."v-add-firewall-ipset ".escapeshellarg($v_ipname)." ".escapeshellarg($v_datasource)." ".escapeshellarg($v_ipver)." ".escapeshellarg($v_autoupdate), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = _('IPSET_CREATED_OK');
    }
}

// Render
render_page($user, $TAB, 'add_firewall_ipset');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
