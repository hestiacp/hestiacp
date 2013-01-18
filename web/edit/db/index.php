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

    // Check user argument?
    if (empty($_GET['database'])) {
        header("Location: /list/db/");
        exit;
    }

    // Edit as someone else?
    if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
        $user=escapeshellarg($_GET['user']);
    }

    $v_database = escapeshellarg($_GET['database']);
    exec (VESTA_CMD."v-list-database ".$user." ".$v_database." 'json'", $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = _('Error: vesta did not return any output.');
        $_SESSION['error_msg'] = $error;
    } else {
        $data = json_decode(implode('', $output), true);
        unset($output);
        $v_username = $user;
        $v_database = $_GET['database'];
        $v_dbuser = $data[$v_database]['DBUSER'];
        $v_password = "••••••••";
        $v_host = $data[$v_database]['HOST'];
        $v_type = $data[$v_database]['TYPE'];
        $v_charset = $data[$v_database]['CHARSET'];
        $v_date = $data[$v_database]['DATE'];
        $v_time = $data[$v_database]['TIME'];
        $v_suspended = $data[$v_database]['SUSPENDED'];
        if ( $v_suspended == 'yes' ) {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }

        // Action
        if (!empty($_POST['save'])) {
            $v_username = $user;
            // Change password
            if (($v_password != $_POST['v_password']) && (empty($_SESSION['error_msg']))) {
                $v_password = escapeshellarg($_POST['v_password']);
                exec (VESTA_CMD."v-change-database-password ".$v_username." ".$v_database." ".$v_password, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                $v_password = "••••••••";
                unset($output);
            }
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = _('OK: changes has been saved.');
            }
        }
    }

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_db.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
