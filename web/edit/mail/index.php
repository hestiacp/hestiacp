<?php
// Init
//error_reporting(NULL);
ob_start();
session_start();

$TAB = 'MAIL';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {

    // Check user argument?
    if (empty($_GET['domain'])) {
        header("Location: /list/mail/");
    }

    if (!empty($_POST['cancel'])) {
        header("Location: /list/mail/");
    }

    // Check domain
    if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
        $v_domain = escapeshellarg($_GET['domain']);
        exec (VESTA_CMD."v_list_mail_domain ".$user." ".$v_domain." json", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        } else {
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_username = $user;
            $v_domain = $_GET['domain'];
            $v_antispam = $data[$v_domain]['ANTISPAM'];
            $v_antivirus = $data[$v_domain]['ANTIVIRUS'];
            $v_dkim = $data[$v_domain]['DKIM'];
            $v_catchall = $data[$v_domain]['CATCHALL'];
            $v_date = $data[$v_domain]['DATE'];
            $v_time = $data[$v_domain]['TIME'];
            $v_suspended = $data[$v_domain]['SUSPENDED'];
            if ( $v_suspended == 'yes' ) {
                $v_status =  'suspended';
            } else {
                $v_status =  'active';
            }
        }

        // Action
        if (!empty($_POST['save'])) {
            $v_domain = escapeshellarg($_POST['v_domain']);

            // Antispam
            if (($v_antispam == 'yes') && (empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_delete_mail_domain_antispam ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_antispam = 'no';
                unset($output);
            }
            if (($v_antispam == 'no') && (!empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_add_mail_domain_antispam ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_antispam = 'yes';
                unset($output);
            }

            // Antivirus
            if (($v_antivirus == 'yes') && (empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_delete_mail_domain_antivirus ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_antivirus = 'no';
                unset($output);
            }
            if (($v_antivirus == 'no') && (!empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_add_mail_domain_antivirus ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_antivirus = 'yes';
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = "OK: changes has been saved.";
            }

            // DKIM
            if (($v_dkim == 'yes') && (empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_delete_mail_domain_dkim ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_dkim = 'no';
                unset($output);
            }
            if (($v_dkim == 'no') && (!empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_add_mail_domain_dkim ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_dkim = 'yes';
                unset($output);
            }

            // Catchall
            if ((!empty($v_catchall)) && (empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_delete_mail_domain_catchall ".$v_username." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $v_catchall = '';
                unset($output);
            }

            if ((!empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
                if ($v_catchall != $_POST['v_catchall']) {
                    $v_catchall = escapeshellarg($_POST['v_catchall']);
                    exec (VESTA_CMD."v_change_mail_domain_catchall ".$v_username." ".$v_domain." ".$v_catchall, $output, $return_var);
                    if ($return_var != 0) {
                        $error = implode('<br>', $output);
                        if (empty($error)) $error = 'Error: vesta did not return any output.';
                        $_SESSION['error_msg'] = $error;
                    }
                    unset($output);
                }
            }

            if ((empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
                $v_catchall = escapeshellarg($_POST['v_catchall']);
                exec (VESTA_CMD."v_add_mail_domain_catchall ".$v_username." ".$v_domain." ".$v_catchall, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = "OK: changes has been saved.";
            }
        }
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_edit_mail.html');
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_mail.html');
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    } else {
        $v_domain = escapeshellarg($_GET['domain']);
        $v_record_id = escapeshellarg($_GET['record_id']);
        exec (VESTA_CMD."v_list_dns_domain_records ".$user." ".$v_domain." 'json'", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        } else {
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_username = $user;
            $v_domain = $_GET['domain'];
            $v_d = $_GET['record_id'];
            $v_rec = $data[$v_record_id]['RECORD'];
            $v_type = $data[$v_record_id]['TYPE'];
            $v_val = $data[$v_record_id]['VALUE'];
            $v_priority = $data[$v_record_id]['PRIORITY'];
            $v_suspended = $data[$v_record_id]['SUSPENDED'];
            if ( $v_suspended == 'yes' ) {
                $v_status =  'suspended';
            } else {
                $v_status =  'active';
            }
            $v_date = $data[$v_record_id]['DATE'];
            $v_time = $data[$v_record_id]['TIME'];
        }

        // Action
        if (!empty($_POST['save'])) {
            $v_domain = escapeshellarg($_POST['v_domain']);
            $v_record_id = escapeshellarg($_POST['v_record_id']);

            if (($v_val != $_POST['v_val']) || ($v_priority != $_POST['v_priority']) && (empty($_SESSION['error_msg']))) {
                $v_val = escapeshellarg($_POST['v_val']);
                $v_priority = escapeshellarg($_POST['v_priority']);
                exec (VESTA_CMD."v_change_dns_domain_record ".$v_username." ".$v_domain." ".$v_record_id." ".$v_val." ".$v_priority, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                $restart_dns = 'yes';
                unset($output);
            }
    
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = "OK: changes has been saved.";
            }

        }
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_edit_dns_rec.html');
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_dns_rec.html');
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
