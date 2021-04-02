<?php
#error_reporting(NULL);
ob_start();
$TAB = 'MAIL';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Get all user domains 
exec (HESTIA_CMD."v-list-mail-domains ".escapeshellarg($user)." json", $output, $return_var);
$user_domains = json_decode(implode('', $output), true);
$user_domains = array_keys($user_domains);
unset($output);

exec (HESTIA_CMD."v-list-sys-webmail json", $output, $return_var);
$webmail_clients = json_decode(implode('', $output), true);
unset($output);

$v_domain = $_GET['domain'];
if(!empty($v_domain)){
    if(!in_array($v_domain, $user_domains)) {
        header("Location: /list/mail/");
        exit;
    }
    // Set webmail alias
    exec (HESTIA_CMD."v-list-mail-domain ".escapeshellarg($user)." ".escapeshellarg($v_domain)." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);
    $v_webmail_alias = $data[$v_domain]['WEBMAIL_ALIAS'];
}

// Check POST request for mail domain
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Check empty fields
    if (empty($_POST['v_domain'])) $errors[] = _('domain');
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

    // Check antispam option
    if (!empty($_POST['v_antispam'])) {
        $v_antispam = 'yes';
    } else {
        $v_antispam = 'no';
    }

    // Check antivirus option
    if (!empty($_POST['v_antivirus'])) {
        $v_antivirus = 'yes';
    } else {
        $v_antivirus = 'no';
    }

    // Check dkim option
    if (!empty($_POST['v_dkim'])) {
        $v_dkim = 'yes';
    } else {
        $v_dkim = 'no';
    }

    // Set domain name to lowercase and remove www prefix
    $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
    $v_domain = escapeshellarg($v_domain);
    $v_domain = strtolower($v_domain);

    // Add mail domain
    if (empty($_SESSION['error_msg'])) {
        exec (HESTIA_CMD."v-add-mail-domain ".$user." ".$v_domain." ".$v_antispam." ".$v_antivirus." ".$v_dkim, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }
    
    if (!empty($_SESSION['IMAP_SYSTEM']) && !empty($_SESSION['WEBMAIL_SYSTEM'])){
        if (empty($_SESSION['error_msg'])) {
        if (!empty($_POST['v_webmail'])) {
            $v_webmail = escapeshellarg($_POST['v_webmail']);
            exec (HESTIA_CMD."v-add-mail-domain-webmail ".$user." ".$v_domain." ".$v_webmail." yes", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
        }
    }
    
    if (empty($_POST['v_webmail'])) {
        if (empty($_SESSION['error_msg'])) {
        exec (HESTIA_CMD."v-delete-mail-domain-webmail ".$user." ".$v_domain." yes", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        }
    }
    
    // Add SMTP Relay Support
    if (empty($_SESSION['error_msg'])) {
        if (isset($_POST['v_smtp_relay']) && (!empty($_POST['v_smtp_relay_host'])) && (!empty($_POST['v_smtp_relay_user']))) {
            if (($_POST['v_smtp_relay_host'] != $v_smtp_relay_host) ||
                ($_POST['v_smtp_relay_user'] != $v_smtp_relay_user) ||
                ($_POST['v_smtp_relay_port'] != $v_smtp_relay_port)) {
                if (!empty($_POST['v_smtp_relay_pass'])) {
                    $v_smtp_relay = true;	
                    $v_smtp_relay_host = escapeshellarg($_POST['v_smtp_relay_host']);
                    $v_smtp_relay_user = escapeshellarg($_POST['v_smtp_relay_user']);
                    $v_smtp_relay_pass = escapeshellarg($_POST['v_smtp_relay_pass']);
                    if (!empty($_POST['v_smtp_relay_port'])) {
                        $v_smtp_relay_port = escapeshellarg($_POST['v_smtp_relay_port']);
                    } else {
                        $v_smtp_relay_port = '587';
                    }
                    exec (HESTIA_CMD."v-add-mail-domain-smtp-relay ".$user." ".$v_domain." ".$v_smtp_relay_host." ".$v_smtp_relay_user." ".$v_smtp_relay_pass." ".$v_smtp_relay_port, $output, $return_var);
                    check_return_code($return_var,$output);
                    unset($output);
                } else {
                    $_SESSION['error_msg'] = _('SMTP Relay Password is required');
                }
            }
        }
    }
    
    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = sprintf(_('MAIL_DOMAIN_CREATED_OK'),htmlentities($_POST['v_domain']),htmlentities($_POST['v_domain']));
        unset($v_domain, $v_webmail);
    }
}


// Check POST request for mail account
if (!empty($_POST['ok_acc'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }
    
    
    // Check antispam option
    if (!empty($_POST['v_blackhole'])) {
        $v_blackhole = 'yes';
    } else {
        $v_blackhole = 'no';
    }
    // Check empty fields
    if (empty($_POST['v_domain'])) $errors[] = _('domain');
    if (empty($_POST['v_account'])) $errors[] = _('account');
    if ((empty($_POST['v_fwd_only']) && empty($_POST['v_password']))) {
        if (empty($_POST['v_password'])) $errors[] = _('password');
    }
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

    // Validate email
    if ((!empty($_POST['v_send_email'])) && (empty($_SESSION['error_msg']))) {
        if (!filter_var($_POST['v_send_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = _('Please enter valid email address.');
        }
    }
    
    // Check password length
    if (empty($_SESSION['error_msg']) && (empty($_POST['v_fwd_only']))) {
        if (!validate_password($_POST['v_password'])) { $_SESSION['error_msg'] = _('Password does not match the minimum requirements');}
    }

    // Protect input
    $v_domain = escapeshellarg($_POST['v_domain']);
    $v_domain = strtolower($v_domain);
    $v_account = escapeshellarg($_POST['v_account']);
    $v_quota = escapeshellarg($_POST['v_quota']);
    $v_send_email = $_POST['v_send_email'];
    $v_credentials = $_POST['v_credentials'];
    $v_aliases = $_POST['v_aliases'];
    $v_fwd = $_POST['v_fwd'];
    if (empty($_POST['v_quota'])) $v_quota = 0;
    if ((!empty($_POST['v_quota'])) || (!empty($_POST['v_aliases'])) || (!empty($_POST['v_fwd'])) ) $v_adv = 'yes';

    // Add Mail Account
    if (empty($_SESSION['error_msg'])) {
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        exec (HESTIA_CMD."v-add-mail-account ".$user." ".$v_domain." ".$v_account." ".$v_password." ".$v_quota, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($v_password);
        $v_password = escapeshellarg($_POST['v_password']);
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
                exec (HESTIA_CMD."v-add-mail-account-alias ".$user." ".$v_domain." ".$v_account." ".$alias, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    if ((!empty($_POST['v_blackhole'])) && (empty($_SESSION['error_msg']))){
        exec (HESTIA_CMD."v-add-mail-account-forward ".$user." ".$v_domain." ".$v_account." :blackhole:", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        //disable  any input in v_fwd
        $_POST['v_fwd'] = '';
    }
    // Add Forwarders
    if ((!empty($_POST['v_fwd'])) && (empty($_SESSION['error_msg']))) {
        $vfwd = preg_replace("/\n/", " ", $_POST['v_fwd']);
        $vfwd = preg_replace("/,/", " ", $vfwd);
        $vfwd = preg_replace('/\s+/', ' ',$vfwd);
        $vfwd = trim($vfwd);
        $fwd = explode(" ", $vfwd);
        foreach ($fwd as $forward) {
            $forward = escapeshellarg($forward);
            if (empty($_SESSION['error_msg'])) {
                exec (HESTIA_CMD."v-add-mail-account-forward ".$user." ".$v_domain." ".$v_account." ".$forward, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    // Add fwd_only flag
    if ((!empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-add-mail-account-fwd-only ".$user." ".$v_domain." ".$v_account, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Get webmail url
    if (empty($_SESSION['error_msg'])) {
        list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"].":");
        $webmail = "http://".$hostname."/".$v_webmail_alias."/";
        if (!empty($_SESSION['WEBMAIL_ALIAS'])) $webmail = $_SESSION['WEBMAIL_ALIAS'];
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

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = sprintf(_('MAIL_ACCOUNT_CREATED_OK'),htmlentities(strtolower($_POST['v_account'])),htmlentities($_POST[v_domain]),htmlentities(strtolower($_POST['v_account'])),htmlentities($_POST[v_domain]));
        unset($v_account);
        unset($v_password);
        unset($v_password);
        unset($v_aliases);
        unset($v_fwd);
        unset($v_quota);
    }
}

// Render page
if (empty($_GET['domain'])) {
    // Display body for mail domain
    if( !empty($_POST['v_webmail']) ){
        $v_webmail  = $_POST['v_webmail'];
    }else{
        //default is always roundcube unless it hasn't been installed. Then picks the first one in order
        $v_webmail  = 'roundcube';
    }
    render_page($user, $TAB, 'add_mail');
} else {
    // Display body for mail account

    $v_domain = $_GET['domain'];
    render_page($user, $TAB, 'add_mail_acc');
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
