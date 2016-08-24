<?php
error_reporting(NULL);
ob_start();
$TAB = 'WEB';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Check for empty fields
    if (empty($_POST['v_domain'])) $errors[] = __('domain');
    if (empty($_POST['v_ip'])) $errors[] = __('ip');
    if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_crt']))) $errors[] = __('ssl certificate');
    if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_key']))) $errors[] = __('ssl key');
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

    // Check stats password length
    if ((!empty($v_stats)) && (empty($_SESSION['error_msg']))) {
        if (!empty($_POST['v_stats_user'])) {
            $pw_len = strlen($_POST['v_stats_password']);
            if ($pw_len < 6 ) $_SESSION['error_msg'] = __('Password is too short.',$error_msg);
        }
    }

    // Set domain to lowercase and remove www prefix
    $v_domain = preg_replace("/^www\./i", "", $_POST['v_domain']);
    $v_domain = escapeshellarg($v_domain);
    $v_domain = strtolower($v_domain);

    // Define domain ip address
    $v_ip = escapeshellarg($_POST['v_ip']);

    // Define domain aliases
    $v_aliases = $_POST['v_aliases'];
    $aliases = preg_replace("/\n/", ",", $v_aliases);
    $aliases = preg_replace("/\r/", ",", $aliases);
    $aliases = preg_replace("/\t/", ",", $aliases);
    $aliases = preg_replace("/ /", ",", $aliases);
    $aliases_arr = explode(",", $aliases);
    $aliases_arr = array_unique($aliases_arr);
    $aliases_arr = array_filter($aliases_arr);
    $aliases = implode(",",$aliases_arr);
    $aliases = escapeshellarg($aliases);
    if (empty($_POST['v_aliases'])) $aliases = 'none';

    // Define proxy extensions
    $v_proxy_ext = $_POST['v_proxy_ext'];
    $proxy_ext = preg_replace("/\n/", ",", $v_proxy_ext);
    $proxy_ext = preg_replace("/\r/", ",", $proxy_ext);
    $proxy_ext = preg_replace("/\t/", ",", $proxy_ext);
    $proxy_ext = preg_replace("/ /", ",", $proxy_ext);
    $proxy_ext_arr = explode(",", $proxy_ext);
    $proxy_ext_arr = array_unique($proxy_ext_arr);
    $proxy_ext_arr = array_filter($proxy_ext_arr);
    $proxy_ext = implode(",",$proxy_ext_arr);
    $proxy_ext = escapeshellarg($proxy_ext);

    // Define other options
    $v_elog = $_POST['v_elog'];
    $v_ssl = $_POST['v_ssl'];
    $v_ssl_crt = $_POST['v_ssl_crt'];
    $v_ssl_key = $_POST['v_ssl_key'];
    $v_ssl_ca = $_POST['v_ssl_ca'];
    $v_ssl_home = $data[$v_domain]['SSL_HOME'];
    $v_stats = escapeshellarg($_POST['v_stats']);
    $v_stats_user = $data[$v_domain]['STATS_USER'];
    $v_stats_password = $data[$v_domain]['STATS_PASSWORD'];
    $v_ftp = $_POST['v_ftp'];
    $v_ftp_user = $_POST['v_ftp_user'];
    $v_ftp_password = $_POST['v_ftp_password'];
    $v_ftp_email = $_POST['v_ftp_email'];
    if (!empty($v_domain)) $v_ftp_user_prepath .= $v_domain;

    // Set advanced option checkmark
    if (empty($_POST['v_proxy'])) $v_adv = 'yes';
    if (!empty($_POST['v_ftp'])) $v_adv = 'yes';
    if ($_POST['v_proxy_ext'] != $v_proxy_ext) $v_adv = 'yes';
    if ((!empty($_POST['v_aliases'])) && ($_POST['v_aliases'] != 'www.'.$_POST['v_domain'])) $v_adv = 'yes';
    if ((!empty($_POST['v_ssl'])) || (!empty($_POST['v_elog']))) $v_adv = 'yes';
    if ((!empty($_POST['v_ssl_crt'])) || (!empty($_POST['v_ssl_key']))) $v_adv = 'yes';
    if ((!empty($_POST['v_ssl_ca'])) || ($_POST['v_stats'] != 'none')) $v_adv = 'yes';

    // Check advanced features
    if (empty($_POST['v_dns'])) $v_dns = 'off';
    if (empty($_POST['v_mail'])) $v_mail = 'off';
    if (empty($_POST['v_proxy'])) $v_proxy = 'off';

    // Add web domain
    if (empty($_SESSION['error_msg'])) {
        exec (VESTA_CMD."v-add-web-domain ".$user." ".$v_domain." ".$v_ip." 'no' ".$aliases." ".$proxy_ext, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $domain_added = empty($_SESSION['error_msg']);
    }

    // Add DNS domain
    if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-add-dns-domain ".$user." ".$v_domain." ".$v_ip, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Add DNS for domain aliases
    if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
        foreach ($aliases_arr as $alias) {
            if ($alias != "www.".$_POST['v_domain']) {
                $alias = escapeshellarg($alias);
                exec (VESTA_CMD."v-add-dns-on-web-alias ".$user." ".$alias." ".$v_ip." 'no'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    // Add mail domain
    if (($_POST['v_mail'] == 'on') && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-add-mail-domain ".$user." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Delete proxy support
    if ((!empty($_SESSION['PROXY_SYSTEM'])) && ($_POST['v_proxy'] == 'off')  && (empty($_SESSION['error_msg']))) {
        $ext = escapeshellarg($ext);
        exec (VESTA_CMD."v-delete-web-domain-proxy ".$user." ".$v_domain." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Add SSL certificates
    if ((!empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        exec ('mktemp -d', $output, $return_var);
        $tmpdir = $output[0];
        unset($output);

        // Save certificate
        if (!empty($_POST['v_ssl_crt'])) {
            $fp = fopen($tmpdir."/".$_POST['v_domain'].".crt", 'w');
            fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_crt']));
            fwrite($fp, "\n");
            fclose($fp);
        }

        // Save private key
        if (!empty($_POST['v_ssl_key'])) {
            $fp = fopen($tmpdir."/".$_POST['v_domain'].".key", 'w');
            fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
            fwrite($fp, "\n");
            fclose($fp);
        }

        // Save CA bundle
        if (!empty($_POST['v_ssl_ca'])) {
            $fp = fopen($tmpdir."/".$_POST['v_domain'].".ca", 'w');
            fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_ca']));
            fwrite($fp, "\n");
            fclose($fp);
        }

        $v_ssl_home = escapeshellarg($_POST['v_ssl_home']);
        exec (VESTA_CMD."v-add-web-domain-ssl ".$user." ".$v_domain." ".$tmpdir." ".$v_ssl_home." 'no'", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Add web stats
    if ((!empty($_POST['v_stats'])) && ($_POST['v_stats'] != 'none' ) && (empty($_SESSION['error_msg']))) {
        $v_stats = escapeshellarg($_POST['v_stats']);
        exec (VESTA_CMD."v-add-web-domain-stats ".$user." ".$v_domain." ".$v_stats, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Add web stats password
    if ((!empty($_POST['v_stats_user'])) && (empty($_SESSION['error_msg']))) {
        $v_stats_user = escapeshellarg($_POST['v_stats_user']);
        $v_stats_password = tempnam("/tmp","vst");
        $fp = fopen($v_stats_password, "w");
        fwrite($fp, $_POST['v_stats_password']."\n");
        fclose($fp);
        exec (VESTA_CMD."v-add-web-domain-stats-user ".$user." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($v_stats_password);
        $v_stats_password = escapeshellarg($_POST['v_stats_password']);
    }

    // Restart DNS server
    if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-dns", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Restart web server
    if (empty($_SESSION['error_msg'])) {
        exec (VESTA_CMD."v-restart-web", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Restart backend server
    //if ((!empty($_SESSION['WEB_BACKEND'])) && (empty($_SESSION['error_msg']))) {
    //    exec (VESTA_CMD."v-restart-web-backend", $output, $return_var);
    //    check_return_code($return_var,$output);
    //    unset($output);
    //}

    // Restart proxy server
    if ((!empty($_SESSION['PROXY_SYSTEM'])) && ($_POST['v_proxy'] == 'on') && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-proxy", $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Add FTP
    if ((!empty($_POST['v_ftp'])) && (empty($_SESSION['error_msg']))) {
        $v_ftp_users_updated = array();
        foreach ($_POST['v_ftp_user'] as $i => $v_ftp_user_data) {
            if ($v_ftp_user_data['is_new'] == 1) {
                if ((!empty($v_ftp_user_data['v_ftp_email'])) && (!filter_var($v_ftp_user_data['v_ftp_email'], FILTER_VALIDATE_EMAIL))) $_SESSION['error_msg'] = __('Please enter valid email address.');
                if (empty($v_ftp_user_data['v_ftp_user'])) $errors[] = 'ftp user';
                if (empty($v_ftp_user_data['v_ftp_password'])) $errors[] = 'ftp user password';
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

                // Validate email
                if ((!empty($v_ftp_user_data['v_ftp_email'])) && (!filter_var($v_ftp_user_data['v_ftp_email'], FILTER_VALIDATE_EMAIL))) {
                    $_SESSION['error_msg'] = __('Please enter valid email address.');
                }

                // Check ftp password length
                if ((!empty($v_ftp_user_data['v_ftp']))) {
                    if (!empty($v_ftp_user_data['v_ftp_user'])) {
                        $pw_len = strlen($v_ftp_user_data['v_ftp_password']);
                        if ($pw_len < 6 ) $_SESSION['error_msg'] = __('Password is too short.',$error_msg);
                    }
                }

                $v_ftp_user_data['v_ftp_user'] = preg_replace("/^".$user."_/i", "", $v_ftp_user_data['v_ftp_user']);
                $v_ftp_username      = $v_ftp_user_data['v_ftp_user'];
                $v_ftp_username_full = $user . '_' . $v_ftp_user_data['v_ftp_user'];
                $v_ftp_user = escapeshellarg($v_ftp_user_data['v_ftp_user']);
                if ($domain_added) {
                    $v_ftp_path = escapeshellarg(trim($v_ftp_user_data['v_ftp_path']));
                    $v_ftp_password = tempnam("/tmp","vst");
                    $fp = fopen($v_ftp_password, "w");
                    fwrite($fp, $v_ftp_user_data['v_ftp_password']."\n");
                    fclose($fp);
                    exec (VESTA_CMD."v-add-web-domain-ftp ".$user." ".$v_domain." ".$v_ftp_user." ".$v_ftp_password . " " . $v_ftp_path, $output, $return_var);
                    check_return_code($return_var,$output);
                    unset($output);
                    unlink($v_ftp_password);
                    if ((!empty($v_ftp_user_data['v_ftp_email'])) && (empty($_SESSION['error_msg']))) {
                        $to = $v_ftp_user_data['v_ftp_email'];
                        $subject = __("FTP login credentials");
                        $from = __('MAIL_FROM',$_POST['v_domain']);
                        $mailtext = __('FTP_ACCOUNT_READY',$_POST['v_domain'],$user,$v_ftp_user_data['v_ftp_user'],$v_ftp_user_data['v_ftp_password']);
                        send_email($to, $subject, $mailtext, $from);
                        unset($v_ftp_email);
                    }
                } else {
                    $return_var = -1;
                }

                if ($return_var == 0) {
                    $v_ftp_password = "••••••••";
                    $v_ftp_user_data['is_new'] = 0;
                } else {
                    $v_ftp_user_data['is_new'] = 1;
                }

                $v_ftp_username = preg_replace("/^".$user."_/", "", $v_ftp_user_data['v_ftp_user']);
                $v_ftp_users_updated[] = array(
                    'is_new'            => $v_ftp_user_data['is_new'],
                    'v_ftp_user'        => $return_var == 0 ? $v_ftp_username_full : $v_ftp_username,
                    'v_ftp_password'    => $v_ftp_password,
                    'v_ftp_path'        => $v_ftp_user_data['v_ftp_path'],
                    'v_ftp_email'       => $v_ftp_user_data['v_ftp_email'],
                    'v_ftp_pre_path'    => $v_ftp_user_prepath
                );
                continue;
            }
        }

        if (!empty($_SESSION['error_msg']) && $domain_added) {
            $_SESSION['ok_msg'] = __('WEB_DOMAIN_CREATED_OK',htmlentities($_POST[v_domain]),htmlentities($_POST[v_domain]));
            $_SESSION['flash_error_msg'] = $_SESSION['error_msg'];
            $url = '/edit/web/?domain='.strtolower(preg_replace("/^www\./i", "", $_POST['v_domain']));
            header('Location: ' . $url);
            exit;
        }
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('WEB_DOMAIN_CREATED_OK',htmlentities($_POST[v_domain]),htmlentities($_POST[v_domain]));
        unset($v_domain);
        unset($v_aliases);
        unset($v_ssl);
        unset($v_ssl_crt);
        unset($v_ssl_key);
        unset($v_ssl_ca);
        unset($v_stats_user);
        unset($v_stats_password);
        unset($v_ftp);
    }
}


// Define user variables
$v_ftp_user_prepath = $panel[$user]['HOME'] . "/web";
$v_ftp_email = $panel[$user]['CONTACT'];

// List IP addresses
exec (VESTA_CMD."v-list-user-ips ".$user." json", $output, $return_var);
$ips = json_decode(implode('', $output), true);
unset($output);

// List web stat engines
exec (VESTA_CMD."v-list-web-stats json", $output, $return_var);
$stats = json_decode(implode('', $output), true);
unset($output);

// Render page
render_page($user, $TAB, 'add_web');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
