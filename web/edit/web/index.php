<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
unset($_SESSION['error_msg']);

$TAB = 'WEB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Check user argument?
if (empty($_GET['domain'])) {
    header("Location: /list/web/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// Check domain
$v_domain = escapeshellarg($_GET['domain']);
exec (VESTA_CMD."v-list-web-domain ".$user." ".$v_domain." json", $output, $return_var);
if ($return_var != 0) {
    $error = implode('<br>', $output);
    if (empty($error)) $error = __('Error code:',$return_var);
    $_SESSION['error_msg'] = $error;
} else {
    $data = json_decode(implode('', $output), true);
    unset($output);
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
    $v_proxy = $data[$v_domain]['PROXY'];
    $v_proxy_template = $data[$v_domain]['PROXY'];
    $v_proxy_ext = str_replace(',', ', ', $data[$v_domain]['PROXY_EXT']);
    $v_stats = $data[$v_domain]['STATS'];
    $v_stats_user = $data[$v_domain]['STATS_USER'];
    if (!empty($v_stats_user)) $v_stats_password = "••••••••";
    $v_ftp_user = $data[$v_domain]['FTP_USER'];
    if (!empty($v_ftp_user)) $v_ftp_password = "••••••••";
    $v_suspended = $data[$v_domain]['SUSPENDED'];
    if ( $v_suspended == 'yes' ) {
        $v_status =  'suspended';
    } else {
        $v_status =  'active';
    }
    $v_time = $data[$v_domain]['TIME'];
    $v_date = $data[$v_domain]['DATE'];

    exec (VESTA_CMD."v-list-user-ips ".$user." json", $output, $return_var);
    $ips = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v-list-web-templates json", $output, $return_var);
    $templates = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v-list-web-templates-proxy json", $output, $return_var);
    $proxy_templates = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v-list-web-stats json", $output, $return_var);
    $stats = json_decode(implode('', $output), true);
    unset($output);
}


// Action
$v_ftp_email = $panel[$user]['CONTACT'];
if (!empty($_POST['save'])) {
    $v_domain = escapeshellarg($_POST['v_domain']);

    // IP
    if (($v_ip != $_POST['v_ip']) && (empty($_SESSION['error_msg']))) {
        $v_ip = escapeshellarg($_POST['v_ip']);
        exec (VESTA_CMD."v-change-web-domain-ip ".$v_username." ".$v_domain." ".$v_ip." 'no'", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        $restart_web = 'yes';
        unset($output);
        exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain." json", $output, $return_var);
        if ((empty($_SESSION['error_msg'])) && ($return_var == 0 )) {
            unset($output);
            exec (VESTA_CMD."v-change-dns-domain-ip ".$v_username." ".$v_domain." ".$v_ip." 'no'", $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            $restart_dns = 'yes';
        }
        unset($output);
        foreach($valiases as $v_alias ){
            exec (VESTA_CMD."v-list-dns-domain ".$v_username." '".$v_alias."' json", $output, $return_var);
            if ((empty($_SESSION['error_msg'])) && ($return_var == 0 )) {
                exec (VESTA_CMD."v-change-dns-domain-ip ".$v_username." '".$v_alias."' ".$v_ip, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = __('Error code:',$return_var);
                    $_SESSION['error_msg'] = $error;
                }
                $restart_dns = 'yes';
            }
            unset($output);
        }
    }

    // Template
    if (( $_SESSION['user'] == 'admin') && ($v_template != $_POST['v_template']) && (empty($_SESSION['error_msg']))) {
        $v_template = escapeshellarg($_POST['v_template']);
        exec (VESTA_CMD."v-change-web-domain-tpl ".$v_username." ".$v_domain." ".$v_template." 'no'", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        $restart_web = 'yes';
    }

    // Aliases
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
                $v_template = escapeshellarg($_POST['v_template']);
                exec (VESTA_CMD."v-delete-web-domain-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = __('Error code:',$return_var);
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);

                if (empty($_SESSION['error_msg'])) {
                    exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain, $output, $return_var);
                    unset($output);
                    if ($return_var == 0) {
                        exec (VESTA_CMD."v-delete-dns-on-web-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                        if ($return_var != 0) {
                            $error = implode('<br>', $output);
                            if (empty($error)) $error = __('Error code:',$return_var);
                            $_SESSION['error_msg'] = $error;
                        }
                        $restart_dns = 'yes';
                    }
                    unset($output);
                }
            }
        }

        $result = array_diff($aliases, $valiases);
        foreach ($result as $alias) {
            if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                $restart_web = 'yes';
                $v_template = escapeshellarg($_POST['v_template']);
                exec (VESTA_CMD."v-add-web-domain-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = __('Error code:',$return_var);
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
                if (empty($_SESSION['error_msg'])) {
                    exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain, $output, $return_var);
                    unset($output);
                    if ($return_var == 0) {
                        exec (VESTA_CMD."v-add-dns-on-web-alias ".$v_username." ".$v_domain." '".$alias."' 'no'", $output, $return_var);
                        if ($return_var != 0) {
                            $error = implode('<br>', $output);
                            if (empty($error)) $error = __('Error code:',$return_var);
                            $_SESSION['error_msg'] = $error;
                        }
                        $restart_dns = 'yes';
                    }
                }
                unset($output);
            }
        }
    }

    // Proxy
    if ((!empty($v_proxy)) && (empty($_POST['v_proxy'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-proxy ".$v_username." ".$v_domain." 'no'", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        unset($v_proxy);
        $restart_proxy = 'yes';
    }
    if ((!empty($v_proxy)) && (!empty($_POST['v_proxy'])) && (empty($_SESSION['error_msg']))) {
        $ext = preg_replace("/\n/", " ", $_POST['v_proxy_ext']);
        $ext = preg_replace("/,/", " ", $ext);
        $ext = preg_replace('/\s+/', ' ',$ext);
        $ext = trim($ext);
        $ext = str_replace(' ', ", ", $ext);
        if (( $v_proxy_template != $_POST['v_proxy_template']) ||  ($v_proxy_ext != $ext)) {
            $ext = str_replace(', ', ",", $ext);
            if (!empty($_POST['v_proxy_template'])) $v_proxy_template = $_POST['v_proxy_template'];
            exec (VESTA_CMD."v-change-web-domain-proxy-tpl ".$v_username." ".$v_domain." ".escapeshellarg($v_proxy_template)." ".escapeshellarg($ext)." 'no'", $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            $v_proxy_ext = str_replace(',', ', ', $ext);
            unset($output);
            $restart_proxy = 'yes';
        }
    }
    if ((empty($v_proxy)) && (!empty($_POST['v_proxy'])) && (empty($_SESSION['error_msg']))) {
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
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        $restart_proxy = 'yes';
    }

    // SSL
    if (( $v_ssl == 'yes' ) && (empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-ssl ".$v_username." ".$v_domain." 'no'", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        $v_ssl = 'no';
        $restart_web = 'yes';
    }
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
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
            $restart_web = 'yes';
            $v_ssl_crt = $_POST['v_ssl_crt'];
            $v_ssl_key = $_POST['v_ssl_key'];
            $v_ssl_ca = $_POST['v_ssl_ca'];
        }
    }
    if (( $v_ssl == 'yes') && (!empty($_POST['v_ssl'])) && (empty($_SESSION['error_msg']))) {
        if ( $v_ssl_home != $_POST['v_ssl_home'] ) {
            $v_ssl_home = escapeshellarg($_POST['v_ssl_home']);
            exec (VESTA_CMD."v-change-web-domain-sslhome ".$user." ".$v_domain." ".$v_ssl_home." 'no'", $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            $v_ssl_home = $_POST['v_ssl_home'];
            unset($output);
        }
    }
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
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
            $v_ssl = 'yes';
            $restart_web = 'yes';
            $v_ssl_crt = $_POST['v_ssl_crt'];
            $v_ssl_key = $_POST['v_ssl_key'];
            $v_ssl_ca = $_POST['v_ssl_ca'];
            $v_ssl_home = $_POST['v_ssl_home'];
        }
    }

    // Web Stats
    if ((!empty($v_stats)) && ($_POST['v_stats'] == 'none') && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-stats ".$v_username." ".$v_domain, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        $v_stats = '';
    }
    if ((!empty($v_stats)) && ($_POST['v_stats'] != $v_stats) && (empty($_SESSION['error_msg']))) {
        $v_stats = escapeshellarg($_POST['v_stats']);
        exec (VESTA_CMD."v-change-web-domain-stats ".$v_username." ".$v_domain." ".$v_stats, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
    }
    if ((empty($v_stats)) && ($_POST['v_stats'] != 'none') && (empty($_SESSION['error_msg']))) {
        $v_stats = escapeshellarg($_POST['v_stats']);
        exec (VESTA_CMD."v-add-web-domain-stats ".$v_username." ".$v_domain." ".$v_stats, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
    }

    // Web Stats Auth
    if ((!empty($v_stats_user)) && (empty($_POST['v_stats_auth'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-stats-user ".$v_username." ".$v_domain, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        $v_stats_user = '';
        $v_stats_password = '';
    }
    if ((empty($v_stats_user)) && (!empty($_POST['v_stats_auth'])) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_stats_user'])) $errors[] = __('stats username');
        if (empty($_POST['v_stats_password'])) $errors[] = __('stats password');
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
            $v_stats_password = escapeshellarg($_POST['v_stats_password']);
            exec (VESTA_CMD."v-add-web-domain-stats-user ".$v_username." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
            $v_stats_password = "••••••••";
        }
    }
    if ((!empty($v_stats_user)) && (!empty($_POST['v_stats_auth'])) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_stats_user'])) $errors[] = __('stats user');
        if (empty($_POST['v_stats_password'])) $errors[] = __('stats password');
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
        if (($v_stats_user != $_POST['v_stats_user']) || ($_POST['v_stats_password'] != "••••••••" ) && (empty($_SESSION['error_msg']))) {
            $v_stats_user = escapeshellarg($_POST['v_stats_user']);
            $v_stats_password = escapeshellarg($_POST['v_stats_password']);
            exec (VESTA_CMD."v-add-web-domain-stats-user ".$v_username." ".$v_domain." ".$v_stats_user." ".$v_stats_password, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
            $v_stats_password = "••••••••";
        }
    }

    // Delete FTP Account
    if ((!empty($v_ftp_user)) && (empty($_POST['v_ftp'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-web-domain-ftp ".$v_username." ".$v_domain, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
        unset($output);
        $v_ftp= '';
        $v_ftp_user = '';
        $v_ftp_password = '';
    }

    // Change FTP Account
    if ((!empty($v_ftp_user)) && (!empty($_POST['v_ftp'])) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_ftp_user'])) $errors[] = __('ftp user');
        if (empty($_POST['v_ftp_password'])) $errors[] = __('ftp user password');
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
        if (($v_ftp_user != $_POST['v_ftp_user']) || ($_POST['v_ftp_password'] != "••••••••" ) && (empty($_SESSION['error_msg']))) {
            $v_ftp_user = preg_replace("/^".$user."_/", "", $_POST['v_ftp_user']);
            $v_ftp_user = escapeshellarg($v_ftp_user);
            $v_ftp_password = escapeshellarg($_POST['v_ftp_password']);
            exec (VESTA_CMD."v-add-web-domain-ftp ".$v_username." ".$v_domain." ".$v_ftp_user." ".$v_ftp_password, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
            $v_ftp= '';
            $v_ftp_user = $user."_".preg_replace("/^".$user."_/", "", $_POST['v_ftp_user']);
            $v_ftp_password = "••••••••";
        }
    }

    // Add FTP Account
    if ((empty($v_ftp_user)) && (!empty($_POST['v_ftp'])) && (empty($_SESSION['error_msg']))) {
        if ((!empty($_POST['v_ftp_email'])) && (!filter_var($_POST['v_ftp_email'], FILTER_VALIDATE_EMAIL))) $_SESSION['error_msg'] = __('Please enter valid email address.');
        if (empty($_POST['v_ftp_user'])) $errors[] = 'ftp user';
        if (empty($_POST['v_ftp_password'])) $errors[] = 'ftp user password';
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
            $v_ftp_user = escapeshellarg($_POST['v_ftp_user']);
            $v_ftp_password = escapeshellarg($_POST['v_ftp_password']);
            exec (VESTA_CMD."v-add-web-domain-ftp ".$v_username." ".$v_domain." ".$v_ftp_user." ".$v_ftp_password, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = __('Error code:',$return_var);
                $_SESSION['error_msg'] = $error;
            } else {
                if (!empty($_POST['v_ftp_email'])) {
                    $to = $_POST['v_ftp_email'];
                    $subject = __("FTP login credentials");
                    $hostname = exec('hostname');
                    $from = __('MAIL_FROM',$hostname);
                    $mailtext .= __('FTP_ACCOUNT_READY',$_GET['domain'],$user,$_POST['v_ftp_user'],$_POST['v_ftp_password']);
                    send_email($to, $subject, $mailtext, $from);
                    unset($v_ftp_email);
                }
            }
            unset($output);
            $v_ftp_user =  $user."_".$_POST['v_ftp_user'];
            $v_ftp_password = "••••••••";
        }
    }


    // Restart web
    if (!empty($restart_web) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-web", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
    }

    // Restart dns
    if (!empty($restart_dns) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-restart-dns", $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
        }
    }

    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }

}
if ($_SESSION['user'] == 'admin') {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_web.html');
} else {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/edit_web.html');
}
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
