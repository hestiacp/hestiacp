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

    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_domain'])) $errors[] = _('domain');
        if (empty($_POST['v_ip'])) $errors[] = _('ip');
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_crt']))) $errors[] = _('ssl certificate');
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_key']))) $errors[] = _('ssl key');
        if ((!empty($_POST['v_stats_user'])) && (empty($_POST['v_stats_password']))) $errors[] = _('stats user password');
        if ((!empty($_POST['v_ftp_user'])) && (empty($_POST['v_ftp_password']))) $errors[] = _('ftp user password');

        if ((!empty($_POST['v_aliases'])) && ($_POST['v_aliases'] != 'www.'.$_POST['v_domain'])) $v_adv = 'yes';
        if ((!empty($_POST['v_ssl'])) || (!empty($_POST['v_elog']))) $v_adv = 'yes';
        if ((!empty($_POST['v_ssl_crt'])) || (!empty($_POST['v_ssl_key']))) $v_adv = 'yes';
        if ((!empty($_POST['v_ssl_ca'])) || ($_POST['v_stats'] != 'none')) $v_adv = 'yes';
        if (empty($_POST['v_nginx'])) $v_adv = 'yes';
        if (!empty($_POST['v_ftp'])) $v_adv = 'yes';

        $v_nginx_ext = 'jpg, jpeg, gif, png, ico, svg, css, zip, tgz, gz, rar, bz2, exe, pdf, ';
        $v_nginx_ext .= 'doc, xls, ppt, txt, odt, ods, odp, odf, tar, bmp, rtf, js, mp3, avi, ';
        $v_nginx_ext .= 'mpeg, flv, html, htm';
        if ($_POST['v_nginx_ext'] != $v_nginx_ext) $v_adv = 'yes';

        // Protect input
        $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
        $v_domain = escapeshellarg($v_domain);
        $v_ip = escapeshellarg($_POST['v_ip']);
        if ($_SESSION['user'] == 'admin') {
            $v_template = escapeshellarg($_POST['v_template']);
        } else {
            $v_template = "''";
        }
        if (empty($_POST['v_dns'])) $v_dns = 'off';
        if (empty($_POST['v_mail'])) $v_mail = 'off';
        if (empty($_POST['v_nginx'])) $v_nginx = 'off';
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
        $v_nginx_ext = preg_replace("/\n/", " ", $_POST['v_nginx_ext']);
        $v_nginx_ext = preg_replace("/,/", " ", $v_nginx_ext);
        $v_nginx_ext = preg_replace('/\s+/', ' ',$v_nginx_ext);
        $v_nginx_ext = trim($v_nginx_ext);
        $v_nginx_ext = str_replace(' ', ", ", $v_nginx_ext);
        $v_ftp = $_POST['v_ftp'];
        $v_ftp_user = $_POST['v_ftp_user'];
        $v_ftp_password = $_POST['v_ftp_password'];
        $v_ftp_email = $_POST['v_ftp_email'];

        // Validate email
        if ((!empty($_POST['v_ftp_email'])) && (!filter_var($_POST['v_ftp_email'], FILTER_VALIDATE_EMAIL))) {
            $_SESSION['error_msg'] = _('Please enter valid email address.');
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
            $_SESSION['error_msg'] = _('Error: field "%s" can not be blank.',$error_msg);
        }

        if (empty($_SESSION['error_msg'])) {
            // Add WEB
            exec (VESTA_CMD."v-add-web-domain ".$user." ".$v_domain." ".$v_ip." ".$v_template." 'no'", $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = _('Error: vesta did not return any output.');
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
        
            // Add DNS
            if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v-add-dns-domain ".$user." ".$v_domain." ".$v_ip, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add Mail
            if (($_POST['v_mail'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v-add-mail-domain ".$user." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
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
                            if ($return_var != 0) {
                                $error = implode('<br>', $output);
                                if (empty($error)) $error = _('Error: vesta did not return any output.');
                                $_SESSION['error_msg'] = $error;
                            }
                        }
                        unset($output);
                        if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                            exec (VESTA_CMD."v-add-dns-on-web-alias ".$user." ".$v_domain." ".$alias." 'no'", $output, $return_var);
                            if ($return_var != 0) {
                                $error = implode('<br>', $output);
                                if (empty($error)) $error = _('Error: vesta did not return any output.');
                                $_SESSION['error_msg'] = $error;
                            }
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
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
            }


            // Add Nginx
            if (($_POST['v_nginx'] == 'on') && (empty($_SESSION['error_msg']))) {
                $ext = str_replace(' ', '', $v_nginx_ext);
                $ext = escapeshellarg($ext);
                exec (VESTA_CMD."v-add-web-domain-nginx ".$user." ".$v_domain." 'default' ".$ext." 'no'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
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
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add WebStats
            if ((!empty($_POST['v_stats'])) && ($_POST['v_stats'] != 'none' ) && (empty($_SESSION['error_msg']))) {
                $v_stats = escapeshellarg($_POST['v_stats']);
                exec (VESTA_CMD."v-add-web-domain-stats ".$user." ".$v_domain." ".$v_stats, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);

                if ((!empty($_POST['v_stats_user'])) && (empty($_SESSION['error_msg']))) {
                    $v_stats_user = escapeshellarg($_POST['v_stats_user']);
                    $v_stats_password = escapeshellarg($_POST['v_stats_password']);
                    exec (VESTA_CMD."v-add-web-domain-stats-user ".$user." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
                    if ($return_var != 0) {
                        $error = implode('<br>', $output);
                        if (empty($error)) $error = _('Error: vesta did not return any output.');
                        $_SESSION['error_msg'] = $error;
                    }
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
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                } else {
                    if (!empty($v_ftp_email)) {
                        $to = $_POST['v_ftp_email'];
                        $subject = _("FTP login credentials");
                        $hostname = exec('hostname');
                        $from = _('MAIL_FROM',$hostname);
                        $mailtext .= _('FTP_ACCOUNT_READY',$_POST['v_domain'],$user,$_POST['v_ftp_user'],$_POST['v_ftp_password']);
                        send_email($to, $subject, $mailtext, $from);
                        unset($v_ftp_email);
                    }
                }
                unset($v_ftp);
                unset($v_ftp_user);
                unset($v_ftp_password);
                unset($output);
            }

            if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v-restart-dns", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                exec (VESTA_CMD."v-restart-web", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
                $_SESSION['ok_msg'] = _('HOSTING_DOMAIN_CREATED_OK',$_POST[v_domain],$_POST[v_domain]);
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

    exec (VESTA_CMD."v-get-user-value ".$user." 'TEMPLATE'", $output, $return_var);
    $template = $output[0] ;
    unset($output);

    exec (VESTA_CMD."v-list-web-templates json", $output, $return_var);
    $templates = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v-list-web-stats json", $output, $return_var);
    $stats = json_decode(implode('', $output), true);
    unset($output);

// Are you admin?
if ($_SESSION['user'] == 'admin') {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_web.html');
} else {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/add_web.html');
}
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
