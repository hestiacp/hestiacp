<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'WEB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);
$v_ftp_email = $panel[$user]['CONTACT'];
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_domain'])) $errors[] = __('domain');
        if (empty($_POST['v_ip'])) $errors[] = __('ip');
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_crt']))) $errors[] = __('ssl certificate');
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_key']))) $errors[] = __('ssl key');
        if ((!empty($_POST['v_aliases'])) && ($_POST['v_aliases'] != 'www.'.$_POST['v_domain'])) $v_adv = 'yes';
        if ((!empty($_POST['v_ssl'])) || (!empty($_POST['v_elog']))) $v_adv = 'yes';
        if ((!empty($_POST['v_ssl_crt'])) || (!empty($_POST['v_ssl_key']))) $v_adv = 'yes';
        if ((!empty($_POST['v_ssl_ca'])) || ($_POST['v_stats'] != 'none')) $v_adv = 'yes';
        if (empty($_POST['v_proxy'])) $v_adv = 'yes';
        if (!empty($_POST['v_ftp'])) $v_adv = 'yes';

        $v_proxy_ext = 'jpeg, jpg, png, gif, bmp, ico, svg, tif, tiff, css, js, htm, html, ttf,';
        $v_proxy_ext .= 'otf, webp, woff, txt, csv, rtf, doc, docx, xls, xlsx, ppt, pptx, odf, ';
        $v_proxy_ext .= 'odp, ods, odt, pdf, psd, ai, eot, eps, ps, zip, tar, tgz, gz, rar, ';
        $v_proxy_ext .= 'bz2, 7z, aac, m4a, mp3, mp4, ogg, wav, wma, 3gp, avi, flv, m4v, mkv, ';
        $v_proxy_ext .= 'mov, mp4, mpeg, mpg, wmv, exe, iso, dmg, swf';
        if ($_POST['v_proxy_ext'] != $v_proxy_ext) $v_adv = 'yes';

        // Protect input
        $v_domain = preg_replace("/^www\./i", "", $_POST['v_domain']);
        $v_domain = escapeshellarg($v_domain);
        $v_domain = strtolower($v_domain);
        $v_ip = escapeshellarg($_POST['v_ip']);
        if (empty($_POST['v_dns'])) $v_dns = 'off';
        if (empty($_POST['v_mail'])) $v_mail = 'off';
        if (empty($_POST['v_proxy'])) $v_proxy = 'off';
        $v_aliases = $_POST['v_aliases'];
        $v_elog = $_POST['v_elog'];
        $v_ssl = $_POST['v_ssl'];
        $v_ssl_crt = $_POST['v_ssl_crt'];
        $v_ssl_key = $_POST['v_ssl_key'];
        $v_ssl_ca = $_POST['v_ssl_ca'];
        $v_ssl_home = $data[$v_domain]['SSL_HOME'];
        $v_stats = escapeshellarg($_POST['v_stats']);
        $v_stats_user = $data[$v_domain]['STATS_USER'];
        $v_stats_password = $data[$v_domain]['STATS_PASSWORD'];
        $v_proxy_ext = preg_replace("/\n/", " ", $_POST['v_proxy_ext']);
        $v_proxy_ext = preg_replace("/,/", " ", $v_proxy_ext);
        $v_proxy_ext = preg_replace('/\s+/', ' ',$v_proxy_ext);
        $v_proxy_ext = trim($v_proxy_ext);
        $v_proxy_ext = str_replace(' ', ", ", $v_proxy_ext);
        $v_ftp = $_POST['v_ftp'];
        $v_ftp_user = $_POST['v_ftp_user'];
        $v_ftp_password = $_POST['v_ftp_password'];
        $v_ftp_email = $_POST['v_ftp_email'];

        // Validate email
        if ((!empty($_POST['v_ftp_email'])) && (!filter_var($_POST['v_ftp_email'], FILTER_VALIDATE_EMAIL))) {
            $_SESSION['error_msg'] = __('Please enter valid email address.');
        }

        // Check ftp password length
        if ((!empty($_POST['v_ftp'])) && (empty($_SESSION['error_msg']))) {
            if (!empty($_POST['v_ftp_user'])) {
                $pw_len = strlen($_POST['v_ftp_password']);
                if ($pw_len < 6 ) $_SESSION['error_msg'] = __('Password is too short.',$error_msg);
            }
        }

        // Check stats password length
        if ((!empty($v_stats)) && (empty($_SESSION['error_msg']))) {
            if (!empty($_POST['v_stats_user'])) {
                $pw_len = strlen($_POST['v_stats_password']);
                if ($pw_len < 6 ) $_SESSION['error_msg'] = __('Password is too short.',$error_msg);
            }
        }

        // Check for errors
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

        if (empty($_SESSION['error_msg'])) {
            // Add WEB
            exec (VESTA_CMD."v-add-web-domain ".$user." ".$v_domain." ".$v_ip." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);

            // Add DNS
            if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v-add-dns-domain ".$user." ".$v_domain." ".$v_ip, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            // Add Mail
            if (($_POST['v_mail'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v-add-mail-domain ".$user." ".$v_domain, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            // Add Aliases
            if ((!empty($_POST['v_aliases'])) && (empty($_SESSION['error_msg']))) {
                $valiases = preg_replace("/\n/", " ", $_POST['v_aliases']);
                $valiases = preg_replace("/,/", " ", $valiases);
                $valiases = preg_replace('/\s+/', ' ',$valiases);
                $valiases = trim($valiases);
                $aliases = explode(" ", $valiases);
                foreach ($aliases as $alias) {
                    if ($alias == 'www.'.$_POST['v_domain']) {
                        $www_alias = 'yes';
                    } else {
                        $alias = escapeshellarg($alias);
                        if (empty($_SESSION['error_msg'])) {
                            exec (VESTA_CMD."v-add-web-domain-alias ".$user." ".$v_domain." ".$alias." 'no'", $output, $return_var);
                            check_return_code($return_var,$output);
                        }
                        unset($output);
                        if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                            exec (VESTA_CMD."v-add-dns-on-web-alias ".$user." ".$v_domain." ".$alias." 'no'", $output, $return_var);
                            check_return_code($return_var,$output);
                            unset($output);
                        }
                    }
                }
            }
            if ((empty($www_alias)) && (empty($_SESSION['error_msg']))) {
                $alias =  preg_replace("/^www./i", "", $_POST['v_domain']);
                $alias = 'www.'.$alias;
                $alias = escapeshellarg($alias);
                exec (VESTA_CMD."v-delete-web-domain-alias ".$user." ".$v_domain." ".$alias." 'no'", $output, $return_var);
                check_return_code($return_var,$output);
            }


            // Add proxy
            if (($_POST['v_proxy'] == 'on') && (empty($_SESSION['error_msg']))) {
                $ext = str_replace(' ', '', $v_proxy_ext);
                $ext = escapeshellarg($ext);
                exec (VESTA_CMD."v-add-web-domain-proxy ".$user." ".$v_domain." '' ".$ext." 'no'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            // Add SSL
            if (!empty($_POST['v_ssl'])) {
                exec ('mktemp -d', $output, $return_var);
                $tmpdir = $output[0];

                // Certificate
                if (!empty($_POST['v_ssl_crt'])) {
                    $fp = fopen($tmpdir."/".$_POST['v_domain'].".crt", 'w');
                    fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_crt']));
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

                $v_ssl_home = escapeshellarg($_POST['v_ssl_home']);
                exec (VESTA_CMD."v-add-web-domain-ssl ".$user." ".$v_domain." ".$tmpdir." ".$v_ssl_home." 'no'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            // Add WebStats
            if ((!empty($_POST['v_stats'])) && ($_POST['v_stats'] != 'none' ) && (empty($_SESSION['error_msg']))) {
                $v_stats = escapeshellarg($_POST['v_stats']);
                exec (VESTA_CMD."v-add-web-domain-stats ".$user." ".$v_domain." ".$v_stats, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);

                if ((!empty($_POST['v_stats_user'])) && (empty($_SESSION['error_msg']))) {
                    $v_stats_user = escapeshellarg($_POST['v_stats_user']);
                    $v_stats_password = escapeshellarg($_POST['v_stats_password']);
                    exec (VESTA_CMD."v-add-web-domain-stats-user ".$user." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
                    check_return_code($return_var,$output);
                    unset($v_stats_user);
                    unset($v_stats_password);
                    unset($output);
                }
            }


            // Add FTP
            if ((!empty($_POST['v_ftp'])) && (empty($_SESSION['error_msg']))) {
                $v_ftp_user = escapeshellarg($_POST['v_ftp_user']);
                $v_ftp_password = escapeshellarg($_POST['v_ftp_password']);
                exec (VESTA_CMD."v-add-web-domain-ftp ".$user." ".$v_domain." ".$v_ftp_user." ".$v_ftp_password, $output, $return_var);
                check_return_code($return_var,$output);
                if (empty($_SESSION['error_msg'])) {
                    if (!empty($v_ftp_email)) {
                        $to = $_POST['v_ftp_email'];
                        $subject = __("FTP login credentials");
                        $hostname = exec('hostname');
                        $from = __('MAIL_FROM',$hostname);
                        $mailtext .= __('FTP_ACCOUNT_READY',$_POST['v_domain'],$user,$_POST['v_ftp_user'],$_POST['v_ftp_password']);
                        send_email($to, $subject, $mailtext, $from);
                    }
                }
                unset($v_ftp);
                unset($v_ftp_user);
                unset($v_ftp_password);
                unset($output);
            }

            if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v-restart-dns", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                exec (VESTA_CMD."v-restart-web", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                exec (VESTA_CMD."v-restart-proxy", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                unset($output);
                $_SESSION['ok_msg'] = __('WEB_DOMAIN_CREATED_OK',$_POST[v_domain],$_POST[v_domain]);
                unset($v_domain);
                unset($v_aliases);
                unset($v_ssl);
                unset($v_ssl_crt);
                unset($v_ssl_key);
                unset($v_ssl_ca);
            }
        }
    }

    exec (VESTA_CMD."v-list-user-ips ".$user." json", $output, $return_var);
    $ips = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v-list-web-stats json", $output, $return_var);
    $stats = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_web.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
