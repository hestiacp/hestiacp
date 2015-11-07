<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
unset($_SESSION['error_msg']);
$TAB = 'WEB';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check domain argument
if (empty($_GET['domain'])) {
    header("Location: /list/web/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// List domain
$v_domain = escapeshellarg($_GET['domain']);
exec (VESTA_CMD."v-list-web-domain ".$user." ".$v_domain." json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse domain
$v_username = $user;
$v_domain = $_GET['domain'];
$v_ip = $data[$v_domain]['IP'];
$v_template = $data[$v_domain]['TPL'];
$v_aliases = str_replace(',', "\n", $data[$v_domain]['ALIAS']);
$valiases = explode(",", $data[$v_domain]['ALIAS']);
$v_tpl = $data[$v_domain]['IP'];
$v_cgi = $data[$v_domain]['CGI'];
$v_elog = $data[$v_domain]['ELOG'];
$v_ssl = $data[$v_domain]['SSL'];
if ( $v_ssl == 'yes' ) {
    exec (VESTA_CMD."v-list-web-domain-ssl ".$user." '".$v_domain."' json", $output, $return_var);
    $ssl_str = json_decode(implode('', $output), true);
    unset($output);
    $v_ssl_crt = $ssl_str[$v_domain]['CRT'];
    $v_ssl_key = $ssl_str[$v_domain]['KEY'];
    $v_ssl_ca = $ssl_str[$v_domain]['CA'];
}
$v_ssl_home = $data[$v_domain]['SSL_HOME'];
$v_backend_template = $data[$v_domain]['BACKEND'];
$v_proxy = $data[$v_domain]['PROXY'];
$v_proxy_template = $data[$v_domain]['PROXY'];
$v_proxy_ext = str_replace(',', ', ', $data[$v_domain]['PROXY_EXT']);
$v_stats = $data[$v_domain]['STATS'];
$v_stats_user = $data[$v_domain]['STATS_USER'];
if (!empty($v_stats_user)) $v_stats_password = "";
$v_ftp_user = $data[$v_domain]['FTP_USER'];
$v_ftp_path = $data[$v_domain]['FTP_PATH'];
if (!empty($v_ftp_user)) $v_ftp_password = "";
$v_ftp_user_prepath = $data[$v_domain]['DOCUMENT_ROOT'];
$v_ftp_user_prepath = str_replace('/public_html', '', $v_ftp_user_prepath, $occurance = 1);
$v_ftp_email = $panel[$user]['CONTACT'];
$v_suspended = $data[$v_domain]['SUSPENDED'];
if ( $v_suspended == 'yes' ) {
    $v_status =  'suspended';
} else {
    $v_status =  'active';
}
$v_time = $data[$v_domain]['TIME'];
$v_date = $data[$v_domain]['DATE'];

// List ip addresses
exec (VESTA_CMD."v-list-user-ips ".$user." json", $output, $return_var);
$ips = json_decode(implode('', $output), true);
unset($output);

// List web templates
exec (VESTA_CMD."v-list-web-templates json", $output, $return_var);
$templates = json_decode(implode('', $output), true);
unset($output);

// List backend templates
if (!empty($_SESSION['WEB_BACKEND'])) {
    exec (VESTA_CMD."v-list-web-templates-backend json", $output, $return_var);
    $backend_templates = json_decode(implode('', $output), true);
    unset($output);
}

// List proxy templates
if (!empty($_SESSION['PROXY_SYSTEM'])) {
    exec (VESTA_CMD."v-list-web-templates-proxy json", $output, $return_var);
    $proxy_templates = json_decode(implode('', $output), true);
    unset($output);
}

// List web stat engines
exec (VESTA_CMD."v-list-web-stats json", $output, $return_var);
$stats = json_decode(implode('', $output), true);
unset($output);

// Check POST request
if (!empty($_POST['save'])) {
    $v_domain = escapeshellarg($_POST['v_domain']);

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Change web domain IP
    if (($v_ip != $_POST['v_ip']) && (empty($_SESSION['error_msg']))) {
        $v_ip = escapeshellarg($_POST['v_ip']);
        exec (VESTA_CMD."v-change-web-domain-ip ".$v_username." ".$v_domain." ".$v_ip." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        $restart_web = 'yes';
        $restart_proxy = 'yes';
        unset($output);
    }

    // Chane dns domain IP
    if (($v_ip != $_POST['v_ip']) && (empty($_SESSION['error_msg'])))  {
        exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain." json", $output, $return_var);
        unset($output);
        if ($return_var == 0 ) {
            exec (VESTA_CMD."v-change-dns-domain-ip ".$v_username." ".$v_domain." ".$v_ip." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_dns = 'yes';
        }
    }

    // Change dns ip for each alias
    if (($v_ip != $_POST['v_ip']) && (empty($_SESSION['error_msg']))) {
        foreach($valiases as $v_alias ){
            exec (VESTA_CMD."v-list-dns-domain ".$v_username." '".$v_alias."' json", $output, $return_var);
            unset($output);
            if ($return_var == 0 ) {
                exec (VESTA_CMD."v-change-dns-domain-ip ".$v_username." '".$v_alias."' ".$v_ip, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                $restart_dns = 'yes';
            }
        }
    }

    // Change template (admin only)
    if (($v_template != $_POST['v_template']) && ( $_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_template = escapeshellarg($_POST['v_template']);
        exec (VESTA_CMD."v-change-web-domain-tpl ".$v_username." ".$v_domain." ".$v_template." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $restart_web = 'yes';
    }

    // Change aliases
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
                $restart_web = 'yes';
                $restart_proxy = 'yes';
                $v_template = escapeshellarg($_POST['v_template']);
                exec (VESTA_CMD."v-delete-web-domain-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);

                if (empty($_SESSION['error_msg'])) {
                    exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain, $output, $return_var);
                    unset($output);
                    if ($return_var == 0) {
                        exec (VESTA_CMD."v-delete-dns-on-web-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                        check_return_code($return_var,$output);
                        unset($output);
                        $restart_dns = 'yes';
                    }
                }
            }
        }

        $result = array_diff($aliases, $valiases);
        foreach ($result as $alias) {
            if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                $restart_web = 'yes';
                $restart_proxy = 'yes';
                $v_template = escapeshellarg($_POST['v_template']);
                exec (VESTA_CMD."v-add-web-domain-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                if (empty($_SESSION['error_msg'])) {
                    exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain, $output, $return_var);
                    unset($output);
                    if ($return_var == 0) {
                        exec (VESTA_CMD."v-add-dns-on-web-alias ".$v_username." ".$alias." ".$v_ip." no", $output, $return_var);
                        check_return_code($return_var,$output);
                    unset($output);
                        $restart_dns = 'yes';
                    }
                }
            }
        }
    }

    // Change backend template
    if ((!empty($_SESSION['WEB_BACKEND'])) && ( $v_backend_template != $_POST['v_backend_template']) && (empty($_SESSION['error_msg']))) {
            $v_backend_template = $_POST['v_backend_template'];
            exec (VESTA_CMD."v-change-web-domain-backend-tpl ".$v_username." ".$v_domain." ".escapeshellarg($v_backend_template), $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
    }

    // Delete proxy support
    if ((!empty($_SESSION['PROXY_SYSTEM'])) && (!empty($v_proxy)) && (empty($_POST['v_proxy'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-proxy ".$v_username." ".$v_domain." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unset($v_proxy);
        $restart_proxy = 'yes';
    }

    // Change proxy template / Update extention list
    if ((!empty($_SESSION['PROXY_SYSTEM'])) && (!empty($v_proxy)) && (!empty($_POST['v_proxy'])) && (empty($_SESSION['error_msg']))) {
        $ext = preg_replace("/\n/", " ", $_POST['v_proxy_ext']);
        $ext = preg_replace("/,/", " ", $ext);
        $ext = preg_replace('/\s+/', ' ',$ext);
        $ext = trim($ext);
        $ext = str_replace(' ', ", ", $ext);
        if (( $v_proxy_template != $_POST['v_proxy_template']) || ($v_proxy_ext != $ext)) {
            $ext = str_replace(', ', ",", $ext);
            if (!empty($_POST['v_proxy_template'])) $v_proxy_template = $_POST['v_proxy_template'];
            exec (VESTA_CMD."v-change-web-domain-proxy-tpl ".$v_username." ".$v_domain." ".escapeshellarg($v_proxy_template)." ".escapeshellarg($ext)." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            $v_proxy_ext = str_replace(',', ', ', $ext);
            unset($output);
            $restart_proxy = 'yes';
        }
    }

    // Add proxy support
    if ((!empty($_SESSION['PROXY_SYSTEM'])) && (empty($v_proxy)) && (!empty($_POST['v_proxy'])) && (empty($_SESSION['error_msg']))) {
        $v_proxy_template = $_POST['v_proxy_template'];
        if (!empty($_POST['v_proxy_ext'])) {
            $ext = preg_replace("/\n/", " ", $_POST['v_proxy_ext']);
            $ext = preg_replace("/,/", " ", $ext);
            $ext = preg_replace('/\s+/', ' ',$ext);
            $ext = trim($ext);
            $ext = str_replace(' ', ",", $ext);
            $v_proxy_ext = str_replace(',', ', ', $ext);
        }
        exec (VESTA_CMD."v-add-web-domain-proxy ".$v_username." ".$v_domain." ".escapeshellarg($v_proxy_template)." ".escapeshellarg($ext)." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $restart_proxy = 'yes';
    }

    // Delete SSL certificate
    if (( $v_ssl == 'yes' ) && (empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-ssl ".$v_username." ".$v_domain." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_ssl = 'no';
        $restart_web = 'yes';
        $restart_proxy = 'yes';
    }

    // Change SSL certificate
    if (($v_ssl == 'yes') && (!empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        if (( $v_ssl_crt != str_replace("\r\n", "\n",  $_POST['v_ssl_crt'])) || ( $v_ssl_key != str_replace("\r\n", "\n",  $_POST['v_ssl_key'])) || ( $v_ssl_ca != str_replace("\r\n", "\n",  $_POST['v_ssl_ca']))) {
            exec ('mktemp -d', $mktemp_output, $return_var);
            $tmpdir = $mktemp_output[0];

            // Certificate
            if (!empty($_POST['v_ssl_crt'])) {
                $fp = fopen($tmpdir."/".$_POST['v_domain'].".crt", 'w');
                fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_ssl_crt']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            // Key
            if (!empty($_POST['v_ssl_key'])) {
                $fp = fopen($tmpdir."/".$_POST['v_domain'].".key", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            // CA
            if (!empty($_POST['v_ssl_ca'])) {
                $fp = fopen($tmpdir."/".$_POST['v_domain'].".ca", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_ca']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            exec (VESTA_CMD."v-change-web-domain-sslcert ".$user." ".$v_domain." ".$tmpdir." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_web = 'yes';
            $restart_proxy = 'yes';
            $v_ssl_crt = $_POST['v_ssl_crt'];
            $v_ssl_key = $_POST['v_ssl_key'];
            $v_ssl_ca = $_POST['v_ssl_ca'];

            // Cleanup certificate tempfiles
            if (!empty($_POST['v_ssl_crt'])) {
                unlink($tmpdir."/".$_POST['v_domain'].".crt");
            }

            if (!empty($_POST['v_ssl_key'])) {
                unlink($tmpdir."/".$_POST['v_domain'].".key");
            }

            if (!empty($_POST['v_ssl_ca'])) {
                unlink($tmpdir."/".$_POST['v_domain'].".ca");
            }

            rmdir($tmpdir);
        }
    }

    // Add SSL certificate
    if (( $v_ssl == 'no') && (!empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_crt']))) $errors[] = 'ssl certificate';
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_key']))) $errors[] = 'ssl key';
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_home']))) $errors[] = 'ssl home';
        $v_ssl_home = escapeshellarg($_POST['v_ssl_home']);
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
        } else {
            exec ('mktemp -d', $mktemp_output, $return_var);
            $tmpdir = $mktemp_output[0];

            // Certificate
            if (!empty($_POST['v_ssl_crt'])) {
                $fp = fopen($tmpdir."/".$_POST['v_domain'].".crt", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_crt']));
                fclose($fp);
            }

            // Key
            if (!empty($_POST['v_ssl_key'])) {
                $fp = fopen($tmpdir."/".$_POST['v_domain'].".key", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
                fclose($fp);
            }

            // CA
            if (!empty($_POST['v_ssl_ca'])) {
                $fp = fopen($tmpdir."/".$_POST['v_domain'].".ca", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_ca']));
                fclose($fp);
            }
            exec (VESTA_CMD."v-add-web-domain-ssl ".$user." ".$v_domain." ".$tmpdir." ".$v_ssl_home." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_ssl = 'yes';
            $restart_web = 'yes';
            $restart_proxy = 'yes';
            $v_ssl_crt = $_POST['v_ssl_crt'];
            $v_ssl_key = $_POST['v_ssl_key'];
            $v_ssl_ca = $_POST['v_ssl_ca'];
            $v_ssl_home = $_POST['v_ssl_home'];

            // Cleanup certificate tempfiles
            if (!empty($_POST['v_ssl_crt'])) {
                unlink($tmpdir."/".$_POST['v_domain'].".crt");
            }

            if (!empty($_POST['v_ssl_key'])) {
                unlink($tmpdir."/".$_POST['v_domain'].".key");
            }

            if (!empty($_POST['v_ssl_ca'])) {
                unlink($tmpdir."/".$_POST['v_domain'].".ca");
            }

            rmdir($tmpdir);
        }
    }

    // Change document root for ssl domain
    if (( $v_ssl == 'yes') && (!empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        if ( $v_ssl_home != $_POST['v_ssl_home'] ) {
            $v_ssl_home = escapeshellarg($_POST['v_ssl_home']);
            exec (VESTA_CMD."v-change-web-domain-sslhome ".$user." ".$v_domain." ".$v_ssl_home." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            $v_ssl_home = $_POST['v_ssl_home'];
            unset($output);
        }
    }

    // Delete web stats
    if ((!empty($v_stats)) && ($_POST['v_stats'] == 'none') && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-stats ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_stats = '';
    }

    // Change web stats engine
    if ((!empty($v_stats)) && ($_POST['v_stats'] != $v_stats) && (empty($_SESSION['error_msg']))) {
        $v_stats = escapeshellarg($_POST['v_stats']);
        exec (VESTA_CMD."v-change-web-domain-stats ".$v_username." ".$v_domain." ".$v_stats, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Add web stats
    if ((empty($v_stats)) && ($_POST['v_stats'] != 'none') && (empty($_SESSION['error_msg']))) {
        $v_stats = escapeshellarg($_POST['v_stats']);
        exec (VESTA_CMD."v-add-web-domain-stats ".$v_username." ".$v_domain." ".$v_stats, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Delete web stats authorization
    if ((!empty($v_stats_user)) && (empty($_POST['v_stats_auth'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-stats-user ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_stats_user = '';
        $v_stats_password = '';
    }

    // Change web stats user or password
    if ((empty($v_stats_user)) && (!empty($_POST['v_stats_auth'])) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_stats_user'])) $errors[] = __('stats username');
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
        } else {
            $v_stats_user = escapeshellarg($_POST['v_stats_user']);
            $v_stats_password = tempnam("/tmp","vst");
            $fp = fopen($v_stats_password, "w");
            fwrite($fp, $_POST['v_stats_password']."\n");
            fclose($fp);
            exec (VESTA_CMD."v-add-web-domain-stats-user ".$v_username." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            unlink($v_stats_password);
            $v_stats_password = escapeshellarg($_POST['v_stats_password']);
        }
    }

    // Add web stats authorization
    if ((!empty($v_stats_user)) && (!empty($_POST['v_stats_auth'])) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_stats_user'])) $errors[] = __('stats user');
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
        }
        if (($v_stats_user != $_POST['v_stats_user']) || (!empty($_POST['v_stats_password'])) && (empty($_SESSION['error_msg']))) {
            $v_stats_user = escapeshellarg($_POST['v_stats_user']);
            $v_stats_password = tempnam("/tmp","vst");
            $fp = fopen($v_stats_password, "w");
            fwrite($fp, $_POST['v_stats_password']."\n");
            fclose($fp);
            exec (VESTA_CMD."v-add-web-domain-stats-user ".$v_username." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            unlink($v_stats_password);
            $v_stats_password = escapeshellarg($_POST['v_stats_password']);
        }
    }

    // Update ftp account
    if (!empty($_POST['v_ftp_user'])) {
        $v_ftp_users_updated = array();
        foreach ($_POST['v_ftp_user'] as $i => $v_ftp_user_data) {
            if (empty($v_ftp_user_data['v_ftp_user'])) {
                continue;
            }

            $v_ftp_user_data['v_ftp_user'] = preg_replace("/^".$user."_/i", "", $v_ftp_user_data['v_ftp_user']);
            if ($v_ftp_user_data['is_new'] == 1 && !empty($_POST['v_ftp'])) {
                if ((!empty($v_ftp_user_data['v_ftp_email'])) && (!filter_var($v_ftp_user_data['v_ftp_email'], FILTER_VALIDATE_EMAIL))) $_SESSION['error_msg'] = __('Please enter valid email address.');
                if (empty($v_ftp_user_data['v_ftp_user'])) $errors[] = 'ftp user';
                if (!empty($errors[0])) {
                    foreach ($errors as $i => $error) {
                        if ( $i == 0 ) {
                            $error_msg = $error;
                        } else {
                            $error_msg = $error_msg.", ".$error;
                        }
                    }
                    $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
                }

                // Add ftp account
                $v_ftp_username      = $v_ftp_user_data['v_ftp_user'];
                $v_ftp_username_full = $user . '_' . $v_ftp_user_data['v_ftp_user'];
                $v_ftp_user = escapeshellarg($v_ftp_username);
                $v_ftp_path = escapeshellarg(trim($v_ftp_user_data['v_ftp_path']));
                if (empty($_SESSION['error_msg'])) {
                    $v_ftp_password = tempnam("/tmp","vst");
                    $fp = fopen($v_ftp_password, "w");
                    fwrite($fp, $v_ftp_user_data['v_ftp_password']."\n");
                    fclose($fp);
                    exec (VESTA_CMD."v-add-web-domain-ftp ".$v_username." ".$v_domain." ".$v_ftp_username." ".$v_ftp_password . " " . $v_ftp_path, $output, $return_var);
                    check_return_code($return_var,$output);
                    if ((!empty($v_ftp_user_data['v_ftp_email'])) && (empty($_SESSION['error_msg']))) {
                        $to = $v_ftp_user_data['v_ftp_email'];
                        $subject = __("FTP login credentials");
                        $hostname = exec('hostname');
                        $from = __('MAIL_FROM',$hostname);
                        $mailtext = __('FTP_ACCOUNT_READY',$_GET['domain'],$user,$v_ftp_username,$v_ftp_user_data['v_ftp_password']);
                        send_email($to, $subject, $mailtext, $from);
                        unset($v_ftp_email);
                    }
                    unset($output);
                    unlink($v_ftp_password);
                    $v_ftp_password = escapeshellarg($v_ftp_user_data['v_ftp_password']);
                }

                if ($return_var == 0) {
                    $v_ftp_password = "";
                    $v_ftp_user_data['is_new'] = 0;
                }
                else {
                    $v_ftp_user_data['is_new'] = 1;
                }

                $v_ftp_users_updated[] = array(
                    'is_new'            => empty($_SESSION['error_msg']) ? 0 : 1,
                    'v_ftp_user'        => $v_ftp_username_full,
                    'v_ftp_password'    => $v_ftp_password,
                    'v_ftp_path'        => $v_ftp_user_data['v_ftp_path'],
                    'v_ftp_email'       => $v_ftp_user_data['v_ftp_email'],
                    'v_ftp_pre_path'    => $v_ftp_user_prepath
                );

                continue;
            }

            // Delete FTP account
            if ($v_ftp_user_data['delete'] == 1) {
                $v_ftp_username = $user . '_' . $v_ftp_user_data['v_ftp_user'];
                exec (VESTA_CMD."v-delete-web-domain-ftp ".$v_username." ".$v_domain." ".$v_ftp_username, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);

                continue;
            }

            if (!empty($_POST['v_ftp'])) {
                if (empty($v_ftp_user_data['v_ftp_user'])) $errors[] = __('ftp user');
                if (!empty($errors[0])) {
                    foreach ($errors as $i => $error) {
                        if ( $i == 0 ) {
                            $error_msg = $error;
                        } else {
                            $error_msg = $error_msg.", ".$error;
                        }
                    }
                    $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
                }

                // Change FTP account path
                $v_ftp_username = $user . '_' . $v_ftp_user_data['v_ftp_user']; //preg_replace("/^".$user."_/", "", $v_ftp_user_data['v_ftp_user']);
                $v_ftp_username = escapeshellarg($v_ftp_username);
                //if (!empty($v_ftp_user_data['v_ftp_path'])) {
                    $v_ftp_path = escapeshellarg(trim($v_ftp_user_data['v_ftp_path']));
                    exec (VESTA_CMD."v-change-web-domain-ftp-path ".$v_username." ".$v_domain." ".$v_ftp_username." ".$v_ftp_path, $output, $return_var);
                //}

                // Change FTP account password
                if (!empty($v_ftp_user_data['v_ftp_password'])) {
                    $v_ftp_password = tempnam("/tmp","vst");
                    $fp = fopen($v_ftp_password, "w");
                    fwrite($fp, $v_ftp_user_data['v_ftp_password']."\n");
                    fclose($fp);
                    exec (VESTA_CMD."v-change-web-domain-ftp-password ".$v_username." ".$v_domain." ".$v_ftp_username." ".$v_ftp_password, $output, $return_var);
                    unlink($v_ftp_password);

                    $to = $v_ftp_user_data['v_ftp_email'];
                    $subject = __("FTP login credentials");
                    $hostname = exec('hostname');
                    $from = __('MAIL_FROM',$hostname);
                    $mailtext = __('FTP_ACCOUNT_READY',$_GET['domain'],$user,$v_ftp_username,$v_ftp_user_data['v_ftp_password']);
                    send_email($to, $subject, $mailtext, $from);
                    unset($v_ftp_email);
                }
                check_return_code($return_var, $output);
                unset($output);

                $v_ftp_users_updated[] = array(
                    'is_new'            => 0,
                    'v_ftp_user'        => $v_ftp_username,
                    'v_ftp_password'    => $v_ftp_user_data['v_ftp_password'],
                    'v_ftp_path'        => $v_ftp_user_data['v_ftp_path'],
                    'v_ftp_email'       => $v_ftp_user_data['v_ftp_email'],
                    'v_ftp_pre_path'    => $v_ftp_user_prepath
                );
            }
        }
    }

    // Restart web server
    if (!empty($restart_web) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-web", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Restart proxy server
    if ((!empty($_SESSION['PROXY_SYSTEM'])) && !empty($restart_proxy) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-proxy", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Restart dns server
    if (!empty($restart_dns) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-dns", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }

}


$v_ftp_users_raw = explode(':', $v_ftp_user);
$v_ftp_users_paths_raw = explode(':', $data[$v_domain]['FTP_PATH']);
$v_ftp_users = array();
foreach ($v_ftp_users_raw as $v_ftp_user_index => $v_ftp_user_val) {
    if (empty($v_ftp_user_val)) {
        continue;
    }
    $v_ftp_users[] = array(
        'is_new'            => 0,
        'v_ftp_user'        => $v_ftp_user_val,
        'v_ftp_password'    => $v_ftp_password,
        'v_ftp_path'        => (isset($v_ftp_users_paths_raw[$v_ftp_user_index]) ? $v_ftp_users_paths_raw[$v_ftp_user_index] : ''),
        'v_ftp_email'       => $v_ftp_email,
        'v_ftp_pre_path'    => $v_ftp_user_prepath
    );
}

if (empty($v_ftp_users)) {
    $v_ftp_user = null;
    $v_ftp_users[] = array(
        'is_new'            => 1,
        'v_ftp_user'        => '',
        'v_ftp_password'    => '',
        'v_ftp_path'        => (isset($v_ftp_users_paths_raw[$v_ftp_user_index]) ? $v_ftp_users_paths_raw[$v_ftp_user_index] : ''),
        'v_ftp_email'       => '',
        'v_ftp_pre_path'    => $v_ftp_user_prepath
    );
}

// set default pre path for newly created users
$v_ftp_pre_path_new_user = $v_ftp_user_prepath;
if (isset($v_ftp_users_updated)) {
    $v_ftp_users = $v_ftp_users_updated;
    if (empty($v_ftp_users_updated)) {
        $v_ftp_user = null;
        $v_ftp_users[] = array(
            'is_new'            => 1,
            'v_ftp_user'        => '',
            'v_ftp_password'    => '',
            'v_ftp_path'        => (isset($v_ftp_users_paths_raw[$v_ftp_user_index]) ? $v_ftp_users_paths_raw[$v_ftp_user_index] : ''),
            'v_ftp_email'       => '',
            'v_ftp_pre_path'    => $v_ftp_user_prepath
        );
    }
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Display body
if ($_SESSION['user'] == 'admin') {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_web.html');
} else {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/edit_web.html');
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
