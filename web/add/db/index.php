<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'DB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
//if ($_SESSION['user'] == 'admin') {
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_database'])) $errors[] = _('database');
        if (empty($_POST['v_dbuser'])) $errors[] = _('username');
        if (empty($_POST['v_password'])) $errors[] = _('password');
        if (empty($_POST['v_type'])) $errors[] = _('type');
        if (empty($_POST['v_charset'])) $errors[] = _('charset');

        // Protect input
        $v_database = escapeshellarg($_POST['v_database']);
        $v_dbuser = escapeshellarg($_POST['v_dbuser']);
        $v_password = escapeshellarg($_POST['v_password']);
        $v_type = $_POST['v_type'];
        $v_charset = $_POST['v_charset'];

        // Check for errors
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = _('Error: field "%s" can not be blank.',$error_msg);
        } else {
            // Add Database
            $v_type = escapeshellarg($_POST['v_type']);
            $v_charset = escapeshellarg($_POST['v_charset']);
            exec (VESTA_CMD."v-add-database ".$user." ".$v_database." ".$v_dbuser." ".$v_password." ".$v_type." 'default' ".$v_charset, $output, $return_var);
            $v_type = $_POST['v_type'];
            $v_charset = $_POST['v_charset'];
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = _('Error: vesta did not return any output.');
                $_SESSION['error_msg'] = $error;
                unset($v_password);
                unset($output);
            } else {
                $_SESSION['ok_msg'] = _('DATABASE_CREATED_OK',$user."_".$_POST['v_database'],$user."_".$_POST['v_database']);
                unset($v_database);
                unset($v_dbuser);
                unset($v_password);
                unset($v_type);
                unset($v_charset);
                unset($output);
            }
        }
    }
    exec (VESTA_CMD."v-list-database-types 'json'", $output, $return_var);
    $db_types = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_db.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
