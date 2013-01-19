<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'MAIL';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
//if ($_SESSION['user'] == 'admin') {
    // Mail Domain
    if (!empty($_POST['ok'])) {
        if (empty($_POST['v_domain'])) $errors[] = _('domain');
        if (!empty($_POST['v_antispam'])) {
            $v_antispam = 'yes';
        } else {
            $v_antispam = 'no';
        }

        if (!empty($_POST['v_antivirus'])) {
            $v_antivirus = 'yes';
        } else {
            $v_antivirus = 'no';
        }

        if (!empty($_POST['v_dkim'])) {
            $v_dkim = 'yes';
        } else {
            $v_dkim = 'no';
        }

        // Protect input
        $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
        $v_domain = escapeshellarg($v_domain);

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

            // Add mail domain
            exec (VESTA_CMD."v-add-mail-domain ".$user." ".$v_domain." ".$v_antispam." ".$v_antivirus." ".$v_dkim, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = _('Error: vesta did not return any output.');
                $_SESSION['error_msg'] = $error;
            }
            unset($output);

            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = _("DOMAIN_MAIL_CREATED_OK",$_POST['v_domain'],$_POST['v_domain']);
                unset($v_domain);
            }
        }
    }


    // Mail Account
    if (!empty($_POST['ok_acc'])) {
        // Check input
        if (empty($_POST['v_domain'])) $errors[] = _('domain');
        if (empty($_POST['v_account'])) $errors[] = _('account');
        if (empty($_POST['v_password'])) $errors[] = _('password');

        // Protect input
        $v_domain = escapeshellarg($_POST['v_domain']);
        $v_account = escapeshellarg($_POST['v_account']);
        $v_password = escapeshellarg($_POST['v_password']);
        $v_quota = escapeshellarg($_POST['v_quota']);
        $v_aliases = $_POST['v_aliases'];
        $v_fwd = $_POST['v_fwd'];

        if (empty($_POST['v_quota'])) $v_quota = 0;
        if ((!empty($_POST['v_quota'])) || (!empty($_POST['v_aliases'])) || (!empty($_POST['v_fwd'])) ) $v_adv = 'yes';

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
            // Add Mail Account
            exec (VESTA_CMD."v-add-mail-account ".$user." ".$v_domain." ".$v_account." ".$v_password." ".$v_quota, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = _('Error: vesta did not return any output.');
                $_SESSION['error_msg'] = $error;
            }

            // Add Aliases
            if ((!empty($_POST['v_aliases'])) && (empty($_SESSION['error_msg']))) {
                $valiases = preg_replace("/\n/", " ", $_POST['v_aliases']);
                $valiases = preg_replace("/,/", " ", $valiases);
                $valiases = preg_replace('/\s+/', ' ',$valiases);
                $valiases = trim($valiases);
                $aliases = explode(" ", $valiases);
                foreach ($aliases as $alias) {
                    $alias = escapeshellarg($alias);
                    if (empty($_SESSION['error_msg'])) {
                        exec (VESTA_CMD."v-add-mail-account-alias ".$user." ".$v_domain." ".$v_account." ".$alias, $output, $return_var);
                        if ($return_var != 0) {
                            $error = implode('<br>', $output);
                            if (empty($error)) $error = _('Error: vesta did not return any output.');
                            $_SESSION['error_msg'] = $error;
                        }
                    }
                    unset($output);
                }
            }

            // Add Forwads
            if ((!empty($_POST['v_fwd'])) && (empty($_SESSION['error_msg']))) {
                $vfwd = preg_replace("/\n/", " ", $_POST['v_fwd']);
                $vfwd = preg_replace("/,/", " ", $vfwd);
                $vfwd = preg_replace('/\s+/', ' ',$vfwd);
                $vfwd = trim($vfwd);
                $fwd = explode(" ", $vfwd);
                foreach ($fwd as $forward) {
                    $forward = escapeshellarg($forward);
                    if (empty($_SESSION['error_msg'])) {
                        exec (VESTA_CMD."v-add-mail-account-forward ".$user." ".$v_domain." ".$v_account." ".$forward, $output, $return_var);
                        if ($return_var != 0) {
                            $error = implode('<br>', $output);
                            if (empty($error)) $error = _('Error: vesta did not return any output.');
                            $_SESSION['error_msg'] = $error;
                        }
                    }
                    unset($output);
                }
            }

            unset($output);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = _('MAIL_ACCOUNT_CREATED_OK',$_POST['v_account'],$_POST[v_domain],$_POST['v_account'],$_POST[v_domain]);
                unset($v_account);
                unset($v_password);
                unset($v_password);
                unset($v_aliases);
                unset($v_fwd);
                unset($v_quota);
            }
        }
    }


    if ((empty($_GET['domain'])) && (empty($_POST['domain'])))  {
        $v_domain = (isset($_GET['domain'])?$_GET['domain']:'');
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_mail.html');
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    } else {
        $v_domain = $_GET['domain'];
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_mail_acc.html');
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
