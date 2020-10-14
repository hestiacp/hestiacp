<?php
error_reporting(NULL);
ob_start();
$TAB = 'MAIL';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check domain argument
if (empty($_GET['domain'])) {
    header("Location: /list/mail/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}
$v_username = $user;

// Get all user domains 
exec (HESTIA_CMD."v-list-mail-domains ".escapeshellarg($user)." json", $output, $return_var);
$user_domains = json_decode(implode('', $output), true);
$user_domains = array_keys($user_domains);
unset($output);

// List mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account']))) {

    $v_domain = $_GET['domain'];
    if(!in_array($v_domain, $user_domains)) {
        header("Location: /list/mail/");
        exit;
    }

    exec (HESTIA_CMD."v-list-mail-domain ".$user." ".escapeshellarg($v_domain)." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);

    // Parse domain
    $v_antispam = $data[$v_domain]['ANTISPAM'];
    $v_antivirus = $data[$v_domain]['ANTIVIRUS'];
    $v_dkim = $data[$v_domain]['DKIM'];
    $v_catchall = $data[$v_domain]['CATCHALL'];
    $v_date = $data[$v_domain]['DATE'];
    $v_time = $data[$v_domain]['TIME'];
    $v_suspended = $data[$v_domain]['SUSPENDED'];
    $v_webmail_alias = $data[$v_domain]['WEBMAIL_ALIAS'];
    
    if ( $v_suspended == 'yes' ) {
        $v_status =  'suspended';
    } else {
        $v_status =  'active';
    }
    
    $v_ssl = $data[$v_domain]['SSL'];
    if (!empty($v_ssl)) {
        exec (HESTIA_CMD."v-list-mail-domain-ssl ".$user." ".escapeshellarg($v_domain)." json", $output, $return_var);
        $ssl_str = json_decode(implode('', $output), true);
        unset($output);
        $v_ssl_crt = $ssl_str[$v_domain]['CRT'];
        $v_ssl_key = $ssl_str[$v_domain]['KEY'];
        $v_ssl_ca = $ssl_str[$v_domain]['CA'];
        $v_ssl_subject = $ssl_str[$v_domain]['SUBJECT'];
        $v_ssl_aliases = $ssl_str[$v_domain]['ALIASES'];
        $v_ssl_not_before = $ssl_str[$v_domain]['NOT_BEFORE'];
        $v_ssl_not_after = $ssl_str[$v_domain]['NOT_AFTER'];
        $v_ssl_signature = $ssl_str[$v_domain]['SIGNATURE'];
        $v_ssl_pub_key = $ssl_str[$v_domain]['PUB_KEY'];
        $v_ssl_issuer = $ssl_str[$v_domain]['ISSUER'];
    }
    $v_letsencrypt = $data[$v_domain]['LETSENCRYPT'];
    if (empty($v_letsencrypt)) $v_letsencrypt = 'no';
}

// List mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account']))) {

    $v_domain = $_GET['domain'];
    if(!in_array($v_domain, $user_domains)) {
        header("Location: /list/mail/");
        exit;
    }

    $v_account = $_GET['account'];
    exec (HESTIA_CMD."v-list-mail-account ".$user." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." 'json'", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);

    // Parse mail account
    $v_username = $user;
    $v_password = "";
    $v_aliases = str_replace(',', "\n", $data[$v_account]['ALIAS']);
    $valiases = explode(",", $data[$v_account]['ALIAS']);
    $v_fwd = str_replace(',', "\n", $data[$v_account]['FWD']);
    $vfwd = explode(",", $data[$v_account]['FWD']);
    $v_fwd_only = $data[$v_account]['FWD_ONLY'];
    $v_quota = $data[$v_account]['QUOTA'];
    $v_autoreply = $data[$v_account]['AUTOREPLY'];
    $v_suspended = $data[$v_account]['SUSPENDED'];
    $v_webmail_alias = $data[$v_account]['WEBMAIL_ALIAS'];
    if ( $v_suspended == 'yes' ) {
        $v_status =  'suspended';
    } else {
        $v_status =  'active';
    }
    $v_date = $data[$v_account]['DATE'];
    $v_time = $data[$v_account]['TIME'];

    // Parse autoreply
    if ( $v_autoreply == 'yes' ) {
        exec (HESTIA_CMD."v-list-mail-account-autoreply ".$user." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." json", $output, $return_var);
        $autoreply_str = json_decode(implode('', $output), true);
        unset($output);
        $v_autoreply_message = $autoreply_str[$v_account]['MSG'];
        $v_autoreply_message=str_replace("\\n", "\n", $v_autoreply_message);
    }
}


// Check POST request for mail domain
if ((!empty($_POST['save'])) && (!empty($_GET['domain'])) && (empty($_GET['account']))) {
    $v_domain = $_POST['v_domain'];
    if(!in_array($v_domain, $user_domains)) {
        check_return_code(3, ["Unknown domain"]);
    }

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Delete antispam
    if (($v_antispam == 'yes') && (empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-domain-antispam ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_antispam = 'no';
        unset($output);
    }

    // Add antispam
    if (($v_antispam == 'no') && (!empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-add-mail-domain-antispam ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_antispam = 'yes';
        unset($output);
    }

    // Delete antivirus
    if (($v_antivirus == 'yes') && (empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-domain-antivirus ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_antivirus = 'no';
        unset($output);
    }

    // Add antivirs
    if (($v_antivirus == 'no') && (!empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-add-mail-domain-antivirus ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_antivirus = 'yes';
        unset($output);
    }

    // Delete DKIM
    if (($v_dkim == 'yes') && (empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-domain-dkim ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_dkim = 'no';
        unset($output);
    }

    // Add DKIM
    if (($v_dkim == 'no') && (!empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-add-mail-domain-dkim ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_dkim = 'yes';
        unset($output);
    }

    // Delete catchall
    if ((!empty($v_catchall)) && (empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-domain-catchall ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        $v_catchall = '';
        unset($output);
    }

    // Change catchall address
    if ((!empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
        if ($v_catchall != $_POST['v_catchall']) {
            $v_catchall = escapeshellarg($_POST['v_catchall']);
            exec (HESTIA_CMD."v-change-mail-domain-catchall ".$v_username." ".escapeshellarg($v_domain)." ".$v_catchall, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
    }

    // Add catchall
    if ((empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
        $v_catchall = escapeshellarg($_POST['v_catchall']);
        exec (HESTIA_CMD."v-add-mail-domain-catchall ".$v_username." ".escapeshellarg($v_domain)." ".$v_catchall, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }
    
    // Change SSL certificate
    if (( $v_letsencrypt == 'no' ) && (empty($_POST['v_letsencrypt'])) && ( $v_ssl == 'yes' ) && (!empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        if (( $v_ssl_crt != str_replace("\r\n", "\n",  $_POST['v_ssl_crt'])) || ( $v_ssl_key != str_replace("\r\n", "\n",  $_POST['v_ssl_key'])) || ( $v_ssl_ca != str_replace("\r\n", "\n",  $_POST['v_ssl_ca']))) {
            exec ('mktemp -d', $mktemp_output, $return_var);
            $tmpdir = $mktemp_output[0];

            // Certificate
            if (!empty($_POST['v_ssl_crt'])) {
                $fp = fopen($tmpdir."/".$v_domain.".crt", 'w');
                fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_ssl_crt']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            // Key
            if (!empty($_POST['v_ssl_key'])) {
                $fp = fopen($tmpdir."/".$v_domain.".key", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            // CA
            if (!empty($_POST['v_ssl_ca'])) {
                $fp = fopen($tmpdir."/".$v_domain.".ca", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_ca']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            exec (HESTIA_CMD."v-change-mail-domain-sslcert ".$user." ".escapeshellarg($v_domain)." ".$tmpdir." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_web = 'yes';
            $restart_proxy = 'yes';

            exec (HESTIA_CMD."v-list-mail-domain-ssl ".$user." ".escapeshellarg($v_domain)." json", $output, $return_var);
            $ssl_str = json_decode(implode('', $output), true);
            unset($output);
            $v_ssl_crt = $ssl_str[$v_domain]['CRT'];
            $v_ssl_key = $ssl_str[$v_domain]['KEY'];
            $v_ssl_ca = $ssl_str[$v_domain]['CA'];
            $v_ssl_subject = $ssl_str[$v_domain]['SUBJECT'];
            $v_ssl_aliases = $ssl_str[$v_domain]['ALIASES'];
            $v_ssl_not_before = $ssl_str[$v_domain]['NOT_BEFORE'];
            $v_ssl_not_after = $ssl_str[$v_domain]['NOT_AFTER'];
            $v_ssl_signature = $ssl_str[$v_domain]['SIGNATURE'];
            $v_ssl_pub_key = $ssl_str[$v_domain]['PUB_KEY'];
            $v_ssl_issuer = $ssl_str[$v_domain]['ISSUER'];

            // Cleanup certificate tempfiles
            if (!empty($_POST['v_ssl_crt'])) unlink($tmpdir."/".$v_domain.".crt");
            if (!empty($_POST['v_ssl_key'])) unlink($tmpdir."/".$v_domain.".key");
            if (!empty($_POST['v_ssl_ca']))  unlink($tmpdir."/".$v_domain.".ca");
            rmdir($tmpdir);
        }
    }

    // Delete Lets Encrypt support
    if (( $v_letsencrypt == 'yes' ) && (empty($_POST['v_letsencrypt']) || empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-letsencrypt-domain ".$user." ".escapeshellarg($v_domain)." ' ' 'yes'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_ssl_crt = '';
        $v_ssl_key = '';
        $v_ssl_ca = '';
        $v_letsencrypt = 'no';
        $v_letsencrypt_deleted = 'yes';
        $v_ssl = 'no';
        $restart_mail = 'yes';
    }

    // Delete SSL certificate
    if (( $v_ssl == 'yes' ) && (empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-domain-ssl ".$v_username." ".escapeshellarg($v_domain), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_ssl_crt = '';
        $v_ssl_key = '';
        $v_ssl_ca = '';
        $v_ssl = 'no';
        $restart_mail = 'yes';
    }

    // Add Lets Encrypt support
    if ((!empty($_POST['v_ssl'])) && ( $v_letsencrypt == 'no' ) && (!empty($_POST['v_letsencrypt'])) && empty($_SESSION['error_msg'])) {
        exec (HESTIA_CMD."v-add-letsencrypt-domain ".$user." ".escapeshellarg($v_domain)." ' ' 'yes'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_letsencrypt = 'yes';
        $v_ssl = 'yes';
        $restart_mail = 'yes';
     }

     // Add SSL certificate
     if (( $v_ssl == 'no' ) && (!empty($_POST['v_ssl']))  && (empty($v_letsencrypt_deleted)) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_ssl_crt'])) $errors[] = 'ssl certificate';
        if (empty($_POST['v_ssl_key'])) $errors[] = 'ssl key';
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = _('Field "%s" can not be blank.',$error_msg);
        } else {
            exec ('mktemp -d', $mktemp_output, $return_var);
            $tmpdir = $mktemp_output[0];

            // Certificate
            if (!empty($_POST['v_ssl_crt'])) {
                $fp = fopen($tmpdir."/".$v_domain.".crt", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_crt']));
                fclose($fp);
            }

            // Key
            if (!empty($_POST['v_ssl_key'])) {
                $fp = fopen($tmpdir."/".$v_domain.".key", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
                fclose($fp);
            }

            // CA
            if (!empty($_POST['v_ssl_ca'])) {
                $fp = fopen($tmpdir."/".$v_domain.".ca", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_ca']));
                fclose($fp);
            }
            exec (HESTIA_CMD."v-add-mail-domain-ssl ".$user." ".escapeshellarg($v_domain)." ".$tmpdir." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_ssl = 'yes';
            $restart_web = 'yes';
            $restart_proxy = 'yes';

            exec (HESTIA_CMD."v-list-mail-domain-ssl ".$user." ".escapeshellarg($v_domain)." json", $output, $return_var);
            $ssl_str = json_decode(implode('', $output), true);
            unset($output);
            $v_ssl_crt = $ssl_str[$v_domain]['CRT'];
            $v_ssl_key = $ssl_str[$v_domain]['KEY'];
            $v_ssl_ca = $ssl_str[$v_domain]['CA'];
            $v_ssl_subject = $ssl_str[$v_domain]['SUBJECT'];
            $v_ssl_aliases = $ssl_str[$v_domain]['ALIASES'];
            $v_ssl_not_before = $ssl_str[$v_domain]['NOT_BEFORE'];
            $v_ssl_not_after = $ssl_str[$v_domain]['NOT_AFTER'];
            $v_ssl_signature = $ssl_str[$v_domain]['SIGNATURE'];
            $v_ssl_pub_key = $ssl_str[$v_domain]['PUB_KEY'];
            $v_ssl_issuer = $ssl_str[$v_domain]['ISSUER'];

            // Cleanup certificate tempfiles
            if (!empty($_POST['v_ssl_crt'])) unlink($tmpdir."/".$v_domain.".crt");
            if (!empty($_POST['v_ssl_key'])) unlink($tmpdir."/".$v_domain.".key");
            if (!empty($_POST['v_ssl_ca'])) unlink($tmpdir."/".$v_domain.".ca");
            rmdir($tmpdir);
        }
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = _('Changes has been saved.');
    }
}

// Check POST request for mail account
if ((!empty($_POST['save'])) && (!empty($_GET['domain'])) && (!empty($_GET['account']))) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Validate email
    if ((!empty($_POST['v_send_email'])) && (empty($_SESSION['error_msg']))) {
        if (!filter_var($_POST['v_send_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = _('Please enter valid email address.');
        }
    }

    $v_domain = $_POST['v_domain'];
    if(!in_array($v_domain, $user_domains)) {
        check_return_code(3, ["Unknown domain"]);
    }

    $v_account = $_POST['v_account'];
    $v_send_email = $_POST['v_send_email'];
    $v_credentials = $_POST['v_credentials'];

    // Change password
    if ((!empty($_POST['v_password'])) && (empty($_SESSION['error_msg']))) {
        if (!validate_password($_POST['v_password'])) { 
            $_SESSION['error_msg'] = _('Password does not match the minimum requirements');
        }else{         
            $v_password = tempnam("/tmp","vst");
            $fp = fopen($v_password, "w");
            fwrite($fp, $_POST['v_password']."\n");
            fclose($fp);
            exec (HESTIA_CMD."v-change-mail-account-password ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".$v_password, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            unlink($v_password);
            $v_password = escapeshellarg($_POST['v_password']);
        }
    }

    // Change quota
    if (($v_quota != $_POST['v_quota']) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_quota'])) {
            $v_quota = 0;
        } else {
            $v_quota = escapeshellarg($_POST['v_quota']);
        }
        exec (HESTIA_CMD."v-change-mail-account-quota ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".$v_quota, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change account aliases
    if (empty($_SESSION['error_msg'])) {
        $waliases = preg_replace("/\n/", " ", $_POST['v_aliases']);
        $waliases = preg_replace("/,/", " ", $waliases);
        $waliases = preg_replace('/\s+/', ' ',$waliases);
        $waliases = trim($waliases);
        $aliases = explode(" ", $waliases);
        $v_aliases = str_replace(' ', "\n", $waliases);
        $result = array_diff($valiases, $aliases);
        foreach ($result as $alias) {
            if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                exec (HESTIA_CMD."v-delete-mail-account-alias ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".escapeshellarg($alias), $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
        $result = array_diff($aliases, $valiases);
        foreach ($result as $alias) {
            if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                exec (HESTIA_CMD."v-add-mail-account-alias ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".escapeshellarg($alias), $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    // Change forwarders
    if (empty($_SESSION['error_msg'])) {
        $wfwd = preg_replace("/\n/", " ", $_POST['v_fwd']);
        $wfwd = preg_replace("/,/", " ", $wfwd);
        $wfwd = preg_replace('/\s+/', ' ',$wfwd);
        $wfwd = trim($wfwd);
        $fwd = explode(" ", $wfwd);
        $v_fwd = str_replace(' ', "\n", $wfwd);
        $result = array_diff($vfwd, $fwd);
        foreach ($result as $forward) {
            if ((empty($_SESSION['error_msg'])) && (!empty($forward))) {
                exec (HESTIA_CMD."v-delete-mail-account-forward ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".escapeshellarg($forward), $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
        $result = array_diff($fwd, $vfwd);
        foreach ($result as $forward) {
            if ((empty($_SESSION['error_msg'])) && (!empty($forward))) {
                exec (HESTIA_CMD."v-add-mail-account-forward ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".escapeshellarg($forward), $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    // Delete FWD_ONLY flag
    if (($v_fwd_only == 'yes') && (empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-account-fwd-only ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_fwd_only = '';
    }

    // Add FWD_ONLY flag
    if (($v_fwd_only != 'yes') && (!empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-add-mail-account-fwd-only ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_fwd_only = 'yes';
    }

    // Delete autoreply
    if (($v_autoreply == 'yes') && (empty($_POST['v_autoreply'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-mail-account-autoreply ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_autoreply = 'no';
        $v_autoreply_message = '';
    }

    // Add autoreply
    if ((!empty($_POST['v_autoreply'])) && (empty($_SESSION['error_msg']))) {
        if ( $v_autoreply_message != str_replace("\r\n", "\n", $_POST['v_autoreply_message'])) {
            $v_autoreply_message = str_replace("\r\n", "\n", $_POST['v_autoreply_message']);
            $v_autoreply_message = escapeshellarg($v_autoreply_message);
            exec (HESTIA_CMD."v-add-mail-account-autoreply ".$v_username." ".escapeshellarg($v_domain)." ".escapeshellarg($v_account)." ".$v_autoreply_message, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_autoreply = 'yes';
            $v_autoreply_message = $_POST['v_autoreply_message'];
        }
    }

    // Email login credentials
    if ((!empty($v_send_email)) && (empty($_SESSION['error_msg']))) {
        $to = $v_send_email;
        $subject = _("Email Credentials");
        $hostname = exec('hostname');
        $from = sprintf(_('MAIL_FROM'), $hostname);
        $mailtext = $v_credentials;
        send_email($to, $subject, $mailtext, $from);
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = _('Changes has been saved.');
    }
}


// Render page
if (empty($_GET['account']))  {
    // Display body for mail domain
    render_page($user, $TAB, 'edit_mail');
} else {
    // Display body for mail account
    render_page($user, $TAB, 'edit_mail_acc');
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
